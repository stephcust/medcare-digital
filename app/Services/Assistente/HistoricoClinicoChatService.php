<?php

namespace App\Services\Assistente;

use App\Models\HistoricoClinico;
use App\Models\User;
use App\Services\IA\GeminiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class HistoricoClinicoChatService
{
    private const CONTEXTO_MINUTOS = 30;

    public function __construct(private GeminiService $geminiService) {}

    /**
     * @return array{
     *   texto: string,
     *   exame_id: null,
     *   receita_id: null,
     *   vacinacao_id: null,
     *   historico_clinico_id: ?int,
     *   arquivo_nome: ?string
     * }|null
     */
    public function processar(User $user, string $mensagem): ?array
    {
        $normalizada = $this->normalizar($mensagem);
        $pendente = Cache::get($this->chavePendente($user));

        if (is_array($pendente)) {
            if ($this->ehConfirmacao($normalizada)) {
                return $this->salvarPendente($user, $pendente);
            }

            if ($this->ehCancelamento($normalizada)) {
                Cache::forget($this->chavePendente($user));

                return $this->resposta(
                    'Tudo bem. O atendimento não foi salvo.'
                );
            }

            return $this->resposta(
                'Ainda estou aguardando sua confirmação. Responda "sim" '
                . 'para salvar o atendimento ou "cancelar" para descartar.'
            );
        }

        if (!$this->pareceRelatoParaSalvar($normalizada)) {
            return null;
        }

        $dados = $this->extrairDados($mensagem);

        if ($dados === null) {
            return $this->resposta(
                'Não consegui organizar esse atendimento com segurança. '
                . 'Informe, pelo menos, quando ocorreu, o motivo e o local '
                . 'do atendimento.'
            );
        }

        Cache::put(
            $this->chavePendente($user),
            [
                'dados' => $dados,
                'relato_original' => $mensagem,
            ],
            now()->addMinutes(self::CONTEXTO_MINUTOS)
        );

        return $this->resposta(
            $this->montarConfirmacao($dados)
        );
    }

    private function salvarPendente(User $user, array $pendente): array
    {
        $dados = $pendente['dados'] ?? [];
        $paciente = $user->paciente()->firstOrCreate([]);

        try {
            $registro = HistoricoClinico::create([
                'paciente_id' => $paciente->id,
                'motivo_atendimento' => $dados['motivo_atendimento']
                    ?? 'Atendimento de pronto-socorro',
                'gravidade' => $dados['gravidade'] ?? 'Não informada',
                'data_atendimento' => $this->dataValida(
                    $dados['data_atendimento'] ?? null
                ),
                'local_atendimento' => $dados['local_atendimento']
                    ?? 'Não informado',
                'medico_nome' => $dados['medico_nome'] ?? 'Não informado',
                'diagnostico' => $dados['diagnostico'] ?? 'Não informado',
                'tratamento' => $dados['tratamento'] ?? 'Não informado',
                'exames_realizados' => $this->listaStrings(
                    $dados['exames_realizados'] ?? []
                ),
                'medicamentos' => $this->listaMedicamentos(
                    $dados['medicamentos'] ?? []
                ),
                'desfecho' => $dados['desfecho'] ?? 'Não informado',
                'acompanhamento' => $dados['acompanhamento'] ?? null,
                'observacoes' => $dados['observacoes'] ?? null,
                'arquivo_path' => null,
                'arquivo_url' => null,
                'origem' => 'simulador',
                'relato_original' => $pendente['relato_original'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::error(
                'Falha ao salvar histórico clínico pelo simulador: '
                . $e->getMessage()
            );

            return $this->resposta(
                'Não foi possível salvar o atendimento agora. '
                . 'Tente novamente.'
            );
        } finally {
            Cache::forget($this->chavePendente($user));
        }

        $data = $registro->data_atendimento?->format('d/m/Y H:i')
            ?? 'não informada';

        return [
            'texto' => "✅ Atendimento salvo no Histórico Clínico.\n\n"
                . "Motivo: {$registro->motivo_atendimento}\n"
                . "Local: {$registro->local_atendimento}\n"
                . "Data: {$data}\n\n"
                . 'Use os botões abaixo para visualizar ou baixar o resumo '
                . 'em PDF.',
            'exame_id' => null,
            'receita_id' => null,
            'vacinacao_id' => null,
            'historico_clinico_id' => (int) $registro->id,
            'arquivo_nome' => $this->nomeArquivo($registro),
        ];
    }

    private function extrairDados(string $relato): ?array
    {
        $agora = now()->format('Y-m-d H:i:s');

        $prompt = <<<PROMPT
Transforme o relato abaixo em um registro estruturado de atendimento de pronto-socorro.
Data e hora atuais: {$agora}.

REGRAS:
- Retorne somente JSON válido, sem markdown.
- Não faça diagnóstico e não invente dados.
- O campo diagnostico só pode conter algo informado por médico ou pelo próprio relato como diagnóstico recebido.
- Quando algo não estiver informado, use null.
- Converta datas relativas, como "ontem", considerando a data atual.
- gravidade deve ser uma destas opções: "Alta Gravidade", "Média Gravidade", "Baixa Gravidade" ou "Não informada".
- exames_realizados deve ser uma lista de textos.
- medicamentos deve ser uma lista de objetos com nome e dosagem. Quando a dosagem não for informada, use null.

FORMATO:
{
  "motivo_atendimento": "texto ou null",
  "gravidade": "opção permitida",
  "data_atendimento": "AAAA-MM-DD HH:MM:SS ou null",
  "local_atendimento": "texto ou null",
  "medico_nome": "texto ou null",
  "diagnostico": "texto ou null",
  "tratamento": "texto ou null",
  "exames_realizados": [],
  "medicamentos": [{"nome": "texto", "dosagem": "texto ou null"}],
  "desfecho": "texto ou null",
  "acompanhamento": "texto ou null",
  "observacoes": "texto ou null"
}

RELATO DO USUÁRIO:
{$relato}
PROMPT;

        try {
            $resposta = $this->geminiService->gerarResposta(
                $prompt,
                'Extração estruturada de um relato pessoal de atendimento. '
                . 'Não acrescente orientação médica.'
            );

            if (!$resposta) {
                return null;
            }

            $dados = $this->decodificarJson($resposta);

            if (!is_array($dados)) {
                return null;
            }

            if (
                empty($dados['motivo_atendimento'])
                && empty($dados['local_atendimento'])
            ) {
                return null;
            }

            $dados['gravidade'] = in_array(
                $dados['gravidade'] ?? null,
                [
                    'Alta Gravidade',
                    'Média Gravidade',
                    'Baixa Gravidade',
                    'Não informada',
                ],
                true
            ) ? $dados['gravidade'] : 'Não informada';

            return $dados;
        } catch (Throwable $e) {
            Log::error(
                'Falha ao extrair relato do histórico clínico: '
                . $e->getMessage()
            );

            return null;
        }
    }

    private function decodificarJson(string $resposta): ?array
    {
        $texto = trim($resposta);
        $inicio = strpos($texto, '{');
        $fim = strrpos($texto, '}');

        if ($inicio === false || $fim === false || $fim < $inicio) {
            return null;
        }

        $json = substr($texto, $inicio, $fim - $inicio + 1);
        $dados = json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE ? $dados : null;
    }

    private function montarConfirmacao(array $dados): string
    {
        $data = $this->dataValida($dados['data_atendimento'] ?? null)
            ->format('d/m/Y H:i');
        $exames = $this->listaStrings($dados['exames_realizados'] ?? []);
        $medicamentos = $this->listaMedicamentos($dados['medicamentos'] ?? []);
        $nomesMedicamentos = array_map(
            fn ($med) => $med['nome'],
            $medicamentos
        );

        $linhas = [
            'Entendi estas informações do atendimento:',
            '',
            '• Motivo: ' . ($dados['motivo_atendimento'] ?? 'Não informado'),
            '• Data: ' . $data,
            '• Local: ' . ($dados['local_atendimento'] ?? 'Não informado'),
            '• Médico(a): ' . ($dados['medico_nome'] ?? 'Não informado'),
            '• Diagnóstico informado: '
                . ($dados['diagnostico'] ?? 'Não informado'),
            '• Exames: ' . ($exames ? implode(', ', $exames) : 'Nenhum informado'),
            '• Medicamentos: ' . ($nomesMedicamentos
                ? implode(', ', $nomesMedicamentos)
                : 'Nenhum informado'),
            '• Desfecho: ' . ($dados['desfecho'] ?? 'Não informado'),
            '',
            'Deseja salvar este atendimento no Histórico Clínico? '
                . 'Responda "sim" para confirmar ou "cancelar".',
        ];

        return implode("\n", $linhas);
    }

    private function pareceRelatoParaSalvar(string $mensagem): bool
    {
        $temContexto = Str::contains($mensagem, [
            'pronto socorro',
            'pronto-socorro',
            'hospital',
            'upa',
            'emergencia',
            'urgencia',
            'atendimento medico',
        ]);

        $acaoExplicita = Str::contains($mensagem, [
            'salve',
            'salvar',
            'registre',
            'registrar',
            'guarde',
            'guardar',
            'adicione ao historico',
            'coloque no historico',
            'anote no historico',
        ]);

        $relatoPessoal = Str::contains($mensagem, [
            'fui ao',
            'fui para',
            'fui atendido',
            'fui atendida',
            'passei no',
            'estive no',
            'dei entrada',
            'recebi alta',
            'tive alta',
        ]);

        return $temContexto && ($acaoExplicita || $relatoPessoal);
    }

    private function ehConfirmacao(string $mensagem): bool
    {
        return in_array(trim($mensagem), [
            'sim',
            's',
            'confirmar',
            'confirmo',
            'pode salvar',
            'salvar',
            'salve',
            'pode registrar',
            'registre',
        ], true);
    }

    private function ehCancelamento(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'cancelar',
            'cancela',
            'nao',
            'não',
            'deixa pra la',
            'deixe pra la',
        ]);
    }

    private function dataValida(?string $data): Carbon
    {
        try {
            return $data ? Carbon::parse($data) : now();
        } catch (Throwable) {
            return now();
        }
    }

    private function listaStrings(mixed $itens): array
    {
        if (!is_array($itens)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item) => is_scalar($item) ? trim((string) $item) : '',
            $itens
        )));
    }

    private function listaMedicamentos(mixed $itens): array
    {
        if (!is_array($itens)) {
            return [];
        }

        $resultado = [];

        foreach ($itens as $item) {
            if (is_string($item) && trim($item) !== '') {
                $resultado[] = [
                    'nome' => trim($item),
                    'dosagem' => 'Não informada',
                ];
                continue;
            }

            if (!is_array($item) || empty($item['nome'])) {
                continue;
            }

            $resultado[] = [
                'nome' => trim((string) $item['nome']),
                'dosagem' => !empty($item['dosagem'])
                    ? trim((string) $item['dosagem'])
                    : 'Não informada',
            ];
        }

        return $resultado;
    }

    private function nomeArquivo(HistoricoClinico $registro): string
    {
        $data = $registro->data_atendimento?->format('Y-m-d') ?? 'sem-data';

        return "resumo-atendimento-{$data}.pdf";
    }

    private function resposta(string $texto): array
    {
        return [
            'texto' => $texto,
            'exame_id' => null,
            'receita_id' => null,
            'vacinacao_id' => null,
            'historico_clinico_id' => null,
            'arquivo_nome' => null,
        ];
    }

    private function chavePendente(User $user): string
    {
        return "medcare:historico-clinico:pendente:{$user->id}";
    }

    private function normalizar(string $texto): string
    {
        return Str::lower(Str::ascii(trim($texto)));
    }
}
