<?php

namespace App\Services\Assistente;

use App\Models\Lembrete;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RecorrenciaLembreteService
{
    /**
     * Cria vários lembretes com um intervalo fixo de horas.
     *
     * Exemplo:
     * tomar um medicamento de 8 em 8 horas durante 7 dias.
     */
    public function criarPorIntervalo(
        User $user,
        string $titulo,
        string $tipo,
        Carbon $inicio,
        int $intervaloHoras,
        int $duracaoDias,
        ?string $descricao = null
    ): Collection {
        if ($intervaloHoras < 1 || $intervaloHoras > 24) {
            throw new InvalidArgumentException(
                'O intervalo deve estar entre 1 e 24 horas.'
            );
        }

        if ($duracaoDias < 1 || $duracaoDias > 365) {
            throw new InvalidArgumentException(
                'A duração deve estar entre 1 e 365 dias.'
            );
        }

        $inicio = $inicio->copy()->setSecond(0);
        $fim = $inicio->copy()->addDays($duracaoDias);

        $ocorrencias = collect();
        $dataHoraAtual = $inicio->copy();

        while ($dataHoraAtual->lt($fim)) {
            $ocorrencias->push($dataHoraAtual->copy());

            $dataHoraAtual->addHours($intervaloHoras);

            if ($ocorrencias->count() > 500) {
                throw new InvalidArgumentException(
                    'A quantidade de lembretes ultrapassou o limite permitido.'
                );
            }
        }

        $serieId = (string) Str::uuid();

        return DB::transaction(function () use (
            $user,
            $titulo,
            $tipo,
            $descricao,
            $inicio,
            $fim,
            $intervaloHoras,
            $serieId,
            $ocorrencias
        ) {
            return $ocorrencias->map(function (Carbon $dataHora) use (
                $user,
                $titulo,
                $tipo,
                $descricao,
                $inicio,
                $fim,
                $intervaloHoras,
                $serieId
            ) {
                return Lembrete::create([
                    'user_id' => $user->id,
                    'tipo' => $tipo,
                    'titulo' => $titulo,
                    'descricao' => $descricao,
                    'data_hora' => $dataHora,
                    'status' => 'pendente',
                    'concluido_em' => null,

                    'frequencia' => 'intervalo_horas',
                    'horarios' => [
                        $dataHora->format('H:i'),
                    ],

                    'serie_id' => $serieId,
                    'recorrente' => true,
                    'intervalo_horas' => $intervaloHoras,
                    'data_inicio' => $inicio,
                    'data_fim' => $fim,
                    'dias_semana' => null,
                    'ativo' => true,
                    'enviado_em' => null,
                ]);
            });
        });
    }
}