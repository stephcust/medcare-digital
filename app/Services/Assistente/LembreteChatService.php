<?php

namespace App\Services\Assistente;

use App\Models\Lembrete;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LembreteChatService
{
    public function processar(User $user, string $mensagem): ?string
    {
        $mensagemNormalizada = $this->normalizar($mensagem);

        if ($this->ehListagem($mensagemNormalizada)) {
            return $this->listar($user);
        }

        if ($this->ehConclusao($mensagemNormalizada)) {
            return $this->concluir($user, $mensagemNormalizada);
        }

        if ($this->ehCriacao($mensagemNormalizada)) {
            return $this->criar($user, $mensagem, $mensagemNormalizada);
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

    private function criar(User $user, string $mensagemOriginal, string $mensagemNormalizada): string
    {
        $dataHora = $this->extrairDataHora($mensagemNormalizada);

        if (!$dataHora) {
            return "Para criar o lembrete, me diga também a data e o horário. Exemplo: \"me lembre de tomar remédio amanhã às 8h\".";
        }

        $titulo = $this->extrairTitulo($mensagemOriginal);

        if (!$titulo) {
            $titulo = 'Lembrete do MedCare';
        }

        $tipo = $this->identificarTipo($mensagemNormalizada);

        $lembrete = Lembrete::create([
            'user_id' => $user->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'descricao' => null,
            'data_hora' => $dataHora,
            'status' => 'pendente',
        ]);

        return "Lembrete criado com sucesso ✅\n\n"
            . "Código: {$lembrete->id}\n"
            . "Tipo: {$this->nomeTipo($tipo)}\n"
            . "Título: {$lembrete->titulo}\n"
            . "Data e horário: {$dataHora->format('d/m/Y H:i')}";
    }

    private function listar(User $user): string
    {
        $lembretes = Lembrete::where('user_id', $user->id)
            ->where('status', 'pendente')
            ->orderBy('data_hora')
            ->limit(10)
            ->get();

        if ($lembretes->isEmpty()) {
            return "Você não tem lembretes pendentes no momento.";
        }

        $resposta = "Seus lembretes pendentes:\n\n";

        foreach ($lembretes as $lembrete) {
            $dataHora = Carbon::parse($lembrete->data_hora)->format('d/m/Y H:i');

            $resposta .= "{$lembrete->id}. {$lembrete->titulo}\n";
            $resposta .= "Tipo: {$this->nomeTipo($lembrete->tipo)}\n";
            $resposta .= "Quando? {$dataHora}\n\n";
        }

        $resposta .= "Para concluir um lembrete, envie: \"concluir lembrete 1\".";

        return trim($resposta);
    }

    private function concluir(User $user, string $mensagem): string
{
    preg_match('/\d+/', $mensagem, $matches);

    if (empty($matches)) {
        $pendentes = Lembrete::where('user_id', $user->id)
            ->where('status', 'pendente')
            ->orderBy('data_hora')
            ->get();

        if ($pendentes->isEmpty()) {
            return "Você não tem lembretes pendentes para concluir.";
        }

        if ($pendentes->count() > 1) {
            return "Encontrei mais de um lembrete pendente. Me diga o número do lembrete que deseja concluir. Exemplo: \"concluir lembrete 1\".";
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
        return "Não encontrei esse lembrete na sua conta.";
    }

    if ($lembrete->status === 'concluido') {
        return "Esse lembrete já estava marcado como concluído.";
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

        if (!$data && preg_match('/(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{4}))?/', $mensagem, $matches)) {
            $dia = (int) $matches[1];
            $mes = (int) $matches[2];
            $ano = isset($matches[3]) ? (int) $matches[3] : (int) now()->year;

            try {
                $data = Carbon::create($ano, $mes, $dia);
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (!$data) {
            return null;
        }

        if (
    preg_match('/\b(?:as|às)\s*(\d{1,2})(?:(?:h|:)(\d{2})?|\s*horas?)?\b/u', $mensagem, $matches) ||
    preg_match('/\b(\d{1,2})(?:h|:)(\d{2})?\b/u', $mensagem, $matches)
) {
    $hora = (int) $matches[1];
    $minuto = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : 0;
} else {
    return null;
}

        if ($hora < 0 || $hora > 23 || $minuto < 0 || $minuto > 59) {
            return null;
        }

        return $data->setTime($hora, $minuto, 0);
    }

    private function extrairTitulo(string $mensagem): string
    {
        $titulo = $mensagem;

        $padroes = [
            '/me lembre de/i',
            '/me lembre para/i',
            '/lembre-me de/i',
            '/lembre me de/i',
            '/crie um lembrete para/i',
            '/criar lembrete para/i',
            '/cria um lembrete para/i',
            '/agende um lembrete para/i',
            '/adicionar lembrete para/i',
            '/lembre me que/i',
            '/lembre-me que/i',
            '/me lembra de/i',
            '/me lembra que/i',
            '/me lembrar de/i',
            '/me lembrar que/i',
            '/lembrar que/i',
            '/me lembre que/i',
            '/me lembre/i',
            '/lembre me que/i',
            '/lembre me/i',
        ];

        foreach ($padroes as $padrao) {
            $titulo = preg_replace($padrao, '', $titulo);
        }

        $titulo = preg_replace('/\b(hoje|amanhã|amanha|depois de amanhã|depois de amanha)\b.*$/iu', '', $titulo);
        $titulo = preg_replace('/\b(domingo|segunda|terça|terca|quarta|quinta|sexta|sábado|sabado)\b.*$/iu', '', $titulo);
        $titulo = preg_replace('/\d{1,2}[\/\-]\d{1,2}(?:[\/\-]\d{4})?.*$/', '', $titulo);
        $titulo = preg_replace('/(?:as|às)?\s*\d{1,2}(?:h|:)\d{0,2}/iu', '', $titulo);

        $titulo = preg_replace('/^que\s+/iu', '', $titulo);
        $titulo = preg_replace('/^tenho\s+(uma|um)\s+/iu', '', $titulo);

        return trim($titulo);
    }

    private function identificarTipo(string $mensagem): string
    {
        if (Str::contains($mensagem, ['remedio', 'medicacao', 'tomar'])) {
            return 'medicacao';
        }

        if (Str::contains($mensagem, ['consulta', 'medico', 'cardiologista', 'dentista'])) {
            return 'consulta';
        }

        if (Str::contains($mensagem, ['exame', 'sangue', 'laboratorio'])) {
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

    private function normalizar(string $texto): string
    {
        $texto = Str::lower($texto);

        return strtr($texto, [
            'á' => 'a',
            'à' => 'a',
            'ã' => 'a',
            'â' => 'a',
            'é' => 'e',
            'ê' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ú' => 'u',
            'ç' => 'c',
        ]);
    }
}