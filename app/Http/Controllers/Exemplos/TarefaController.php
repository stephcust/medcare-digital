<?php

namespace App\Http\Controllers\Exemplos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exemplos\StoreTarefaRequest;
use App\Http\Requests\Exemplos\UpdateTarefaRequest;
use App\Models\Tarefa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Exemplo de Controlador de Recurso para o modelo de Tarefas.
 */
class TarefaController extends Controller
{
    /**
     * Visualizar a listagem do recurso.
     */
    public function index()
    {
        $user_id = Auth::user()->id;
        $tarefas = Tarefa::where('user_id', $user_id)->get();
        return Inertia::render('Tarefas/Index', [
            'tarefas' => $tarefas,
        ]);
    }

    /**
     * Mostrar o formulário de criação de um novo recurso.
     */
    public function create()
    {
        return Inertia::render('Tarefas/Create');
    }

    /**
     * Salvar o novo recurso a partir do formulário de criação.
     */
    public function store(StoreTarefaRequest $request)
    {
        $request->merge(['user_id' => Auth::user()->id]);
        $tarefa = Tarefa::create($request->all());

        return redirect()->route('tarefa.index');
    }

    /**
     * Mostrar o recurso especificado.
     */
    public function show(Tarefa $tarefa)
    {
        return Inertia::render('Tarefas/Show', [
            'tarefa' => $tarefa,
        ]);
    }

    /**
     * Mostrar o formulário de edição do recurso especificado.
     */
    public function edit(Tarefa $tarefa)
    {
        return Inertia::render('Tarefas/Edit', [
            'tarefa' => $tarefa,
        ]);
    }

    /**
     * Atualizar o recurso especificado a partir do formulário de edição.
     */
    public function update(UpdateTarefaRequest $request, Tarefa $tarefa)
    {
        if ($tarefa->user_id !== auth()->id()) {
            abort(403, 'Você não pode editar tarefas que não são suas.');
        }
        $tarefa->update($request->all());
        return redirect()->route('tarefa.index');
    }

    /**
     * Remover o recurso especificado.
     */
    public function destroy(Tarefa $tarefa)
    {
        if ($tarefa->user_id !== auth()->id()) {
            abort(403, 'Você não pode excluir tarefas que não são suas.');
        }
        $tarefa->delete();
        return redirect()->route('tarefa.index');
    }

    public function complete(Tarefa $tarefa)
    {
        if ($tarefa->user_id !== auth()->id()) {
            abort(403, 'Você não pode completar tarefas que não são suas.');
        }
        $tarefa->update(['concluida_em' => now()]);
        return redirect()->route('tarefa.index');
    }
}
