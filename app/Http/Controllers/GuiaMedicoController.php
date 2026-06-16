<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Clinica;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuiaMedicoController extends Controller
{
    // Tela Inicial do Guia
    public function inicio()
    {
        return Inertia::render('GuiaMedico/Inicio');
    }

    // Tela de Médicos (com busca simples)
    public function medicos(Request $requisicao)
    {
        $busca = $requisicao->input('busca');

        $medicos = Medico::when($busca, function ($query, $busca) {
            $query->where('nome', 'ILIKE', "%{$busca}%")
                ->orWhere('especialidade', 'ILIKE', "%{$busca}%");
        })->orderBy('avaliacao', 'desc')->get();

        return Inertia::render('GuiaMedico/Medicos', [
            'medicos' => $medicos,
            'filtros' => $requisicao->only(['busca'])
        ]);
    }

    // Tela de Clínicas (com busca simples)
    public function clinicas(Request $requisicao)
    {
        $busca = $requisicao->input('busca');

        $clinicas = Clinica::when($busca, function ($query, $busca) {
            $query->where('nome', 'ILIKE', "%{$busca}%")
                ->orWhere('tipo', 'ILIKE', "%{$busca}%");
        })->orderBy('avaliacao', 'desc')->get();

        return Inertia::render('GuiaMedico/Clinicas', [
            'clinicas' => $clinicas,
            'filtros' => $requisicao->only(['busca'])
        ]);
    }
}
