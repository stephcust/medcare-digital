<?php

namespace App\Http\Controllers;

use App\Models\Exame;
use App\Services\Pendencias\PendenciaSaudeService;
use Inertia\Inertia;

class InicioController extends Controller
{
    public function inicioAutenticado(PendenciaSaudeService $pendenciaService)
    {
        $user = auth()->user();

        $examesPendentes = Exame::query()
            ->where('user_id', $user->id)
            ->where('visualizado', false)
            ->orderByDesc('data_realizacao')
            ->get();

        $central = $pendenciaService->montar($user);

        return Inertia::render('InicioAutenticado', [
            'ultimoExamePendente' => $examesPendentes->first(),
            'paciente' => $user->paciente,
            'resumoPendencias' => $central['resumo'],
            'pendenciasDestaque' => $central['destaques'],
        ]);
    }
}
