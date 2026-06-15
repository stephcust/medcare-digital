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
        return Inertia::render('WhatsappSimulador/Index');
    }

    public function enviar(Request $request, AssistenteMedCareService $assistenteMedCareService)
    {
        $data = $request->validate([
            'mensagem' => ['required', 'string', 'max:1000'],
        ]);

        $resposta = $assistenteMedCareService->responder(
            $request->user(),
            $data['mensagem']
        );

        return response()->json([
            'resposta' => $resposta,
        ]);
    }
}