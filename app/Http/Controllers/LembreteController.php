<?php

namespace App\Http\Controllers;

use App\Models\Lembrete;
use App\Services\Pendencias\PendenciaSaudeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LembreteController extends Controller
{
    private const TIPOS = [
        'medicacao',
        'consulta',
        'exame',
        'vacina',
        'prescricao',
        'acompanhamento',
        'outro',
    ];

    public function index(PendenciaSaudeService $pendenciaService): Response
    {
        $user = request()->user();

        $lembretes = Lembrete::query()
            ->where('user_id', $user->id)
            ->orderByRaw("CASE WHEN status = 'pendente' THEN 0 ELSE 1 END")
            ->orderBy('data_hora')
            ->limit(250)
            ->get()
            ->map(function (Lembrete $lembrete) use ($pendenciaService) {
                $situacao = $lembrete->status === 'concluido'
                    ? ['situacao' => 'concluido', 'rotulo' => 'Concluído']
                    : $pendenciaService->situacaoDaData($lembrete->data_hora);

                return [
                    'id' => $lembrete->id,
                    'tipo' => $this->normalizarTipo($lembrete->tipo),
                    'titulo' => $lembrete->titulo,
                    'descricao' => $lembrete->descricao,
                    'data_hora' => $lembrete->data_hora?->toIso8601String(),
                    'status' => $lembrete->status,
                    'situacao' => $situacao['situacao'],
                    'rotulo_situacao' => $situacao['rotulo'],
                    'origem' => $lembrete->origem ?: 'manual',
                    'recorrente' => (bool) $lembrete->recorrente,
                    'serie_id' => $lembrete->serie_id,
                    'intervalo_horas' => $lembrete->intervalo_horas,
                    'concluido_em' => $lembrete->concluido_em?->toIso8601String(),
                ];
            });

        $central = $pendenciaService->montar($user);

        return Inertia::render('Lembretes/Index', [
            'lembretes' => $lembretes,
            'pendenciasAutomaticas' => $central['automaticas'],
            'resumo' => $central['resumo'],
            'success' => session('success'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'tipo' => ['required', Rule::in(self::TIPOS)],
            'data_hora' => ['required', 'date'],
        ]);

        Lembrete::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => 'pendente',
            'origem' => 'manual',
            'recorrente' => false,
            'ativo' => true,
        ]);

        return redirect()
            ->route('lembretes.index')
            ->with('success', 'Lembrete criado com sucesso!');
    }

    public function concluir(Request $request, Lembrete $lembrete): RedirectResponse
    {
        $this->autorizar($request, $lembrete);

        $lembrete->update([
            'status' => 'concluido',
            'concluido_em' => now(),
            'ativo' => false,
        ]);

        return back()->with('success', 'Lembrete marcado como concluído.');
    }

    public function adiar(Request $request, Lembrete $lembrete): RedirectResponse
    {
        $this->autorizar($request, $lembrete);

        $dados = $request->validate([
            'dias' => ['required', 'integer', Rule::in([1, 7])],
        ]);

        $base = $lembrete->data_hora && $lembrete->data_hora->isFuture()
            ? $lembrete->data_hora->copy()
            : now();

        $lembrete->update([
            'data_hora' => $base->addDays((int) $dados['dias']),
            'status' => 'pendente',
            'concluido_em' => null,
            'ativo' => true,
        ]);

        return back()->with('success', 'Lembrete adiado com sucesso.');
    }

    public function destroy(Request $request, Lembrete $lembrete): RedirectResponse
    {
        $this->autorizar($request, $lembrete);
        $lembrete->delete();

        return back()->with('success', 'Lembrete removido.');
    }

    public function destroySerie(Request $request, string $serieId): RedirectResponse
    {
        $quantidade = Lembrete::query()
            ->where('user_id', $request->user()->id)
            ->where('serie_id', $serieId)
            ->delete();

        if ($quantidade === 0) {
            return back()->withErrors([
                'lembretes' => 'Nenhuma série de lembretes foi encontrada.',
            ]);
        }

        return back()->with('success', 'Série de lembretes removida.');
    }

    private function autorizar(Request $request, Lembrete $lembrete): void
    {
        if ($lembrete->user_id !== $request->user()->id) {
            abort(403, 'Você não pode alterar este lembrete.');
        }
    }

    private function normalizarTipo(?string $tipo): string
    {
        return match ($tipo) {
            'medicamento' => 'medicacao',
            'outros' => 'outro',
            'medicacao',
            'consulta',
            'exame',
            'vacina',
            'prescricao',
            'acompanhamento',
            'outro' => $tipo,
            default => 'outro',
        };
    }
}
