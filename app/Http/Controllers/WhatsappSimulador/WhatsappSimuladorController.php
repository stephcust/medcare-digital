<?php

namespace App\Http\Controllers\WhatsappSimulador;

use App\Http\Controllers\Controller;
use App\Services\Assistente\AssistenteMedCareService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhatsappSimuladorController extends Controller
{
    public function index()
    {
        return Inertia::render('WhatsappSimulador/Index', [
            'respostaIA' => session('respostaIA')
        ]);
    }

    public function enviar(Request $request, AssistenteMedCareService $assistenteMedCareService)
    {
        $request->validate([
            'mensagem' => ['nullable', 'string', 'max:1000'],
            'arquivo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // max 10MB
        ]);

        // Passa o texto e o arquivo capturado direto para o serviço
        $resposta = $assistenteMedCareService->responder(
            $request->user(),
            $request->input('mensagem') ?? '',
            $request->file('arquivo')
        );

        return redirect()
            ->route('whatsapp-simulador.index')
            ->with('respostaIA', $resposta);
    }
}