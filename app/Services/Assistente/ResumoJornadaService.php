<?php

namespace App\Services\Assistente;

use App\Models\ResumoJornada;
use App\Models\User;
use App\Services\IA\GeminiService;
use Illuminate\Support\Str;
use Throwable;

class ResumoJornadaService
{
    public const SECOES_VALIDAS = [
        'dados_pessoais',
        'relatos',
        'exames',
        'receitas',
        'vacinas',
        'historico_clinico',
    ];

    public const SECOES_PADRAO = [
        'dados_pessoais',
        'relatos',
        'exames',
        'receitas',
        'vacinas',
        'historico_clinico',
    ];

    public function __construct(
        private GeminiService $geminiService,
        private MedCareContextService $medCareContextService
    ) {}

    public function gerar(
        User $user,
        array $secoes,
        string $periodo = 'todos',
        bool $incluirPerguntas = true
    ): array {
        $secoes = $this->normalizarSecoes($secoes);

        if (empty($secoes)) {
            $secoes = self::SECOES_PADRAO;
        }

        $contexto = $this->medCareContextService->montarResumo(
            $user,
            $secoes,
            $periodo
        );

        $perguntas = $incluirPerguntas
            ? 'Inclua de 2 a 5 perguntas úteis e seguras para o paciente fazer ao médico.'
            : 'Não inclua sugestões de perguntas para o médico.';

        $prompt = <<<PROMPT
Gere um Sumário de Preparação Clínica objetivo e fácil de ler para apoiar uma consulta médica.

Use somente os dados presentes no contexto enviado.
Não invente informações ausentes.
Não faça diagnóstico, não prescreva medicamentos e não altere tratamentos.
Diferencie relatos escritos pelo paciente de exames, receitas, vacinas e outros registros do sistema.
{$perguntas}

Retorne SOMENTE um objeto JSON válido, sem Markdown, sem crases, sem hashtags, sem asteriscos e sem texto antes ou depois do JSON.

Use exatamente esta estrutura:
{
  "titulo": "Sumário de Preparação Clínica",
  "periodo": "Período analisado",
  "secoes": [
    {
      "id": "identificador_da_secao",
      "titulo": "Título da seção",
      "itens": [
        "Item objetivo com data quando estiver disponível"
      ]
    }
  ],
  "perguntas_medico": [
    "Pergunta para o médico"
  ]
}

Não crie seções que não foram solicitadas.
Quando uma seção solicitada não tiver registros, inclua um item curto informando que não foram encontrados dados no período.
Use no máximo 5 itens objetivos por seção para evitar respostas excessivamente longas.
PROMPT;

        $resposta = $this->geminiService->gerarResposta(
            $prompt,
            $contexto
        );

        if (!$resposta) {
            return $this->respostaIndisponivel($periodo);
        }

        $dados = $this->extrairJson($resposta);

        if (!is_array($dados)) {
            $dados = $this->tentarCorrigirJson($resposta);
        }

        if (!is_array($dados)) {
            return $this->respostaIndisponivel($periodo);
        }

        return $this->normalizarResultado(
            $dados,
            $periodo,
            $incluirPerguntas
        );
    }

    public function gerarESalvar(
        User $user,
        array $secoes,
        string $periodo = 'todos',
        bool $incluirPerguntas = true,
        string $origem = 'jornada'
    ): ResumoJornada {
        $secoes = $this->normalizarSecoes($secoes);

        if (empty($secoes)) {
            $secoes = self::SECOES_PADRAO;
        }

        $resumo = $this->gerar(
            $user,
            $secoes,
            $periodo,
            $incluirPerguntas
        );

        return ResumoJornada::create([
            'user_id' => $user->id,
            'titulo' => $resumo['titulo']
                ?? 'Sumário de Preparação Clínica',
            'periodo' => $periodo,
            'secoes' => $secoes,
            'incluir_perguntas' => $incluirPerguntas,
            'conteudo' => $resumo,
            'origem' => $origem === 'simulador'
                ? 'simulador'
                : 'jornada',
        ]);
    }

