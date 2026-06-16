<?php

namespace App\Http\Controllers;

use App\Models\PacientePlano;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PlanoController extends Controller
{
    public function index()
    {
        // Resgata o plano e a carteirinha ativa do paciente
        $assinatura = PacientePlano::with('plano')->first();

        // Resgata o histórico das faturas
        $faturas = DB::table('faturas_plano')
            ->orderBy('data_vencimento', 'desc')
            ->get();

        return Inertia::render('MeuPlano/Index', [
            'assinatura' => $assinatura,
            'faturas' => $faturas,
            'titular_nome' => 'Stepheson Custódio' // Mockado conforme o protótipo
        ]);
    }
}
