<?php

namespace App\Services\Pendencias;

use App\Models\Exame;
use App\Models\Lembrete;
use App\Models\Receita;
use App\Models\User;
use App\Models\Vacinacao;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PendenciaSaudeService
{
    private const JANELA_ALERTA_DIAS = 30;

    /**
     * @return array{
     *     automaticas: array<int, array<string, mixed>>,
     *     resumo: array{atrasadas:int, hoje:int, proximas:int, automaticas:int},
     *     destaques: array<int, array<string, mixed>>
     * }
     */
    public function montar(User $user): array
    {
        $automaticas = collect()
            ->merge($this->pendenciasVacinas($user))
            ->merge($this->pendenciasReceitas($user))
            ->merge($this->pendenciasExames($user))
            ->sortBy(fn (array $item) => [
                $this->pesoSituacao($item['situacao']),
                $item['data_referencia'] ?? '9999-12-31',
            ])
            ->values();

        $lembretesPendentes = Lembrete::query()
            ->where('user_id', $user->id)
            ->where('status', 'pendente')
            ->where('ativo', true)
            ->get();

        $situacoesLembretes = $lembretesPendentes
            ->map(fn (Lembrete $lembrete) => $this->situacaoDaData($lembrete->data_hora))
            ->filter();

        $resumo = [
            'atrasadas' => $situacoesLembretes->where('situacao', 'atrasado')->count()
                + $automaticas->where('situacao', 'atrasado')->count(),
            'hoje' => $situacoesLembretes->where('situacao', 'hoje')->count()
                + $automaticas->where('situacao', 'hoje')->count(),
            'proximas' => $situacoesLembretes->where('situacao', 'proximo')->count()
                + $automaticas->where('situacao', 'proximo')->count(),
            'automaticas' => $automaticas->count(),
        ];

        return [
            'automaticas' => $automaticas->all(),
            'resumo' => $resumo,
            'destaques' => $automaticas->take(5)->all(),
        ];
    }

    /**
     * @return array{situacao:string, rotulo:string}
     */
    public function situacaoDaData(Carbon|string|null $data): array
    {
        if (!$data) {
            return [
                'situacao' => 'sem_data',
                'rotulo' => 'Sem data',
            ];
        }

        $momento = $data instanceof Carbon
            ? $data->copy()
            : Carbon::parse($data);

        $agora = now();

        if ($momento->lt($agora)) {
            return [
                'situacao' => 'atrasado',
                'rotulo' => 'Atrasado',
            ];
        }

        if ($momento->isToday()) {
            return [
                'situacao' => 'hoje',
                'rotulo' => 'Hoje',
            ];
        }

        if ($momento->lte($agora->copy()->addDays(7))) {
            return [
                'situacao' => 'proximo',
                'rotulo' => 'Próximo',
            ];
        }

        return [
            'situacao' => 'futuro',
            'rotulo' => 'Agendado',
        ];
    }

    private function pendenciasVacinas(User $user): Collection
    {
        $pacienteId = $user->paciente?->id;

        if (!$pacienteId) {
            return collect();
        }

        return Vacinacao::query()
            ->where('paciente_id', $pacienteId)
            ->whereNotNull('data_proxima_dose')
            ->orderBy('data_proxima_dose')
            ->get()
            ->map(function (Vacinacao $vacinacao) use ($pacienteId) {
                $situacao = $this->situacaoAutomatica($vacinacao->data_proxima_dose);

                if ($situacao === null) {
                    return null;
                }

                return [
                    'id' => "vacina-{$vacinacao->id}",
                    'tipo' => 'vacina',
                    'titulo' => "Próxima dose: {$vacinacao->nome_vacina}",
                    'descricao' => trim(
                        ($vacinacao->numero_dose ? "Dose atual: {$vacinacao->numero_dose}. " : '')
                        . 'Confira a data registrada e mantenha o calendário atualizado.'
                    ),
                    'data_referencia' => $vacinacao->data_proxima_dose?->toIso8601String(),
                    'situacao' => $situacao['situacao'],
                    'rotulo_situacao' => $situacao['rotulo'],
                    'origem' => 'sistema',
                    'href' => route('vacinacoes.index', $pacienteId),
                ];
            })
            ->filter()
            ->values();
    }

    private function pendenciasReceitas(User $user): Collection
    {
        return Receita::query()
            ->where('user_id', $user->id)
            ->whereNotNull('data_validade')
            ->orderBy('data_validade')
            ->get()
            ->map(function (Receita $receita) use ($user) {
                $situacao = $this->situacaoAutomatica($receita->data_validade);

                if ($situacao === null) {
                    return null;
                }

                $medico = $receita->medico ?: 'profissional não informado';

                return [
                    'id' => "receita-{$receita->id}",
                    'tipo' => 'prescricao',
                    'titulo' => "Validade da prescrição de {$medico}",
                    'descricao' => $situacao['situacao'] === 'atrasado'
                        ? 'A prescrição cadastrada está vencida. Atualize o registro quando receber uma nova.'
                        : 'A prescrição está próxima da data de validade cadastrada.',
                    'data_referencia' => $receita->data_validade?->toIso8601String(),
                    'situacao' => $situacao['situacao'],
                    'rotulo_situacao' => $situacao['rotulo'],
                    'origem' => 'sistema',
                    'href' => $user->paciente
                        ? route('receitas.index', $user->paciente->id)
                        : route('dashboard'),
                ];
            })
            ->filter()
            ->values();
    }

    private function pendenciasExames(User $user): Collection
    {
        return Exame::query()
            ->where('user_id', $user->id)
            ->where('visualizado', false)
            ->orderByDesc('data_realizacao')
            ->limit(10)
            ->get()
            ->map(function (Exame $exame) {
                return [
                    'id' => "exame-{$exame->id}",
                    'tipo' => 'exame',
                    'titulo' => "Exame ainda não revisado: {$exame->nome}",
                    'descricao' => 'O arquivo foi cadastrado, mas ainda não foi aberto no MedCare.',
                    'data_referencia' => $exame->data_realizacao?->toIso8601String(),
                    'situacao' => 'proximo',
                    'rotulo_situacao' => 'Revisar',
                    'origem' => 'sistema',
                    'href' => route('exames.show', $exame->id),
                ];
            })
            ->values();
    }

    /**
     * Retorna null quando a data ainda está fora da janela de aviso.
     *
     * @return array{situacao:string, rotulo:string}|null
     */
    private function situacaoAutomatica(Carbon|string|null $data): ?array
    {
        if (!$data) {
            return null;
        }

        $momento = $data instanceof Carbon
            ? $data->copy()->startOfDay()
            : Carbon::parse($data)->startOfDay();
        $hoje = today();

        if ($momento->lt($hoje)) {
            return [
                'situacao' => 'atrasado',
                'rotulo' => 'Atrasado',
            ];
        }

        if ($momento->isSameDay($hoje)) {
            return [
                'situacao' => 'hoje',
                'rotulo' => 'Hoje',
            ];
        }

        if ($momento->lte($hoje->copy()->addDays(7))) {
            return [
                'situacao' => 'proximo',
                'rotulo' => 'Até 7 dias',
            ];
        }

        if ($momento->lte($hoje->copy()->addDays(self::JANELA_ALERTA_DIAS))) {
            return [
                'situacao' => 'futuro',
                'rotulo' => 'Até 30 dias',
            ];
        }

        return null;
    }

    private function pesoSituacao(string $situacao): int
    {
        return match ($situacao) {
            'atrasado' => 0,
            'hoje' => 1,
            'proximo' => 2,
            default => 3,
        };
    }
}
