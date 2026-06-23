<?php

namespace App\Http\Controllers\JornadaInteligente;

use App\Http\Controllers\Controller;
use App\Models\RelatoSaude;
use App\Models\ResumoJornada;
use App\Services\Assistente\ResumoJornadaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class JornadaInteligenteController extends Controller
{
    public function index(
        Request $request,
        ResumoJornadaService $resumoJornadaService
    ): Response {
        $relatos = RelatoSaude::where(
            'user_id',
            $request->user()->id
        )
            ->orderByDesc('data_ocorrencia')
            ->orderByDesc('created_at')
            ->get();

        $resumosSalvos = ResumoJornada::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn (ResumoJornada $resumo) =>
                $resumoJornadaService->serializarResumo($resumo)
            )
            ->values();

        return Inertia::render('JornadaInteligente/Index', [
            'relatos' => $relatos,
            'resumosSalvos' => $resumosSalvos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'categoria' => ['required', 'string', 'max:50'],
            'titulo' => ['nullable', 'string', 'max:150'],
            'relato' => ['required', 'string', 'max:2000'],
            'data_ocorrencia' => ['nullable', 'date'],
            'incluir_no_resumo' => ['boolean'],
        ]);

        if (!empty($data['data_ocorrencia'])) {
            $dataOcorrencia = Carbon::parse(
                $data['data_ocorrencia'] . ' ' . now()->toTimeString()
            )->toDateTimeString();
        } else {
            $dataOcorrencia = now()->toDateTimeString();
        }

        RelatoSaude::create([
            'user_id' => $request->user()->id,
            'categoria' => $data['categoria'],
            'titulo' => $data['titulo'] ?? null,
            'relato' => $data['relato'],
            'data_ocorrencia' => $dataOcorrencia,
            'incluir_no_resumo' =>
                $data['incluir_no_resumo'] ?? true,
        ]);

        return redirect()
            ->route('jornada-inteligente.index')
            ->with(
                'success',
                'Registro salvo na sua Jornada Inteligente.'
            );
    }

    public function gerarResumo(
        Request $request,
        ResumoJornadaService $resumoJornadaService
    ): JsonResponse {
        $dados = $request->validate([
            'periodo' => [
                'required',
                'string',
                Rule::in(['30', '60', '90', 'todos']),
            ],
            'secoes' => ['required', 'array', 'min:1'],
            'secoes.*' => [
                'required',
                'string',
                Rule::in(ResumoJornadaService::SECOES_VALIDAS),
            ],
            'incluir_perguntas' => ['required', 'boolean'],
        ]);

        try {
            $registro = $resumoJornadaService->gerarESalvar(
                $request->user(),
                $dados['secoes'],
                $dados['periodo'],
                $dados['incluir_perguntas'],
                'jornada'
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Não foi possível gerar o sumário agora.',
            ], 500);
        }

        return response()->json([
            'resumo' => $resumoJornadaService
                ->normalizarConteudoSalvo(
                    $registro->conteudo,
                    $registro->periodo,
                    $registro->incluir_perguntas
                ),
            'registro' => $resumoJornadaService
                ->serializarResumo($registro),
        ]);
    }

    public function destruirResumo(
        Request $request,
        ResumoJornada $resumo
    ): RedirectResponse {
        abort_unless(
            (int) $resumo->user_id === (int) $request->user()->id,
            403
        );

        $resumo->delete();

        return redirect()
            ->route('jornada-inteligente.index')
            ->with('success', 'Resumo apagado com sucesso.');
    }
}