    public function serializarResumo(ResumoJornada $resumo): array
    {
        return [
            'id' => $resumo->id,
            'titulo' => $resumo->titulo,
            'periodo_codigo' => $resumo->periodo,
            'periodo' => $this->medCareContextService
                ->nomePeriodo($resumo->periodo),
            'secoes' => $resumo->secoes ?? [],
            'incluir_perguntas' => $resumo->incluir_perguntas,
            'conteudo' => $this->normalizarConteudoSalvo(
                $resumo->conteudo,
                $resumo->periodo,
                (bool) $resumo->incluir_perguntas
            ),
            'origem' => $resumo->origem,
            'criado_em' => $resumo->created_at
                ?->timezone(config('app.timezone'))
                ->format('d/m/Y H:i'),
        ];
    }

    /**
     * Interpreta comandos de resumo enviados no simulador.
     */
    public function processarComando(
        User $user,
        string $mensagem
    ): ?string {
        $normalizada = Str::lower(Str::ascii(trim($mensagem)));

        if (!$this->ehPedidoDeResumo($normalizada)) {
            return null;
        }

        $secoes = $this->identificarSecoes($normalizada);
        $periodo = $this->identificarPeriodo($normalizada);
        $incluirPerguntas = !Str::contains($normalizada, [
            'sem perguntas',
            'nao inclua perguntas',
            'sem sugestoes de perguntas',
        ]);

        try {
            $registro = $this->gerarESalvar(
                $user,
                $secoes,
                $periodo,
                $incluirPerguntas,
                'simulador'
            );
        } catch (Throwable $e) {
            report($e);

            return 'Não consegui gerar o seu sumário agora. '
                . 'Tente novamente em alguns instantes.';
        }

        return $this->formatarParaChat($registro->conteudo)
            . "\n\nResumo salvo na Jornada Inteligente.";
    }

