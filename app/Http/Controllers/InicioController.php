<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Exame;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class InicioController extends Controller
{
    public function inicioAutenticado()
    {
        // dd(Session::all());
        $examesPendentes = Exame::where('user_id', auth()->id())
        ->where('visualizado', false)
        ->with(['user']) // Carrega relacionamento se necessário
        ->orderBy('data_realizacao', 'desc')
        ->get();
        return Inertia::render("InicioAutenticado", [
            'ultimoExamePendente' => $examesPendentes->first()
        ]);
    }
}
