<?php

namespace App\Services\Assistente;

use App\Models\HistoricoClinico;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EnvioHistoricoClinicoSimuladorService
{
    private const CONTEXTO_MINUTOS = 30;

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

        if ($normalizada === '') {
            return null;
        }

        $selecao = Cache::get($this->chaveSelecao($user));

        if (is_array($selecao)) {
            if ($this->ehCancelamento($normalizada)) {
                Cache::forget($this->chaveSelecao($user));

                return $this->resposta('Seleção de atendimento cancelada.');
            }

            if (preg_match('/^\s*(\d{1,2})\s*$/', $normalizada, $matches)) {
                $ids = array_values(array_filter(
                    $selecao['ids'] ?? [],
                    fn ($id) => is_numeric($id)
                ));
                $indice = (int) $matches[1] - 1;

                if (!array_key_exists($indice, $ids)) {
                    return $this->resposta(
                        'Esse número não está na lista. Escolha uma das opções '
                        . 'mostradas ou escreva "cancelar".'
                    );
                }

                $registro = $this->buscarPorId($user, (int) $ids[$indice]);
                Cache::forget($this->chaveSelecao($user));

                return $registro
                    ? $this->anexar($user, $registro)
                    : $this->resposta(
                        'Esse atendimento não está mais disponível.'
                    );
            }
        }

        $mencionaHistorico = $this->mencionaHistorico($normalizada);
        $pedeArquivo = $this->temAcaoDeEnvio($normalizada)
            && $this->mencionaArquivo($normalizada);

        if (!$mencionaHistorico && $pedeArquivo) {
            $ultimoId = Cache::get($this->chaveUltimo($user));

            if (is_numeric($ultimoId)) {
                $registro = $this->buscarPorId($user, (int) $ultimoId);

                if ($registro) {
                    return $this->anexar($user, $registro);
                }
            }

            return null;
        }

        if (!$mencionaHistorico) {
            return null;
        }

        $consulta = $this->extrairConsulta($normalizada);
        $registros = $this->buscar($user, $consulta);

        if ($registros->count() === 1) {
            Cache::put(
                $this->chaveUltimo($user),
                (int) $registros->first()->id,
                now()->addMinutes(self::CONTEXTO_MINUTOS)
            );
        }

        if (!$this->temAcaoDeEnvio($normalizada)) {
            return null;
        }

        if ($registros->isEmpty()) {
            return $this->resposta(
                'Não encontrei atendimentos clínicos relacionados ao pedido '
                . 'na sua conta.'
            );
        }

        if ($registros->count() === 1) {
            return $this->anexar($user, $registros->first());
        }

        $ids = $registros->take(10)->pluck('id')->all();
        Cache::put(
            $this->chaveSelecao($user),
            ['ids' => $ids],
            now()->addMinutes(self::CONTEXTO_MINUTOS)
        );

        $linhas = [
            'Encontrei mais de um atendimento. Qual você deseja receber?',
            '',
        ];

        foreach ($registros->take(10) as $indice => $registro) {
            $data = $registro->data_atendimento
                ?->format('d/m/Y H:i') ?? 'data não informada';
            $linhas[] = ($indice + 1) . '. '
                . "{$registro->motivo_atendimento} — "
                . "{$registro->local_atendimento} — {$data}";
        }

        $linhas[] = '';
        $linhas[] = 'Responda apenas com o número do atendimento.';

        return $this->resposta(implode("\n", $linhas));
    }

    private function buscar(User $user, string $consulta): Collection
    {
        $registros = HistoricoClinico::query()
            ->whereHas(
                'paciente',
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->orderByDesc('data_atendimento')
            ->get();

        if ($consulta === '') {
            return $registros;
        }

        $tokens = $this->tokens($consulta);

        return $registros->filter(function (HistoricoClinico $registro) use (
            $consulta,
            $tokens
        ) {
            $texto = $this->normalizar(implode(' ', array_filter([
                $registro->motivo_atendimento,
                $registro->local_atendimento,
                $registro->medico_nome,
                $registro->diagnostico,
                $registro->desfecho,
            ])));

            if (
                Str::contains($texto, $consulta)
                || Str::contains($consulta, $texto)
            ) {
                return true;
            }

            return count(array_intersect($tokens, $this->tokens($texto))) >= 1;
        })->values();
    }

    private function buscarPorId(
        User $user,
        int $id
    ): ?HistoricoClinico {
        return HistoricoClinico::query()
            ->whereHas(
                'paciente',
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->whereKey($id)
            ->first();
    }

    private function anexar(
        User $user,
        HistoricoClinico $registro
    ): array {
        Cache::put(
            $this->chaveUltimo($user),
            (int) $registro->id,
            now()->addMinutes(self::CONTEXTO_MINUTOS)
        );

        $data = $registro->data_atendimento
            ?->format('d/m/Y H:i') ?? 'não informada';
        $temOriginal = !empty($registro->arquivo_path);

        return [
            'texto' => "Encontrei o atendimento no seu Histórico Clínico.\n\n"
                . "Motivo: {$registro->motivo_atendimento}\n"
                . "Local: {$registro->local_atendimento}\n"
                . "Data: {$data}\n\n"
                . ($temOriginal
                    ? 'Use os botões abaixo para visualizar ou baixar o documento.'
                    : 'Como não há documento original anexado, o MedCare preparou '
                        . 'um resumo pessoal em PDF.'),
            'exame_id' => null,
            'receita_id' => null,
            'vacinacao_id' => null,
            'historico_clinico_id' => (int) $registro->id,
            'arquivo_nome' => $this->nomeArquivo($registro),
        ];
    }

    private function nomeArquivo(HistoricoClinico $registro): string
    {
        if ($registro->arquivo_path) {
            $extensao = strtolower(pathinfo(
                $registro->arquivo_path,
                PATHINFO_EXTENSION
            ));

            if ($extensao !== '') {
                return 'atendimento-'
                    . Str::slug($registro->local_atendimento ?: 'clinico')
                    . ".{$extensao}";
            }
        }

        $data = $registro->data_atendimento?->format('Y-m-d') ?? 'sem-data';

        return "resumo-atendimento-{$data}.pdf";
    }

    private function mencionaHistorico(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'historico clinico',
            'historico de pronto socorro',
            'historico do pronto socorro',
            'atendimento do pronto socorro',
            'atendimento no pronto socorro',
            'atendimento hospitalar',
            'passagem pelo hospital',
            'relatorio de alta',
            'pronto socorro',
            'pronto-socorro',
        ]);
    }

    private function temAcaoDeEnvio(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'envie',
            'enviar',
            'manda',
            'mande',
            'mandar',
            'receber',
            'baixar',
            'download',
            'abra',
            'abrir',
            'mostre',
            'me passe',
            'me da',
        ]);
    }

    private function mencionaArquivo(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'pdf',
            'arquivo',
            'documento',
            'relatorio',
            'anexo',
        ]);
    }

    private function extrairConsulta(string $mensagem): string
    {
        $padroes = [
            '/\b(por favor|pra mim|para mim|aqui|no whatsapp|pelo whatsapp)\b/u',
            '/\b(envie|enviar|manda|mande|mandar|receber|baixar|download|abra|abrir|mostre|passe)\b/u',
            '/\b(quero|gostaria|preciso|pode|consegue|voce|vc)\b/u',
            '/\b(me|meu|minha|o|a|um|uma|do|da|de|em|com|arquivo|documento|pdf|relatorio|anexo)\b/u',
            '/\b(historico|clinico|pronto|socorro|atendimento|hospitalar|passagem)\b/u',
        ];

        $consulta = $mensagem;

        foreach ($padroes as $padrao) {
            $consulta = preg_replace($padrao, ' ', $consulta);
        }

        $consulta = preg_replace('/[^a-z0-9\s]/u', ' ', $consulta);
        $consulta = preg_replace('/\s+/', ' ', $consulta);

        return trim($consulta);
    }

    private function tokens(string $texto): array
    {
        $ignoradas = [
            'historico', 'clinico', 'pronto', 'socorro', 'hospital',
            'atendimento', 'relatorio', 'documento', 'arquivo', 'pdf',
            'meu', 'minha', 'completo',
        ];

        $tokens = preg_split('/\s+/', $this->normalizar($texto)) ?: [];

        return array_values(array_filter(
            array_unique($tokens),
            fn ($token) => strlen($token) >= 3
                && !in_array($token, $ignoradas, true)
        ));
    }

    private function ehCancelamento(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'cancelar',
            'cancela',
            'deixa pra la',
            'deixe pra la',
        ]);
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

    private function chaveSelecao(User $user): string
    {
        return "medcare:historico-clinico:selecao:{$user->id}";
    }

    private function chaveUltimo(User $user): string
    {
        return "medcare:historico-clinico:ultimo:{$user->id}";
    }

    private function normalizar(string $texto): string
    {
        return Str::lower(Str::ascii(trim($texto)));
    }
}
