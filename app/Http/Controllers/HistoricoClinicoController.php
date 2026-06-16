<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HistoricoClinico;
use Inertia\Inertia;

class HistoricoClinicoController extends Controller
{
    public function index()
    {
        // Em um sistema real buscaríamos pelo ID do paciente logado/selecionado
        // Aqui pegamos os dados ordenados por data decrescente (Linha do tempo)
        $historico = HistoricoClinico::orderBy('data_atendimento', 'desc')->get();

        // Estatísticas dinâmicas para o banner superior informativo
        $totalAtendimentos = $historico->count();
        $ultimoAtendimento = $historico->first();

        return Inertia::render('HistoricoPs/Index', [
            'historico' => $historico,
            'estatisticas' => [
                'total' => $totalAtendimentos,
                'ultimo_data' => $ultimoAtendimento ? $ultimoAtendimento->data_atendimento->format('d/m/Y') : null,
                'ultimo_local' => $ultimoAtendimento ? $ultimoAtendimento->local_atendimento : null,
            ]
        ]);
    }
}
