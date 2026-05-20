<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Vacinacao;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VacinacaoController extends Controller
{
    public function index(Paciente $paciente)
    {
        $vacinacoes = Vacinacao::where('paciente_id', $paciente->id)
            ->with('usuario:id,name') // Traz o nome do profissional logado
            ->orderBy('data_aplicacao', 'desc')
            ->get();

        return Inertia::render('Vacinacoes/Index', [
            'paciente' => $paciente,
            'vacinacoes' => $vacinacoes
        ]);
    }

    // Salvar o registro de aplicação da vacina
    public function store(Request $requisicao, Paciente $paciente)
    {
        $dadosValidados = $requisicao->validate([
            'nome_vacina' => 'required|string|max:255',
            'fabricante' => 'nullable|string|max:255',
            'lote' => 'nullable|string|max:100',
            'numero_dose' => 'required|string',
            'data_aplicacao' => 'required|date',
            'data_proxima_dose' => 'nullable|date|after_or_equal:data_aplicacao',
            'observacoes' => 'nullable|string',
        ], [
            'nome_vacina.required' => 'O nome da vacina é obrigatório.',
            'data_aplicacao.required' => 'A data de aplicação é obrigatória.',
            'data_proxima_dose.after_or_equal' => 'A data da próxima dose deve ser igual ou posterior à data de aplicação.'
        ]);

        $dadosValidados['paciente_id'] = $paciente->id;
        $dadosValidados['usuario_id'] = auth()->id();

        Vacinacao::create($dadosValidados);

        return redirect()->route('pacientes.vacinacoes.index', $paciente->id)
            ->with('success', 'Aplicação de vacina registrada com sucesso!');
    }

    // Excluir um registro do histórico
    public function destroy(Vacinacao $vacinacao)
    {
        $pacienteId = $vacinacao->paciente_id;
        $vacinacao->delete();

        return redirect()->route('pacientes.vacinacoes.index', $pacienteId)
            ->with('success', 'Registro de vacinação removido com sucesso.');
    }
}
