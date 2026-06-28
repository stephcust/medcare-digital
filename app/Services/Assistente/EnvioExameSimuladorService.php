<?php

namespace App\Services\Assistente;

use App\Models\Exame;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EnvioExameSimuladorService
{
    private const CONTEXTO_MINUTOS = 30;

    /**
     * Interpreta pedidos para receber um exame no simulador.
     *
     * Retorna null quando a mensagem não é um pedido de arquivo de exame.
     * Quando reconhece o pedido, retorna texto e, se houver, o exame anexado.
     *
     * @return array{texto: string, exame_id: ?int, arquivo_nome: ?string}|null
     */
    public function processar(User $user, string $mensagem): ?array
    {
        $normalizada = $this->normalizar($mensagem);
        $contexto = Cache::get($this->chaveContexto($user));

        if (is_array($contexto)) {
            if ($this->ehCancelamento($normalizada)) {
                Cache::forget($this->chaveContexto($user));

                return $this->resposta(
                    'Tudo bem. Cancelei a seleção do exame.'
                );
            }

            $selecionado = $this->selecionarDoContexto(
                $user,
                $normalizada,
                $contexto
            );

            if ($selecionado !== null) {
                return $selecionado;
            }

            if (!$this->ehPedidoDeExame($normalizada)) {
                return $this->resposta(
                    'Ainda estou aguardando a escolha do exame. '
                    . 'Envie o número mostrado na lista ou escreva "cancelar".'
                );
            }

            Cache::forget($this->chaveContexto($user));
        }

        if (!$this->ehPedidoDeExame($normalizada)) {
            return null;
        }

        $consulta = $this->extrairConsulta($normalizada);
        $exames = $this->buscarExames($user, $consulta);

        if ($exames->isEmpty()) {
            return $this->respostaSemResultado($user, $consulta);
        }

        $comArquivo = $exames
            ->filter(fn (Exame $exame) => !empty($exame->arquivo_path))
            ->values();

        if ($comArquivo->isEmpty()) {
            $nome = $exames->first()?->nome ?? 'solicitado';

            return $this->resposta(
                "Encontrei o exame \"{$nome}\", mas ele não possui "
                . 'um arquivo anexado para download.'
            );
        }

        if ($comArquivo->count() === 1) {
            return $this->anexarExame($comArquivo->first());
        }

        $ids = $comArquivo->take(10)->pluck('id')->all();

        Cache::put(
            $this->chaveContexto($user),
            ['exame_ids' => $ids],
            now()->addMinutes(self::CONTEXTO_MINUTOS)
        );

        $linhas = [
            'Encontrei mais de um exame. Qual deles você deseja receber?',
            '',
        ];

        foreach ($comArquivo->take(10) as $indice => $exame) {
            $data = $exame->data_realizacao
                ? $exame->data_realizacao->format('d/m/Y')
                : 'data não informada';

            $linhas[] = ($indice + 1)
                . ". {$exame->nome} — {$data}";
        }

        $linhas[] = '';
        $linhas[] = 'Responda apenas com o número do exame.';

        return $this->resposta(implode("\n", $linhas));
    }

    private function selecionarDoContexto(
        User $user,
        string $mensagem,
        array $contexto
    ): ?array {
        $ids = array_values(array_filter(
            $contexto['exame_ids'] ?? [],
            fn ($id) => is_numeric($id)
        ));

        if (empty($ids)) {
            Cache::forget($this->chaveContexto($user));

            return null;
        }

        if (preg_match('/^\s*(\d{1,2})\s*$/', $mensagem, $matches)) {
            $indice = (int) $matches[1] - 1;

            if (!array_key_exists($indice, $ids)) {
                return $this->resposta(
                    'Esse número não está na lista. Escolha uma das opções '
                    . 'mostradas ou escreva "cancelar".'
                );
            }

            $exame = Exame::query()
                ->where('user_id', $user->id)
                ->whereKey((int) $ids[$indice])
                ->first();

            if (!$exame) {
                Cache::forget($this->chaveContexto($user));

                return $this->resposta(
                    'Esse exame não está mais disponível na sua conta.'
                );
            }

            Cache::forget($this->chaveContexto($user));

            return $this->anexarExame($exame);
        }

        if (
            preg_match(
                '/\b(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{4}))?\b/',
                $mensagem,
                $matches
            )
        ) {
            $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $ano = $matches[3] ?? null;

            $exames = Exame::query()
                ->where('user_id', $user->id)
                ->whereIn('id', $ids)
                ->get();

            $encontrado = $exames->first(function (Exame $exame) use (
                $dia,
                $mes,
                $ano
            ) {
                if (!$exame->data_realizacao) {
                    return false;
                }

                if ($exame->data_realizacao->format('d/m') !== "{$dia}/{$mes}") {
                    return false;
                }

                return $ano === null
                    || $exame->data_realizacao->format('Y') === $ano;
            });

            if ($encontrado) {
                Cache::forget($this->chaveContexto($user));

                return $this->anexarExame($encontrado);
            }
        }

        return null;
    }

    private function buscarExames(User $user, string $consulta): Collection
    {
        $exames = Exame::query()
            ->where('user_id', $user->id)
            ->orderByDesc('data_realizacao')
            ->orderByDesc('created_at')
            ->get();

        if ($consulta === '') {
            return $exames;
        }

        return $exames
            ->filter(function (Exame $exame) use ($consulta) {
                $nome = $this->normalizar($exame->nome ?? '');

                return Str::contains($nome, $consulta)
                    || Str::contains($consulta, $nome);
            })
            ->values();
    }

    private function respostaSemResultado(User $user, string $consulta): array
    {
        $disponiveis = Exame::query()
            ->where('user_id', $user->id)
            ->whereNotNull('arquivo_path')
            ->orderByDesc('data_realizacao')
            ->limit(5)
            ->get();

        $procurado = $consulta !== ''
            ? "\"{$consulta}\""
            : 'com esse nome';

        if ($disponiveis->isEmpty()) {
            return $this->resposta(
                "Não encontrei um exame {$procurado} com arquivo anexado "
                . 'na sua conta.'
            );
        }

        $linhas = [
            "Não encontrei um exame {$procurado}.",
            '',
            'Exames com arquivo disponíveis:',
        ];

        foreach ($disponiveis as $exame) {
            $data = $exame->data_realizacao
                ? $exame->data_realizacao->format('d/m/Y')
                : 'data não informada';

            $linhas[] = "• {$exame->nome} — {$data}";
        }

        $linhas[] = '';
        $linhas[] = 'Tente pedir novamente usando um desses nomes.';

        return $this->resposta(implode("\n", $linhas));
    }

    private function anexarExame(Exame $exame): array
    {
        if (!$exame->arquivo_path) {
            return $this->resposta(
                "Encontrei o exame \"{$exame->nome}\", mas ele não possui "
                . 'arquivo anexado.'
            );
        }

        $data = $exame->data_realizacao
            ? $exame->data_realizacao->format('d/m/Y')
            : 'não informada';

        $arquivoNome = $this->nomeArquivo($exame);

        return [
            'texto' => "Encontrei o seu exame.\n\n"
                . "Exame: {$exame->nome}\n"
                . "Data: {$data}\n\n"
                . 'Use o botão abaixo para baixar o arquivo.',
            'exame_id' => $exame->id,
            'arquivo_nome' => $arquivoNome,
        ];
    }

    private function nomeArquivo(Exame $exame): string
    {
        $extensao = strtolower(
            pathinfo((string) $exame->arquivo_path, PATHINFO_EXTENSION)
        );

        if ($extensao === '') {
            $extensao = 'pdf';
        }

        return Str::slug($exame->nome ?: 'exame')
            . ".{$extensao}";
    }

    private function ehPedidoDeExame(string $mensagem): bool
    {
        $temAcao = Str::contains($mensagem, [
            'envie',
            'enviar',
            'manda',
            'mande',
            'mandar',
            'receber',
            'quero receber',
            'quero baixar',
            'baixar',
            'download',
            'abra',
            'abrir',
            'mostre o arquivo',
            'me passe',
            'me da',
        ]);

        $temDocumento = Str::contains($mensagem, [
            'exame',
            'laudo',
            'resultado',
            'pdf',
            'arquivo',
            'documento',
        ]);

        return $temAcao && $temDocumento;
    }

    private function ehCancelamento(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'cancelar',
            'cancela',
            'deixa pra la',
            'deixe pra la',
            'nao quero mais',
        ]);
    }

    private function extrairConsulta(string $mensagem): string
    {
        $consulta = $mensagem;

        $padroes = [
            '/\b(por favor|pra mim|para mim|pelo whatsapp|no whatsapp)\b/u',
            '/\b(envie|enviar|manda|mande|mandar|receber|baixar|download|abra|abrir|mostre|passe)\b/u',
            '/\b(quero|gostaria|preciso|pode|consegue|voce)\b/u',
            '/\b(me|meu|minha|o|a|um|uma|do|da|de|em|pelo|arquivo|documento|pdf|exame|laudo|resultado)\b/u',
        ];

        foreach ($padroes as $padrao) {
            $consulta = preg_replace($padrao, ' ', $consulta);
        }

        $consulta = preg_replace('/[^a-z0-9\s]/u', ' ', $consulta);
        $consulta = preg_replace('/\s+/', ' ', $consulta);

        return trim($consulta);
    }

    private function resposta(string $texto): array
    {
        return [
            'texto' => $texto,
            'exame_id' => null,
            'arquivo_nome' => null,
        ];
    }

    private function chaveContexto(User $user): string
    {
        return "medcare:simulador:selecao-exame:{$user->id}";
    }

    private function normalizar(string $texto): string
    {
        return Str::lower(Str::ascii(trim($texto)));
    }
}
