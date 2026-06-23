<?php

namespace App\Http\Controllers\WhatsappSimulador;

use App\Http\Controllers\Controller;
use App\Models\ConversaAssistente;
use App\Services\Assistente\AssistenteMedCareService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class WhatsappSimuladorController extends Controller
{
    public function index(Request $request): Response
    {
        $historico = ConversaAssistente::query()
            ->where('user_id', $request->user()->id)
            ->where('canal', 'simulador')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->reverse()
            ->values()
            ->map(function (ConversaAssistente $mensagem) {
                return [
                    'id' => $mensagem->id,
                    'autor' => $mensagem->autor,
                    'texto' => $mensagem->texto,
                    'arquivo_nome' => $mensagem->arquivo_nome,
                    'hora' => $mensagem->created_at
                        ->timezone(config('app.timezone'))
                        ->format('H:i'),
                ];
            });

        return Inertia::render('WhatsappSimulador/Index', [
            'historico' => $historico,
        ]);
    }

    public function enviar(
        Request $request,
        AssistenteMedCareService $assistenteMedCareService
    ): RedirectResponse {
        $dados = $request->validate([
            'mensagem' => ['nullable', 'string', 'max:1000'],
            'arquivo' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ]);

        $mensagem = trim($dados['mensagem'] ?? '');
        $arquivo = $request->file('arquivo');

        if ($mensagem === '' && !$arquivo) {
            return back()->withErrors([
                'mensagem' => 'Digite uma mensagem ou selecione um arquivo.',
            ]);
        }

        $textoExibido = $mensagem;

        if ($arquivo) {
            $anexo = "📎 Arquivo: {$arquivo->getClientOriginalName()}";
            $textoExibido = $textoExibido !== ''
                ? "{$textoExibido}\n\n{$anexo}"
                : $anexo;
        }

        ConversaAssistente::create([
            'user_id' => $request->user()->id,
            'canal' => 'simulador',
            'autor' => 'usuario',
            'texto' => $textoExibido,
            'arquivo_nome' => $arquivo?->getClientOriginalName(),
        ]);

        try {
            $resposta = $assistenteMedCareService->responder(
                $request->user(),
                $mensagem,
                $arquivo
            );
        } catch (Throwable $e) {
            report($e);

            $resposta = 'Houve um erro ao processar sua mensagem. Tente novamente.';
        }

        ConversaAssistente::create([
            'user_id' => $request->user()->id,
            'canal' => 'simulador',
            'autor' => 'assistente',
            'texto' => $resposta,
            'arquivo_nome' => null,
        ]);

        return redirect()->route('whatsapp-simulador.index');
    }
}
