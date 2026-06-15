<?php

namespace App\Http\Controllers\JornadaInteligente;

use App\Http\Controllers\Controller;
use App\Models\RelatoSaude;
use App\Services\Assistente\MedCareContextService;
use App\Services\IA\GeminiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JornadaInteligenteController extends Controller
{
    public function index(Request $request)
    {
        $relatos = RelatoSaude::where('user_id', $request->user()->id)
            ->orderByDesc('data_ocorrencia')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('JornadaInteligente/Index', [
            'relatos' => $relatos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'categoria' => ['required', 'string', 'max:50'],
            'titulo' => ['nullable', 'string', 'max:150'],
            'relato' => ['required', 'string', 'max:2000'],
            'data_ocorrencia' => ['nullable', 'date'],
            'incluir_no_resumo' => ['boolean'],
        ]);

        RelatoSaude::create([
            'user_id' => $request->user()->id,
            'categoria' => $data['categoria'],
            'titulo' => $data['titulo'] ?? null,
            'relato' => $data['relato'],
            'data_ocorrencia' => $data['data_ocorrencia'] ?? now()->toDateString(),
            'incluir_no_resumo' => $data['incluir_no_resumo'] ?? true,
        ]);

        return redirect()
            ->route('jornada-inteligente.index')
            ->with('success', 'Relato salvo com sucesso.');
    }

    public function gerarResumo(
        Request $request,
        GeminiService $geminiService,
        MedCareContextService $medCareContextService
    ) {
        $contexto = $medCareContextService->montar($request->user());

        $mensagem = "
Gere um resumo objetivo para o médico com base nos dados cadastrados no MedCare.

O resumo deve:
- separar dados cadastrados e relatos do próprio paciente;
- destacar sintomas ou ocorrências relatadas pelo usuário;
- citar exames, vacinas, plano, receitas e histórico apenas se existirem no contexto;
- não inventar dados;
- não dar diagnóstico;
- não prescrever medicamentos;
- deixar claro que os relatos foram informados pelo próprio paciente.

Monte em formato organizado, com tópicos curtos.
";

        $resumo = $geminiService->gerarResposta($mensagem, $contexto);

        return response()->json([
            'resumo' => $resumo ?: 'Não consegui gerar o resumo agora. Tente novamente em alguns instantes.',
        ]);
    }
}