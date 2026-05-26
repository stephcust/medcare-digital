<?php

namespace App\Http\Controllers;

use App\Models\Receita;
use App\Models\Paciente;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReceitaController extends Controller
{
    /**
     * Exibe a lista de receitas do paciente logado.
     */
    public function index(Paciente $paciente)
    {
        // Segurança: Garante que o usuário logado só acesse a rota correspondente ao seu perfil
        if (Auth::user()->paciente->id !== $paciente->id) {
            abort(403, 'Acesso não autorizado.');
        }

        // Busca as receitas pertencentes ao user_id do usuário autenticado
        $receitas = Auth::user()->receitas()
            ->orderBy('data_emissao', 'desc')
            ->get();

        return Inertia::render('Receita/Index', [
            'paciente' => $paciente,
            'receitas' => $receitas
        ]);
    }

    /**
     * Remove uma receita do histórico digital.
     */
    public function destroy(Receita $receita)
    {
        // Segurança: Só permite deletar se a receita pertencer ao usuário logado
        if (Auth::id() !== $receita->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $receita->delete();

        return redirect()->back()->with('success', 'Receita médica removida com sucesso.');
    }
}