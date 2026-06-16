<?php

namespace App\Http\Controllers;

use App\Models\Lembrete;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LembreteController extends Controller
{
    public function index()
    {
        $lembretes = Lembrete::where('user_id', auth()->id())
            ->orderBy('data_hora', 'asc')
            ->get();

        return Inertia::render('Lembretes/Index', [
            'lembretes' => $lembretes
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'tipo' => 'required|in:medicamento,consulta,exame,outros',
            'data_hora' => 'required|date',
        ]);

        Lembrete::create([
            ...$validated,
            'user_id' => auth()->id(),
            'origem' => 'manual', // Diferencia se veio da Web ou do WhatsApp
        ]);

        return redirect()->route('lembretes.index')->with('success', 'Lembrete criado com sucesso!');
    }

    public function destroy(Lembrete $lembrete)
    {
        if ($lembrete->user_id !== auth()->id()) {
            abort(403);
        }

        $lembrete->delete();

        return redirect()->route('lembretes.index')->with('success', 'Lembrete removido.');
    }
}
