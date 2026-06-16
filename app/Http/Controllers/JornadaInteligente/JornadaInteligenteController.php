<?php

namespace App\Http\Controllers\JornadaInteligente;

use App\Http\Controllers\Controller;
use App\Models\RelatoSaude;
use App\Services\Assistente\MedCareContextService;
use App\Services\IA\GeminiService;
use Carbon\Carbon;
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

    if (!empty($data['data_ocorrencia'])) {
        $dataOcorrencia = Carbon::parse($data['data_ocorrencia'] . ' ' . now()->toTimeString())->toDateTimeString();
    } else {
        $dataOcorrencia = now()->toDateTimeString();
    }

    RelatoSaude::create([
        'user_id' => $request->user()->id,
        'categoria' => $data['categoria'],
        'titulo' => $data['titulo'] ?? null,
        'relato' => $data['relato'],
        'data_ocorrencia' => $dataOcorrencia, // Passa a salvar o Data + Horário
        'incluir_no_resumo' => $data['incluir_no_resumo'] ?? true,
    ]);

    return redirect()
        ->route('jornada-inteligente.index')
        ->with('success', 'Registro de sintoma salvo na sua Jornada.');
}

    public function gerarResumo(
        Request $request,
        GeminiService $geminiService,
        MedCareContextService $medCareContextService
    ) {
        $contexto = $medCareContextService->montar($request->user());

        $mensagem = "
Gere um Sumário de Preparação Clínico estruturado e objetivo para o médico com base nos dados e na linha do tempo do MedCare.

O documento gerado deve:
- Organizar de forma limpa as queixas do paciente e sintomas históricos informados por ele;
- Cruzar cronologicamente laudos de exames, receitas ou passagens por PS encontrados no contexto;
- Criar uma seção de sugestões de perguntas pertinentes que o paciente pode fazer ao médico;
- Não tentar prever diagnósticos ou prescrever remédios;
- Manter o foco estrito em simplificar e resumir o histórico para otimizar o tempo da consulta médica.
";

        $resumo = $geminiService->gerarResposta($mensagem, $contexto);

        return response()->json([
            'resumo' => $resumo ?: 'Não consegui compilar o sumário agora. Tente novamente em alguns instantes.'
        ]);
    }
}