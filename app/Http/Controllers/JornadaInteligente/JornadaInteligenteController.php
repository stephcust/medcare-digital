<?php

namespace App\Http\Controllers\JornadaInteligente;

use App\Http\Controllers\Controller;
use App\Models\RelatoSaude;
use App\Models\ResumoJornada;
use App\Services\Assistente\ResumoJornadaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class JornadaInteligenteController extends Controller
{
    public function index(
        Request $request,
        ResumoJornadaService $resumoJornadaService
    ): Response {
        $relatos = RelatoSaude::where('user_id', $request->user()->id)
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

        $dataOcorrencia = !empty($data['data_ocorrencia'])
            ? Carbon::parse(
                $data['data_ocorrencia'] . ' ' . now()->toTimeString()
            )->toDateTimeString()
            : now()->toDateTimeString();

        RelatoSaude::create([
            'user_id' => $request->user()->id,
            'categoria' => $data['categoria'],
            'titulo' => $data['titulo'] ?? null,
            'relato' => $data['relato'],
            'data_ocorrencia' => $dataOcorrencia,
            'incluir_no_resumo' => $data['incluir_no_resumo'] ?? true,
        ]);

        return redirect()
            ->route('jornada-inteligente.index')
            ->with('success', 'Registro salvo na sua Jornada Inteligente.');
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
            'resumo' => $resumoJornadaService->normalizarConteudoSalvo(
                $registro->conteudo,
                $registro->periodo,
                (bool) $registro->incluir_perguntas
            ),
            'registro' => $resumoJornadaService->serializarResumo($registro),
        ]);
    }

    public function visualizar(
        Request $request,
        ResumoJornada $resumo,
        ResumoJornadaService $resumoJornadaService
    ): SymfonyResponse {
        $this->autorizar($request, $resumo);

        return $this->pdf($request, $resumo, $resumoJornadaService)
            ->stream($resumoJornadaService->nomeArquivo($resumo));
    }

    public function download(
        Request $request,
        ResumoJornada $resumo,
        ResumoJornadaService $resumoJornadaService
    ): SymfonyResponse {
        $this->autorizar($request, $resumo);

        return $this->pdf($request, $resumo, $resumoJornadaService)
            ->download($resumoJornadaService->nomeArquivo($resumo));
    }

    public function imprimir(
        Request $request,
        ResumoJornada $resumo,
        ResumoJornadaService $resumoJornadaService
    ): View {
        $this->autorizar($request, $resumo);

        return view('pdf.sumario-clinico', $this->dadosPdf(
            $request,
            $resumo,
            $resumoJornadaService,
            true
        ));
    }

    public function destruirResumo(
        Request $request,
        ResumoJornada $resumo
    ): RedirectResponse {
        $this->autorizar($request, $resumo);
        $resumo->delete();

        return redirect()
            ->route('jornada-inteligente.index')
            ->with('success', 'Resumo apagado com sucesso.');
    }

    private function pdf(
        Request $request,
        ResumoJornada $resumo,
        ResumoJornadaService $service
    ) {
        return Pdf::loadView(
            'pdf.sumario-clinico',
            $this->dadosPdf($request, $resumo, $service, false)
        )->setPaper('a4', 'portrait');
    }

    private function dadosPdf(
        Request $request,
        ResumoJornada $resumo,
        ResumoJornadaService $service,
        bool $modoImpressao
    ): array {
        return [
            'usuario' => $request->user(),
            'resumoRegistro' => $resumo,
            'resumo' => $service->normalizarConteudoSalvo(
                $resumo->conteudo,
                $resumo->periodo,
                (bool) $resumo->incluir_perguntas
            ),
            'modoImpressao' => $modoImpressao,
        ];
    }

    private function autorizar(Request $request, ResumoJornada $resumo): void
    {
        abort_unless(
            (int) $resumo->user_id === (int) $request->user()->id,
            403
        );
    }
}
