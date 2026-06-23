<?php

namespace App\Services\Assistente;

use App\Models\Lembrete;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class LembreteChatService
{
    private const CONTEXTO_MINUTOS = 30;

    public function processar(User $user, string $mensagem): ?string
    {
        $mensagemNormalizada = $this->normalizar($mensagem);

        if ($this->ehListagem($mensagemNormalizada)) {
            return $this->listar($user);
        }

        if ($this->ehConclusao($mensagemNormalizada)) {
            return $this->concluir($user, $mensagemNormalizada);
        }

        if ($this->ehCancelamento($mensagemNormalizada)) {
            Cache::forget($this->chaveContexto($user));

            return 'Tudo bem. Cancelei o preenchimento desse lembrete.';
        }

        $contexto = Cache::get($this->chaveContexto($user));

        if (is_array($contexto)) {
            return $this->continuarCriacao(
                $user,
                $mensagemNormalizada,
                $contexto
            );
        }

        if ($this->ehCriacao($mensagemNormalizada)) {
            return $this->iniciarCriacao(
                $user,
                $mensagem,
                $mensagemNormalizada
            );
        }

        return null;
    }

    private function ehCriacao(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'me lembre',
            'lembre me',
            'lembre-me',
            'me lembra',
            'me lembrar',
            'lembrar que',
            'crie um lembrete',
            'criar lembrete',
            'cria um lembrete',
            'agende um lembrete',
            'adicionar lembrete',
            'quero que me lembre',
        ]);
    }

    private function ehListagem(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'meus lembretes',
            'quais meus lembretes',
            'listar lembretes',
            'ver lembretes',
            'lembretes pendentes',
            'tenho lembretes',
        ]);
    }

    private function ehConclusao(string $mensagem): bool
    {
        return Str::contains($mensagem, ['concluir', 'finalizar', 'marcar'])
            && Str::contains($mensagem, ['lembrete', 'aviso']);
    }

    private function ehCancelamento(string $mensagem): bool
    {
        return Str::contains($mensagem, [
            'cancelar esse lembrete',
            'cancela esse lembrete',
            'deixa pra la',
            'deixe pra la',
            'esquece esse lembrete',
            'esqueca esse lembrete',
        ]);
    }

    private function iniciarCriacao(
        User $user,
        string $mensagemOriginal,
        string $mensagemNormalizada
    ): string {
        $titulo = $this->extrairTitulo($mensagemOriginal);

        if (!$titulo) {
            $titulo = 'Lembrete do MedCare';
        }

        $intervaloHoras = $this->extrairIntervaloHoras(
            $mensagemNormalizada
        );

        $duracaoDias = $this->extrairDuracaoDias(
            $mensagemNormalizada
        );

        $contexto = [
            'modo' => ($intervaloHoras !== null || $duracaoDias !== null)
                ? 'intervalo'
                : 'unico',
            'titulo' => $titulo,
            'tipo' => $this->identificarTipo($mensagemNormalizada),
            'intervalo_horas' => $intervaloHoras,
            'duracao_dias' => $duracaoDias,
            'data_hora' => $this->dataParaCache(
                $this->extrairDataHora($mensagemNormalizada)
            ),
        ];

        return $this->finalizarOuPerguntar($user, $contexto);
    }

    private function continuarCriacao(
        User $user,
        string $mensagemNormalizada,
        array $contexto
    ): string {
        $intervaloHoras = $this->extrairIntervaloHoras(
            $mensagemNormalizada
        );

        $duracaoDias = $this->extrairDuracaoDias(
            $mensagemNormalizada
        );

        $dataHora = $this->extrairDataHora($mensagemNormalizada);

        if ($intervaloHoras !== null) {
            $contexto['intervalo_horas'] = $intervaloHoras;
            $contexto['modo'] = 'intervalo';
        }

        if ($duracaoDias !== null) {
            $contexto['duracao_dias'] = $duracaoDias;
            $contexto['modo'] = 'intervalo';
        }

        if ($dataHora !== null) {
            $contexto['data_hora'] = $this->dataParaCache($dataHora);
        }

        return $this->finalizarOuPerguntar($user, $contexto);
    }

    private function finalizarOuPerguntar(
        User $user,
        array $contexto
    ): string {
        if (($contexto['modo'] ?? 'unico') === 'intervalo') {
            if (empty($contexto['intervalo_horas'])) {
                return $this->salvarContextoEPerguntar(
                    $user,
                    $contexto,
                    'De quanto em quanto tempo o lembrete deve se repetir? '
                    . 'Exemplo: "de 8 em 8 horas".'
                );
            }

            if (empty($contexto['duracao_dias'])) {
                return $this->salvarContextoEPerguntar(
                    $user,
                    $contexto,
                    'Por quantos dias esse tratamento deve durar? '
                    . 'Exemplo: "por 5 dias".'
                );
            }

            if (empty($contexto['data_hora'])) {
                return $this->salvarContextoEPerguntar(
                    $user,
                    $contexto,
                    'Quando deve começar e qual será o primeiro horário? '
                    . 'Exemplo: "amanhã às 8h".'
                );
            }

            return $this->criarSerie($user, $contexto);
        }

        if (empty($contexto['data_hora'])) {
            return $this->salvarContextoEPerguntar(
                $user,
                $contexto,
                'Quando esse lembrete deve acontecer? '
                . 'Exemplo: "amanhã às 8h".'
            );
        }

        return $this->criarUnico($user, $contexto);
    }

    private function salvarContextoEPerguntar(
        User $user,
        array $contexto,
        string $pergunta
    ): string {
        Cache::put(
            $this->chaveContexto($user),
            $contexto,
            now()->addMinutes(self::CONTEXTO_MINUTOS)
        );

        return "Entendi o lembrete para \"{$contexto['titulo']}\".\n\n"
            . $pergunta;
    }

    private function criarSerie(User $user, array $contexto): string
    {
        try {
            $inicio = Carbon::parse($contexto['data_hora']);

            $lembretes = app(RecorrenciaLembreteService::class)
                ->criarPorIntervalo(
                    user: $user,
                    titulo: $contexto['titulo'],
                    tipo: $contexto['tipo'],
                    inicio: $inicio,
                    intervaloHoras: (int) $contexto['intervalo_horas'],
                    duracaoDias: (int) $contexto['duracao_dias']
                );
        } catch (Throwable $e) {
            report($e);

            return 'Não consegui criar essa série de lembretes. '
                . 'Confira o intervalo, a duração e o horário informados.';
        }

        Cache::forget($this->chaveContexto($user));

        $primeiro = $lembretes->first();
        $ultimo = $lembretes->last();

        return "Tratamento organizado com sucesso ✅\n\n"
            . "Tipo: {$this->nomeTipo($contexto['tipo'])}\n"
            . "Título: {$contexto['titulo']}\n"
            . "Frequência: a cada {$contexto['intervalo_horas']} horas\n"
            . "Duração: {$contexto['duracao_dias']} dia(s)\n"
            . "Primeiro lembrete: {$primeiro->data_hora->format('d/m/Y H:i')}\n"
            . "Último lembrete: {$ultimo->data_hora->format('d/m/Y H:i')}\n"
            . "Total de lembretes: {$lembretes->count()}";
    }

    private function criarUnico(User $user, array $contexto): string
    {
        $dataHora = Carbon::parse($contexto['data_hora']);

        $lembrete = Lembrete::create([
            'user_id' => $user->id,
            'tipo' => $contexto['tipo'],
            'titulo' => $contexto['titulo'],
            'descricao' => null,
            'data_hora' => $dataHora,
            'status' => 'pendente',
            'recorrente' => false,
            'ativo' => true,
        ]);

        Cache::forget($this->chaveContexto($user));

        return "Lembrete criado com sucesso ✅\n\n"
            . "Código: {$lembrete->id}\n"
            . "Tipo: {$this->nomeTipo($contexto['tipo'])}\n"
            . "Título: {$lembrete->titulo}\n"
            . "Data e horário: {$dataHora->format('d/m/Y H:i')}";
    }

    private function listar(User $user): string
    {
        $lembretes = Lembrete::where('user_id', $user->id)
            ->where('status', 'pendente')
            ->where('ativo', true)
            ->orderBy('data_hora')
            ->limit(10)
            ->get();

        if ($lembretes->isEmpty()) {
            return 'Você não tem lembretes pendentes no momento.';
        }

        $resposta = "Seus próximos lembretes pendentes:\n\n";

        foreach ($lembretes as $lembrete) {
            $dataHora = Carbon::parse($lembrete->data_hora)
                ->format('d/m/Y H:i');

            $resposta .= "{$lembrete->id}. {$lembrete->titulo}\n";
            $resposta .= "Tipo: {$this->nomeTipo($lembrete->tipo)}\n";
            $resposta .= "Quando? {$dataHora}\n";

            if ($lembrete->recorrente) {
                $resposta .= "Série recorrente: sim\n";
            }

            $resposta .= "\n";
        }

        $resposta .= 'Para concluir um lembrete, envie: '
            . '"concluir lembrete 1".';

        return trim($resposta);
    }

    private function concluir(User $user, string $mensagem): string
    {
        preg_match('/\d+/', $mensagem, $matches);

        if (empty($matches)) {
            $pendentes = Lembrete::where('user_id', $user->id)
                ->where('status', 'pendente')
                ->where('ativo', true)
                ->orderBy('data_hora')
                ->get();

            if ($pendentes->isEmpty()) {
                return 'Você não tem lembretes pendentes para concluir.';
            }

            if ($pendentes->count() > 1) {
                return 'Encontrei mais de um lembrete pendente. Informe '
                    . 'o número do lembrete que deseja concluir. '
                    . 'Exemplo: "concluir lembrete 1".';
            }

            $lembrete = $pendentes->first();

            $lembrete->update([
                'status' => 'concluido',
                'concluido_em' => now(),
            ]);

            return "Lembrete concluído ✅\n\n{$lembrete->titulo}";
        }

        $id = (int) $matches[0];

        $lembrete = Lembrete::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$lembrete) {
            return 'Não encontrei esse lembrete na sua conta.';
        }

        if ($lembrete->status === 'concluido') {
            return 'Esse lembrete já estava marcado como concluído.';
        }

        $lembrete->update([
            'status' => 'concluido',
            'concluido_em' => now(),
        ]);

        return "Lembrete concluído ✅\n\n{$lembrete->titulo}";
    }

    private function extrairDataHora(string $mensagem): ?Carbon
    {
        $data = null;

        if (Str::contains($mensagem, 'depois de amanha')) {
            $data = now()->addDays(2);
        } elseif (Str::contains($mensagem, 'amanha')) {
            $data = now()->addDay();
        } elseif (Str::contains($mensagem, 'hoje')) {
            $data = now();
        } elseif (Str::contains($mensagem, 'segunda')) {
            $data = now()->next(Carbon::MONDAY);
        } elseif (Str::contains($mensagem, 'terca')) {
            $data = now()->next(Carbon::TUESDAY);
        } elseif (Str::contains($mensagem, 'quarta')) {
            $data = now()->next(Carbon::WEDNESDAY);
        } elseif (Str::contains($mensagem, 'quinta')) {
            $data = now()->next(Carbon::THURSDAY);
        } elseif (Str::contains($mensagem, 'sexta')) {
            $data = now()->next(Carbon::FRIDAY);
        } elseif (Str::contains($mensagem, 'sabado')) {
            $data = now()->next(Carbon::SATURDAY);
        } elseif (Str::contains($mensagem, 'domingo')) {
            $data = now()->next(Carbon::SUNDAY);
        }

        if (
            !$data
            && preg_match(
                '/(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{4}))?/',
                $mensagem,
                $matches
            )
        ) {
            $dia = (int) $matches[1];
            $mes = (int) $matches[2];
            $ano = isset($matches[3])
                ? (int) $matches[3]
                : (int) now()->year;

            try {
                $data = Carbon::create($ano, $mes, $dia);
            } catch (Throwable) {
                return null;
            }
        }

        if (!$data) {
            return null;
        }

        $mensagemSemIntervalo = preg_replace(
            '/\bde\s+\d{1,2}\s*h?\s+em\s+\d{1,2}\s*h?'
            . '(?:\s*horas?)?\b/u',
            '',
            $mensagem
        );

        if (
            preg_match(
                '/\bas\s*(\d{1,2})(?:(?:h|:)(\d{2})?)?\b/u',
                $mensagemSemIntervalo,
                $matches
            )
            || preg_match(
                '/\b(\d{1,2})(?:h|:)(\d{2})?\b/u',
                $mensagemSemIntervalo,
                $matches
            )
        ) {
            $hora = (int) $matches[1];
            $minuto = isset($matches[2]) && $matches[2] !== ''
                ? (int) $matches[2]
                : 0;
        } else {
            return null;
        }

        if ($hora > 23 || $minuto > 59) {
            return null;
        }

        return $data->setTime($hora, $minuto, 0);
    }

    private function extrairIntervaloHoras(string $mensagem): ?int
    {
        if (
            preg_match(
                '/\bde\s+(\d{1,2})\s*h?\s+em\s+\1\s*h?'
                . '(?:\s*horas?)?\b/u',
                $mensagem,
                $matches
            )
        ) {
            return (int) $matches[1];
        }

        if (
            preg_match(
                '/\ba\s+cada\s+(\d{1,2})\s*(?:h|horas?)\b/u',
                $mensagem,
                $matches
            )
        ) {
            return (int) $matches[1];
        }

        return null;
    }

    private function extrairDuracaoDias(string $mensagem): ?int
    {
        // Exemplos aceitos:
        // "7 dias", "por 7 dias", "durante 7 dias",
        // "vai durar 7 dias" e "duração de 7 dias".
        if (
            preg_match(
                '/\b(?:(?:por|durante|vai durar|dura|duracao(?: de)?)\s+)?'
                . '(\d{1,3})\s+dias?\b/u',
                $mensagem,
                $matches
            )
        ) {
            return (int) $matches[1];
        }

        // Exemplos:
        // "1 semana", "por 2 semanas" e "vai durar 3 semanas".
        if (
            preg_match(
                '/\b(?:(?:por|durante|vai durar|dura|duracao(?: de)?)\s+)?'
                . '(\d{1,2})\s+semanas?\b/u',
                $mensagem,
                $matches
            )
        ) {
            return (int) $matches[1] * 7;
        }

        // Permite quantidades escritas por extenso.
        $numeros = [
            'um' => 1,
            'uma' => 1,
            'dois' => 2,
            'duas' => 2,
            'tres' => 3,
            'quatro' => 4,
            'cinco' => 5,
            'seis' => 6,
            'sete' => 7,
            'oito' => 8,
            'nove' => 9,
            'dez' => 10,
            'onze' => 11,
            'doze' => 12,
        ];

        if (
            preg_match(
                '/\b(?:(?:por|durante|vai durar|dura|duracao(?: de)?)\s+)?'
                . '(um|uma|dois|duas|tres|quatro|cinco|seis|sete|oito|nove|dez|onze|doze)'
                . '\s+(dia|dias|semana|semanas)\b/u',
                $mensagem,
                $matches
            )
        ) {
            $quantidade = $numeros[$matches[1]];
            $unidade = $matches[2];

            return Str::startsWith($unidade, 'semana')
                ? $quantidade * 7
                : $quantidade;
        }

        return null;
    }

    private function extrairTitulo(string $mensagem): string
    {
        $titulo = $mensagem;

        $padroes = [
            '/quero que me lembre de/iu',
            '/me lembre de/iu',
            '/me lembre para/iu',
            '/lembre-me de/iu',
            '/lembre me de/iu',
            '/crie um lembrete para/iu',
            '/criar lembrete para/iu',
            '/cria um lembrete para/iu',
            '/agende um lembrete para/iu',
            '/adicionar lembrete para/iu',
            '/lembre me que/iu',
            '/lembre-me que/iu',
            '/me lembra de/iu',
            '/me lembra que/iu',
            '/me lembrar de/iu',
            '/me lembrar que/iu',
            '/lembrar que/iu',
            '/me lembre que/iu',
            '/me lembre/iu',
            '/lembre me/iu',
        ];

        foreach ($padroes as $padrao) {
            $titulo = preg_replace($padrao, '', $titulo);
        }

        $titulo = preg_replace(
            '/\bde\s+\d{1,2}\s*h?\s+em\s+\d{1,2}\s*h?'
            . '(?:\s*horas?)?\b/iu',
            '',
            $titulo
        );

        $titulo = preg_replace(
            '/\ba\s+cada\s+\d{1,2}\s*(?:h|horas?)\b/iu',
            '',
            $titulo
        );

        $titulo = preg_replace(
            '/\b(?:por|durante)\s+\d{1,3}\s+(?:dias?|semanas?)\b/iu',
            '',
            $titulo
        );

        $titulo = preg_replace(
            '/\b(?:começando|comecando|a partir de)\b.*$/iu',
            '',
            $titulo
        );

        $titulo = preg_replace(
            '/\b(hoje|amanhã|amanha|depois de amanhã|depois de amanha)\b.*$/iu',
            '',
            $titulo
        );

        $titulo = preg_replace(
            '/\b(domingo|segunda|terça|terca|quarta|quinta|sexta|sábado|sabado)\b.*$/iu',
            '',
            $titulo
        );

        $titulo = preg_replace(
            '/\d{1,2}[\/\-]\d{1,2}(?:[\/\-]\d{4})?.*$/',
            '',
            $titulo
        );

        $titulo = preg_replace(
            '/(?:as|às)?\s*\d{1,2}(?:h|:)\d{0,2}/iu',
            '',
            $titulo
        );

        $titulo = preg_replace('/^que\s+/iu', '', $titulo);
        $titulo = preg_replace('/^tenho\s+(uma|um)\s+/iu', '', $titulo);
        $titulo = preg_replace('/\s+/', ' ', $titulo);

        return trim($titulo, " \t\n\r\0\x0B,.-");
    }

    private function identificarTipo(string $mensagem): string
    {
        if (
            Str::contains(
                $mensagem,
                ['remedio', 'medicacao', 'medicamento', 'tomar']
            )
        ) {
            return 'medicacao';
        }

        if (
            Str::contains(
                $mensagem,
                ['consulta', 'medico', 'cardiologista', 'dentista']
            )
        ) {
            return 'consulta';
        }

        if (
            Str::contains(
                $mensagem,
                ['exame', 'sangue', 'laboratorio']
            )
        ) {
            return 'exame';
        }

        if (Str::contains($mensagem, ['vacina', 'vacinacao'])) {
            return 'vacina';
        }

        return 'outro';
    }

    private function nomeTipo(string $tipo): string
    {
        return match ($tipo) {
            'medicacao' => 'Medicação',
            'consulta' => 'Consulta',
            'exame' => 'Exame',
            'vacina' => 'Vacina',
            default => 'Outro',
        };
    }

    private function chaveContexto(User $user): string
    {
        return "medcare:lembrete:contexto:{$user->id}";
    }

    private function dataParaCache(?Carbon $dataHora): ?string
    {
        return $dataHora?->toIso8601String();
    }

    private function normalizar(string $texto): string
    {
        return Str::lower(Str::ascii($texto));
    }
}
