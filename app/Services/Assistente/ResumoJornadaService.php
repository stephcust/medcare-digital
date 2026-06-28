<?php

namespace App\Services\Assistente;

use App\Models\Exame;
use App\Models\HistoricoClinico;
use App\Models\Lembrete;
use App\Models\Paciente;
use App\Models\PerfilPaciente;
use App\Models\Receita;
use App\Models\RelatoSaude;
use App\Models\ResumoJornada;
use App\Models\User;
use App\Models\Vacinacao;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        'pendencias',
    ];

    public const SECOES_PADRAO = self::SECOES_VALIDAS;

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

        $resultado = [];

        foreach (self::SECOES_VALIDAS as $secao) {
            if (!in_array($secao, $secoes, true)) {
                continue;
            }

            $resultado[] = match ($secao) {
                'dados_pessoais' => $this->secaoPerfilSaude($user),
                'relatos' => $this->secaoRelatos($user, $periodo),
                'exames' => $this->secaoExames($user, $periodo),
                'receitas' => $this->secaoReceitas($user, $periodo),
                'vacinas' => $this->secaoVacinas($user, $periodo),
                'historico_clinico' => $this->secaoHistoricoClinico($user, $periodo),
                'pendencias' => $this->secaoPendencias($user),
            };
        }

        return [
            'titulo' => 'Sumário de Preparação Clínica',
            'periodo' => $this->nomePeriodo($periodo),
            'gerado_em' => now()
                ->timezone(config('app.timezone'))
                ->format('d/m/Y H:i'),
            'secoes' => $resultado,
            'perguntas_medico' => $incluirPerguntas
                ? $this->perguntasSugeridas($resultado)
                : [],
        ];
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
            'titulo' => $resumo['titulo'],
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
            'periodo' => $this->nomePeriodo($resumo->periodo),
            'secoes' => $resumo->secoes ?? [],
            'incluir_perguntas' => (bool) $resumo->incluir_perguntas,
            'conteudo' => $this->normalizarConteudoSalvo(
                $resumo->conteudo,
                $resumo->periodo,
                (bool) $resumo->incluir_perguntas
            ),
            'origem' => $resumo->origem,
            'criado_em' => $resumo->created_at
                ?->timezone(config('app.timezone'))
                ->format('d/m/Y H:i'),
            'visualizar_url' => route(
                'jornada-inteligente.resumos.visualizar',
                $resumo->id
            ),
            'download_url' => route(
                'jornada-inteligente.resumos.download',
                $resumo->id
            ),
            'imprimir_url' => route(
                'jornada-inteligente.resumos.imprimir',
                $resumo->id
            ),
        ];
    }

    /**
     * @return array{
     *     texto: string,
     *     exame_id: null,
     *     receita_id: null,
     *     vacinacao_id: null,
     *     historico_clinico_id: null,
     *     resumo_jornada_id: ?int,
     *     arquivo_nome: string
     * }|null
     */
    public function processarComando(User $user, string $mensagem): ?array
    {
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

            return [
                'texto' => 'Não consegui gerar o seu sumário agora. '
                    . 'Tente novamente em alguns instantes.',
                'exame_id' => null,
                'receita_id' => null,
                'vacinacao_id' => null,
                'historico_clinico_id' => null,
                'resumo_jornada_id' => null,
                'arquivo_nome' => null,
            ];
        }

        return [
            'texto' => $this->formatarParaChat($registro->conteudo)
                . "\n\nO PDF foi salvo na Jornada Inteligente. "
                . 'Use os botões abaixo para visualizar ou baixar.',
            'exame_id' => null,
            'receita_id' => null,
            'vacinacao_id' => null,
            'historico_clinico_id' => null,
            'resumo_jornada_id' => (int) $registro->id,
            'arquivo_nome' => $this->nomeArquivo($registro),
        ];
    }

    public function normalizarConteudoSalvo(
        mixed $conteudo,
        string $periodo = 'todos',
        bool $incluirPerguntas = true
    ): array {
        if (is_string($conteudo)) {
            $conteudo = json_decode($conteudo, true);
        }

        if (!is_array($conteudo)) {
            return [
                'titulo' => 'Sumário de Preparação Clínica',
                'periodo' => $this->nomePeriodo($periodo),
                'gerado_em' => null,
                'secoes' => [],
                'perguntas_medico' => [],
            ];
        }

        return [
            'titulo' => $conteudo['titulo']
                ?? 'Sumário de Preparação Clínica',
            'periodo' => $conteudo['periodo']
                ?? $this->nomePeriodo($periodo),
            'gerado_em' => $conteudo['gerado_em'] ?? null,
            'secoes' => array_values(array_filter(
                is_array($conteudo['secoes'] ?? null)
                    ? $conteudo['secoes']
                    : [],
                fn ($secao) => is_array($secao)
                    && isset($secao['id'], $secao['titulo'])
            )),
            'perguntas_medico' => $incluirPerguntas
                && is_array($conteudo['perguntas_medico'] ?? null)
                    ? array_values($conteudo['perguntas_medico'])
                    : [],
        ];
    }

    public function nomePeriodo(string $periodo): string
    {
        return match ($periodo) {
            '30' => 'Últimos 30 dias',
            '60' => 'Últimos 60 dias',
            '90' => 'Últimos 90 dias',
            default => 'Todo o histórico disponível',
        };
    }

    public function nomeArquivo(ResumoJornada $resumo): string
    {
        $data = $resumo->created_at
            ?->timezone(config('app.timezone'))
            ->format('Y-m-d_H-i') ?? now()->format('Y-m-d_H-i');

        return "sumario-clinico-{$data}.pdf";
    }

    public function formatarParaChat(mixed $conteudo): string
    {
        $resumo = $this->normalizarConteudoSalvo($conteudo);
        $totalSecoes = count($resumo['secoes']);

        $linhas = [
            'Sumário de Preparação Clínica criado ✅',
            '',
            "Período: {$resumo['periodo']}",
            "Seções incluídas: {$totalSecoes}",
        ];

        foreach ($resumo['secoes'] as $secao) {
            $quantidade = count($secao['itens'] ?? []);
            $linhas[] = "• {$secao['titulo']}: {$quantidade} item(ns)";
        }

        return implode("\n", $linhas);
    }

    private function secaoPerfilSaude(User $user): array
    {
        $paciente = Paciente::where('user_id', $user->id)->first();
        $perfil = PerfilPaciente::where('user_id', $user->id)->first();
        $itens = [
            "Nome: {$user->name}",
            "E-mail: {$user->email}",
        ];

        if ($perfil?->data_nascimento) {
            $itens[] = 'Data de nascimento: '
                . $perfil->data_nascimento->format('d/m/Y')
                . " ({$perfil->data_nascimento->age} anos)";
        }

        $this->adicionarCampo($itens, 'Gênero', $paciente?->genero);
        $this->adicionarCampo($itens, 'Telefone', $paciente?->telefone);
        $this->adicionarCampo(
            $itens,
            'Tipo sanguíneo',
            $paciente?->tipo_sanguineo ?: $perfil?->tipo_sanguineo
        );

        if ($perfil?->peso_kg !== null) {
            $peso = number_format((float) $perfil->peso_kg, 1, ',', '.');
            $dataPeso = $perfil->peso_atualizado_em
                ? ' - atualizado em '
                    . $perfil->peso_atualizado_em
                        ->timezone(config('app.timezone'))
                        ->format('d/m/Y')
                : '';
            $itens[] = "Peso: {$peso} kg{$dataPeso}";
        }

        if ($perfil?->altura_cm) {
            $itens[] = "Altura: {$perfil->altura_cm} cm";
        }

        $alergias = $this->normalizarLista(
            $paciente?->alergias_conhecidas ?: $perfil?->alergias_conhecidas
        );
        $itens[] = 'Alergias declaradas: '
            . ($alergias ? implode('; ', $alergias) : 'nenhuma informada');

        $itens[] = 'Condições crônicas declaradas: '
            . $this->textoLista($perfil?->condicoes_cronicas);
        $itens[] = 'Medicamentos de uso contínuo declarados: '
            . $this->textoLista($perfil?->medicamentos_continuos);
        $itens[] = 'Cirurgias anteriores declaradas: '
            . $this->textoLista($perfil?->cirurgias_anteriores);
        $itens[] = 'Dispositivos ou implantes declarados: '
            . $this->textoLista($perfil?->dispositivos_implantes);

        if ($perfil?->observacoes_importantes) {
            $itens[] = 'Informações importantes declaradas pelo paciente: '
                . $perfil->observacoes_importantes;
        }

        $contato = array_filter([
            $perfil?->contato_emergencia_nome,
            $perfil?->contato_emergencia_parentesco,
            $perfil?->contato_emergencia_telefone,
        ]);

        if ($contato) {
            $itens[] = 'Contato de emergência: ' . implode(' - ', $contato);
        }

        if (!$perfil) {
            $itens[] = 'Perfil de Saúde ainda não foi preenchido completamente.';
        }

        return $this->secao(
            'dados_pessoais',
            'Identificação e Perfil de Saúde',
            $itens
        );
    }

    private function secaoRelatos(User $user, string $periodo): array
    {
        $query = RelatoSaude::query()
            ->where('user_id', $user->id)
            ->where('incluir_no_resumo', true)
            ->orderByDesc('data_ocorrencia')
            ->orderByDesc('created_at');

        $this->aplicarPeriodo($query, 'data_ocorrencia', $periodo);

        $itens = $query->get()->map(function (RelatoSaude $relato) {
            $data = $relato->data_ocorrencia
                ?->format('d/m/Y') ?? 'data não informada';
            $titulo = $relato->titulo ?: ucfirst($relato->categoria);

            return "{$data} - {$titulo}: {$relato->relato}";
        })->all();

        return $this->secao(
            'relatos',
            'Sintomas, Queixas e Relatos',
            $itens,
            'Nenhum relato marcado para o sumário foi encontrado no período.'
        );
    }

    private function secaoExames(User $user, string $periodo): array
    {
        $query = Exame::query()
            ->where('user_id', $user->id)
            ->orderByDesc('data_realizacao')
            ->orderByDesc('created_at');

        $this->aplicarPeriodo($query, 'data_realizacao', $periodo);

        $itens = $query->get()->map(function (Exame $exame) {
            $partes = [
                $exame->data_realizacao?->format('d/m/Y')
                    ?? 'data não informada',
                $exame->nome,
            ];

            if ($exame->tipo) {
                $partes[] = "tipo: {$exame->tipo}";
            }
            if ($exame->laboratorio) {
                $partes[] = "local: {$exame->laboratorio}";
            }

            return implode(' - ', array_filter($partes));
        })->all();

        return $this->secao(
            'exames',
            'Exames Cadastrados',
            $itens,
            'Nenhum exame foi encontrado no período selecionado.'
        );
    }

    private function secaoReceitas(User $user, string $periodo): array
    {
        $query = Receita::query()
            ->where('user_id', $user->id)
            ->orderByDesc('data_emissao')
            ->orderByDesc('created_at');

        $this->aplicarPeriodo($query, 'data_emissao', $periodo);

        $itens = $query->get()->map(function (Receita $receita) {
            $data = $receita->data_emissao?->format('d/m/Y')
                ?? 'data não informada';
            $medicamentos = $this->formatarMedicamentos($receita->medicamentos);
            $validade = $receita->data_validade
                ? ' | validade: ' . $receita->data_validade->format('d/m/Y')
                : '';
            $status = $receita->status ? " | status: {$receita->status}" : '';
            $medico = $receita->medico ?: 'profissional não informado';
            $especialidade = $receita->especialidade
                ? " ({$receita->especialidade})"
                : '';

            return "{$data} - {$medico}{$especialidade}{$validade}{$status}"
                . ($medicamentos ? " | medicamentos: {$medicamentos}" : '');
        })->all();

        return $this->secao(
            'receitas',
            'Receitas e Medicamentos',
            $itens,
            'Nenhuma receita foi encontrada no período selecionado.'
        );
    }

    private function secaoVacinas(User $user, string $periodo): array
    {
        $paciente = Paciente::where('user_id', $user->id)->first();

        if (!$paciente) {
            return $this->secao(
                'vacinas',
                'Vacinas',
                [],
                'Nenhum perfil de paciente foi encontrado.'
            );
        }

        $query = Vacinacao::query()
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('data_aplicacao')
            ->orderByDesc('created_at');

        $this->aplicarPeriodo($query, 'data_aplicacao', $periodo);

        $itens = $query->get()->map(function (Vacinacao $vacina) {
            $data = $vacina->data_aplicacao?->format('d/m/Y')
                ?? 'data não informada';
            $proxima = $vacina->data_proxima_dose
                ? ' | próxima dose: '
                    . $vacina->data_proxima_dose->format('d/m/Y')
                : '';
            $fabricante = $vacina->fabricante
                ? " | fabricante: {$vacina->fabricante}"
                : '';

            return "{$data} - {$vacina->nome_vacina}"
                . " ({$vacina->numero_dose}){$fabricante}{$proxima}";
        })->all();

        return $this->secao(
            'vacinas',
            'Vacinas',
            $itens,
            'Nenhuma vacinação foi encontrada no período selecionado.'
        );
    }

    private function secaoHistoricoClinico(User $user, string $periodo): array
    {
        $paciente = Paciente::where('user_id', $user->id)->first();

        if (!$paciente) {
            return $this->secao(
                'historico_clinico',
                'Histórico Clínico',
                [],
                'Nenhum perfil de paciente foi encontrado.'
            );
        }

        $query = HistoricoClinico::query()
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('data_atendimento');

        $this->aplicarPeriodo($query, 'data_atendimento', $periodo);

        $itens = $query->get()->map(function (HistoricoClinico $historico) {
            $data = $historico->data_atendimento
                ?->format('d/m/Y H:i') ?? 'data não informada';
            $partes = [
                "{$data} - {$historico->motivo_atendimento}",
                $historico->local_atendimento
                    ? "local: {$historico->local_atendimento}"
                    : null,
                $historico->gravidade
                    ? "gravidade: {$historico->gravidade}"
                    : null,
                $historico->diagnostico
                    ? "diagnóstico informado: {$historico->diagnostico}"
                    : null,
                $historico->tratamento
                    ? "tratamento registrado: {$historico->tratamento}"
                    : null,
                $this->normalizarLista($historico->exames_realizados)
                    ? 'exames: ' . implode('; ', $this->normalizarLista($historico->exames_realizados))
                    : null,
                $this->formatarMedicamentos($historico->medicamentos)
                    ? 'medicamentos: ' . $this->formatarMedicamentos($historico->medicamentos)
                    : null,
                $historico->desfecho
                    ? "desfecho: {$historico->desfecho}"
                    : null,
                $historico->acompanhamento
                    ? "acompanhamento: {$historico->acompanhamento}"
                    : null,
            ];

            return implode(' | ', array_filter($partes));
        })->all();

        return $this->secao(
            'historico_clinico',
            'Histórico Clínico',
            $itens,
            'Nenhum atendimento foi encontrado no período selecionado.'
        );
    }

    private function secaoPendencias(User $user): array
    {
        $itens = [];
        $agora = now();
        $limite = now()->addDays(30)->endOfDay();

        $lembretes = Lembrete::query()
            ->where('user_id', $user->id)
            ->where('status', 'pendente')
            ->where('ativo', true)
            ->where('data_hora', '<=', $limite)
            ->orderBy('data_hora')
            ->get();

        foreach ($lembretes as $lembrete) {
            $estado = $lembrete->data_hora->isPast()
                ? 'atrasado'
                : 'próximo';
            $itens[] = 'Lembrete ' . $estado . ': '
                . $lembrete->titulo
                . ' - ' . $lembrete->data_hora
                    ->timezone(config('app.timezone'))
                    ->format('d/m/Y H:i');
        }

        $paciente = Paciente::where('user_id', $user->id)->first();

        if ($paciente) {
            $vacinas = Vacinacao::query()
                ->where('paciente_id', $paciente->id)
                ->whereNotNull('data_proxima_dose')
                ->where('data_proxima_dose', '<=', $limite->toDateString())
                ->orderBy('data_proxima_dose')
                ->get();

            foreach ($vacinas as $vacina) {
                $estado = $vacina->data_proxima_dose->lt($agora)
                    ? 'atrasada'
                    : 'próxima';
                $itens[] = "Dose {$estado}: {$vacina->nome_vacina}"
                    . ' - ' . $vacina->data_proxima_dose->format('d/m/Y');
            }
        }

        $receitas = Receita::query()
            ->where('user_id', $user->id)
            ->whereNotNull('data_validade')
            ->where('data_validade', '<=', $limite->toDateString())
            ->orderBy('data_validade')
            ->get();

        foreach ($receitas as $receita) {
            $estado = $receita->data_validade->lt($agora)
                ? 'vencida'
                : 'próxima do vencimento';
            $itens[] = "Prescrição {$estado}: "
                . ($receita->medico ?: 'profissional não informado')
                . ' - ' . $receita->data_validade->format('d/m/Y');
        }

        return $this->secao(
            'pendencias',
            'Pendências e Próximos Cuidados',
            $itens,
            'Nenhuma pendência próxima foi identificada nos dados cadastrados.'
        );
    }

    private function perguntasSugeridas(array $secoes): array
    {
        $ids = array_column($secoes, 'id');
        $perguntas = [
            'Há alguma informação deste sumário que precisa de investigação ou acompanhamento?',
        ];

        if (in_array('exames', $ids, true)) {
            $perguntas[] = 'Os exames cadastrados precisam ser repetidos ou comparados com resultados anteriores?';
        }
        if (in_array('receitas', $ids, true)) {
            $perguntas[] = 'As prescrições e os medicamentos de uso contínuo continuam adequados ao meu acompanhamento?';
        }
        if (in_array('vacinas', $ids, true)) {
            $perguntas[] = 'Existe alguma dose de vacina que precisa ser atualizada?';
        }
        if (in_array('relatos', $ids, true)) {
            $perguntas[] = 'Quais dos sintomas ou relatos registrados merecem acompanhamento mais próximo?';
        }
        if (in_array('historico_clinico', $ids, true)) {
            $perguntas[] = 'Algum atendimento anterior muda os cuidados ou exames indicados atualmente?';
        }

        return array_slice(array_values(array_unique($perguntas)), 0, 5);
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
            'gerar pdf clinico',
            'meu sumario',
        ]);
    }

    private function identificarSecoes(string $mensagem): array
    {
        if (Str::contains($mensagem, [
            'todas as informacoes',
            'todas informacoes',
            'tudo',
            'completo',
            'todo o meu historico',
        ])) {
            return self::SECOES_PADRAO;
        }

        $mapa = [
            'dados_pessoais' => [
                'dado pessoal', 'dados pessoais', 'perfil de saude',
                'tipo sanguineo', 'alergia', 'peso', 'altura',
            ],
            'relatos' => [
                'sintoma', 'relato', 'queixa', 'jornada',
            ],
            'exames' => [
                'exame', 'laudo',
            ],
            'receitas' => [
                'receita', 'prescricao', 'medicamento',
            ],
            'vacinas' => [
                'vacina', 'vacinacao',
            ],
            'historico_clinico' => [
                'historico clinico', 'historico medico', 'pronto socorro',
                'pronto-socorro', 'atendimento de urgencia',
            ],
            'pendencias' => [
                'pendencia', 'lembrete', 'proxima dose', 'vencimento',
            ],
        ];

        $secoes = [];

        foreach ($mapa as $id => $termos) {
            if (Str::contains($mensagem, $termos)) {
                $secoes[] = $id;
            }
        }

        return empty($secoes)
            ? self::SECOES_PADRAO
            : array_values(array_unique($secoes));
    }

    private function identificarPeriodo(string $mensagem): string
    {
        if (Str::contains($mensagem, ['30 dias', 'ultimo mes', '1 mes'])) {
            return '30';
        }
        if (Str::contains($mensagem, ['60 dias', '2 meses'])) {
            return '60';
        }
        if (Str::contains($mensagem, ['90 dias', '3 meses'])) {
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

    private function secao(
        string $id,
        string $titulo,
        array $itens,
        string $vazio = 'Nenhuma informação foi encontrada.'
    ): array {
        $itens = array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $itens
        )));

        return [
            'id' => $id,
            'titulo' => $titulo,
            'itens' => $itens ?: [$vazio],
        ];
    }

    private function aplicarPeriodo(
        Builder $query,
        string $coluna,
        string $periodo
    ): void {
        $dias = match ($periodo) {
            '30' => 30,
            '60' => 60,
            '90' => 90,
            default => null,
        };

        if ($dias !== null) {
            $query->where($coluna, '>=', now()->subDays($dias)->startOfDay());
        }
    }

    private function adicionarCampo(array &$itens, string $rotulo, mixed $valor): void
    {
        if ($valor !== null && trim((string) $valor) !== '') {
            $itens[] = "{$rotulo}: {$valor}";
        }
    }

    private function textoLista(mixed $valor): string
    {
        $lista = $this->normalizarLista($valor);

        return $lista ? implode('; ', $lista) : 'nenhum informado';
    }

    private function normalizarLista(mixed $valor): array
    {
        if (is_array($valor)) {
            return array_values(array_filter(array_map(function ($item) {
                if (is_array($item)) {
                    return trim(implode(' ', array_filter(array_map(
                        fn ($valorInterno) => is_scalar($valorInterno)
                            ? (string) $valorInterno
                            : '',
                        $item
                    ))));
                }

                return trim((string) $item);
            }, $valor)));
        }

        if (!is_string($valor) || trim($valor) === '') {
            return [];
        }

        $json = json_decode($valor, true);

        if (is_array($json)) {
            return $this->normalizarLista($json);
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/[\n,;]+/u', $valor) ?: []
        )));
    }

    private function formatarMedicamentos(mixed $valor): string
    {
        if (is_string($valor)) {
            $json = json_decode($valor, true);
            $valor = is_array($json) ? $json : $valor;
        }

        if (!is_array($valor)) {
            return trim((string) $valor);
        }

        $itens = [];

        foreach ($valor as $medicamento) {
            if (is_string($medicamento)) {
                $itens[] = trim($medicamento);
                continue;
            }

            if (!is_array($medicamento)) {
                continue;
            }

            $nome = $medicamento['nome']
                ?? $medicamento['medicamento']
                ?? 'Medicamento';
            $detalhes = array_filter([
                $medicamento['dosagem'] ?? null,
                $medicamento['orientacao'] ?? null,
                $medicamento['frequencia'] ?? null,
            ]);

            $itens[] = $nome
                . ($detalhes ? ' (' . implode(' - ', $detalhes) . ')' : '');
        }

        return implode('; ', array_filter($itens));
    }
}