    private function ehPedidoDeResumo(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'gerar resumo',
            'gere um resumo',
            'fazer resumo',
            'faca um resumo',
            'meu resumo',
            'resumo clinico',
            'resumo para consulta',
            'sumario clinico',
            'sumario para consulta',
            'preparar consulta',
            'preparacao para consulta',
        ]);
    }

    private function identificarSecoes(string $mensagem): array
    {
        $secoes = [];

        if (Str::contains($mensagem, [
            'dado pessoal',
            'dados pessoais',
            'nome e email',
            'nome e e-mail',
        ])) {
            $secoes[] = 'dados_pessoais';
        }

        if (Str::contains($mensagem, [
            'sintoma',
            'sintomas',
            'relato',
            'relatos',
            'queixa',
            'queixas',
            'jornada',
        ])) {
            $secoes[] = 'relatos';
        }

        if (Str::contains($mensagem, [
            'exame',
            'exames',
            'laudo',
            'laudos',
        ])) {
            $secoes[] = 'exames';
        }

        if (Str::contains($mensagem, [
            'receita',
            'receitas',
            'prescricao',
            'prescricoes',
            'medicamento',
            'medicamentos',
        ])) {
            $secoes[] = 'receitas';
        }

        if (Str::contains($mensagem, [
            'vacina',
            'vacinas',
            'vacinacao',
        ])) {
            $secoes[] = 'vacinas';
        }

        if (Str::contains($mensagem, [
            'historico clinico',
            'historico medico',
            'ocorrencia clinica',
            'ocorrencias clinicas',
            'pronto socorro',
            'pronto-socorro',
            'historico ps',
            'passagem por ps',
            'atendimento de urgencia',
        ])) {
            $secoes[] = 'historico_clinico';
        }

        return empty($secoes)
            ? self::SECOES_PADRAO
            : array_values(array_unique($secoes));
    }

    private function identificarPeriodo(string $mensagem): string
    {
        if (
            Str::contains($mensagem, ['30 dias', 'ultimo mes', '1 mes'])
        ) {
            return '30';
        }

        if (
            Str::contains($mensagem, ['60 dias', '2 meses'])
        ) {
            return '60';
        }

        if (
            Str::contains($mensagem, ['90 dias', '3 meses'])
        ) {
            return '90';
        }

        return 'todos';
    }

    private function normalizarSecoes(array $secoes): array
    {
        return array_values(array_intersect(
            self::SECOES_VALIDAS,
            array_values(array_unique($secoes))
        ));
    }

    public function normalizarConteudoSalvo(
        mixed $conteudo,
        string $periodo = 'todos',
        bool $incluirPerguntas = true
    ): array {
        if (is_string($conteudo)) {
            $decodificado = $this->extrairJson($conteudo);

            if (is_array($decodificado)) {
                return $this->normalizarResultado(
                    $decodificado,
                    $periodo,
                    $incluirPerguntas
                );
            }

            return $this->respostaIndisponivel($periodo);
        }

        if (!is_array($conteudo)) {
            return $this->respostaIndisponivel($periodo);
        }

        $secoes = $conteudo['secoes'] ?? [];

        // Recupera resumos antigos que salvaram o JSON como várias linhas.
        if (
            count($secoes) === 1
            && is_array($secoes[0] ?? null)
            && ($secoes[0]['id'] ?? null) === 'resumo'
            && is_array($secoes[0]['itens'] ?? null)
        ) {
            $textoAntigo = implode("\n", $secoes[0]['itens']);
            $recuperado = $this->extrairJson($textoAntigo);

            if (is_array($recuperado)) {
                return $this->normalizarResultado(
                    $recuperado,
                    $periodo,
                    $incluirPerguntas
                );
            }
        }

        return $this->normalizarResultado(
            $conteudo,
            $periodo,
            $incluirPerguntas
        );
    }

    private function extrairJson(string $resposta): ?array
    {
        $texto = trim($resposta);
        $texto = str_replace(["\xEF\xBB\xBF", "\u{200B}"], '', $texto);
        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = preg_replace('/^```(?:json)?\s*/i', '', $texto);
        $texto = preg_replace('/\s*```$/', '', $texto);
        $texto = str_replace(
            ['“', '”', '„', '‟', '‘', '’'],
            ['"', '"', '"', '"', "'", "'"],
            $texto
        );

        $candidatos = [$texto];

        $inicio = strpos($texto, '{');
        $fim = strrpos($texto, '}');

        if ($inicio !== false && $fim !== false && $fim >= $inicio) {
            $candidatos[] = substr(
                $texto,
                $inicio,
                $fim - $inicio + 1
            );
        }

        $primeiroDecode = json_decode($texto, true);

        if (is_string($primeiroDecode)) {
            $candidatos[] = $primeiroDecode;
        }

        foreach (array_unique($candidatos) as $candidato) {
            $candidato = trim($candidato);
            $candidato = preg_replace(
                '/,\s*([}\]])/u',
                '$1',
                $candidato
            );

            $dados = json_decode($candidato, true);

            if (is_string($dados)) {
                $dados = json_decode($dados, true);
            }

            if (is_array($dados)) {
                if (isset($dados['resumo']) && is_array($dados['resumo'])) {
                    $dados = $dados['resumo'];
                }

                return $dados;
            }
        }

        return null;
    }

    private function tentarCorrigirJson(string $resposta): ?array
    {
        $prompt = <<<'PROMPT'
A resposta fornecida deveria ser um objeto JSON, mas está inválida.
Converta o conteúdo em JSON válido.
Retorne somente o JSON, sem Markdown e sem qualquer explicação.
Mantenha esta estrutura: titulo, periodo, secoes e perguntas_medico.
Cada seção deve possuir id, titulo e itens.
PROMPT;

        $corrigida = $this->geminiService->gerarResposta(
            $prompt,
            "RESPOSTA A CORRIGIR:\n" . $resposta
        );

        return $corrigida
            ? $this->extrairJson($corrigida)
            : null;
    }

    private function normalizarResultado(
        array $dados,
        string $periodo,
        bool $incluirPerguntas
    ): array {
        $secoes = [];

        foreach (($dados['secoes'] ?? []) as $indice => $secao) {
            if (!is_array($secao)) {
                continue;
            }

            $itens = [];

            foreach (($secao['itens'] ?? []) as $item) {
                if (!is_scalar($item)) {
                    continue;
                }

                $limpo = $this->limparTexto((string) $item);

                if ($limpo !== '') {
                    $itens[] = $limpo;
                }
            }

            if (empty($itens)) {
                continue;
            }

            $identificador = $this->limparIdentificador(
                (string) ($secao['id'] ?? "secao_{$indice}")
            );

            if (in_array($identificador, [
                'historico_ps',
                'historico_pronto_socorro',
            ], true)) {
                $identificador = 'historico_clinico';
            }

            $secoes[] = [
                'id' => $identificador,
                'titulo' => $this->limparTexto(
                    (string) ($secao['titulo'] ?? 'Informações')
                ),
                'itens' => $itens,
            ];
        }

        $perguntas = [];

        if ($incluirPerguntas) {
            foreach (($dados['perguntas_medico'] ?? []) as $pergunta) {
                if (!is_scalar($pergunta)) {
                    continue;
                }

                $limpa = $this->limparTexto((string) $pergunta);

                if ($limpa !== '') {
                    $perguntas[] = $limpa;
                }
            }
        }

        if (empty($secoes)) {
            return $this->respostaIndisponivel($periodo);
        }

        return [
            'titulo' => $this->limparTexto(
                (string) ($dados['titulo']
                    ?? 'Sumário de Preparação Clínica')
            ),
            'periodo' => $this->medCareContextService
                ->nomePeriodo($periodo),
            'secoes' => $secoes,
            'perguntas_medico' => $perguntas,
        ];
    }

    private function fallbackEstruturado(
        string $resposta,
        string $periodo
    ): array {
        return $this->respostaIndisponivel($periodo);
    }

    private function respostaIndisponivel(string $periodo): array
    {
        return [
            'titulo' => 'Sumário de Preparação Clínica',
            'periodo' => $this->medCareContextService
                ->nomePeriodo($periodo),
            'secoes' => [
                [
                    'id' => 'aviso',
                    'titulo' => 'Não foi possível gerar o sumário',
                    'itens' => [
                        'Tente novamente em alguns instantes.',
                    ],
                ],
            ],
            'perguntas_medico' => [],
        ];
    }

    private function formatarParaChat(array $resumo): string
    {
        $linhas = [
            $resumo['titulo'] ?? 'Sumário de Preparação Clínica',
            'Período: ' . ($resumo['periodo'] ?? 'Todo o histórico'),
            '',
        ];

        foreach (($resumo['secoes'] ?? []) as $secao) {
            $linhas[] = $secao['titulo'] ?? 'Informações';

            foreach (($secao['itens'] ?? []) as $item) {
                $linhas[] = '• ' . $item;
            }

            $linhas[] = '';
        }

        if (!empty($resumo['perguntas_medico'])) {
            $linhas[] = 'Perguntas para a consulta';

            foreach ($resumo['perguntas_medico'] as $indice => $pergunta) {
                $linhas[] = ($indice + 1) . '. ' . $pergunta;
            }
        }

        return trim(implode("\n", $linhas));
    }

    private function limparTexto(string $texto): string
    {
        $texto = strip_tags($texto);
        $texto = str_replace(['**', '__', '`'], '', $texto);
        $texto = preg_replace('/^\s*#{1,6}\s*/u', '', $texto);
        $texto = preg_replace('/^\s*[-–—]{3,}\s*$/u', '', $texto);
        $texto = preg_replace('/^\s*[-*]\s+/u', '', $texto);
        $texto = preg_replace('/\s+/u', ' ', $texto);

        return trim((string) $texto);
    }

    private function limparIdentificador(string $identificador): string
    {
        $identificador = Str::lower(Str::ascii($identificador));
        $identificador = preg_replace('/[^a-z0-9_]+/', '_', $identificador);

        return trim((string) $identificador, '_') ?: 'secao';
    }
}
