<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Receita;
use App\Models\Paciente;
use App\Services\IA\GeminiService; // Injeção do serviço de IA oficial
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReceitaController extends Controller
{
    protected $geminiService;

    // Construtor unificado para o consumo da IA
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function index(Paciente $paciente)
    {
        if (Auth::user()->paciente->id !== $paciente->id) {
            abort(403, 'Acesso não autorizado.');
        }

        $receitas = Auth::user()->receitas()
            ->orderBy('data_emissao', 'desc')
            ->get();

        return Inertia::render('Receita/Index', [
            'paciente' => $paciente,
            'receitas' => $receitas,
            'success' => session('success')
        ]);
    }

    public function create(Paciente $paciente)
    {
        if (Auth::user()->paciente->id !== $paciente->id) {
            abort(403, 'Acesso não autorizado.');
        }

        return Inertia::render('Receita/Create', [
            'paciente' => $paciente
        ]);
    }

    /**
     * Endpoint assíncrono que processa a receita médica usando o GeminiService.
     */
    public function analisarComIA(Request $request)
    {
        $request->validate([
            'arquivo' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240']
        ]);

        try {
            $arquivo = $request->file('arquivo');

            // Prompt focado na extração de dados médicos e estrutura de medicamentos
            $promptInstrucao = "Você é o assistente inteligente do MedCare Digital especializado em triagem de receitas e prescrições médicas.
            Analise o documento anexo e extraia com precisão absoluta as seguintes informações:
            1. medico: O nome completo do médico emissor (ex: 'Dr. Carlos Eduardo').
            2. especialidade: A especialidade médica identificada (ex: 'Cardiologia', 'Clínico Geral'). Se não achar, use 'Clínico Geral'.
            3. data_emissao: A data de emissão do documento em formato 'YYYY-MM-DD'. Se não encontrar, use a data atual: " . now()->format('Y-m-d') . ".
            4. data_validade: A data de validade informada em formato 'YYYY-MM-DD'. Caso não esteja explícita, calcule para 30 dias após a data de emissão.
            5. medicamentos: Um array contendo os medicamentos prescritos. Cada item deve conter estritamente este formato interno de chaves:
               - nome: Nome comercial ou genérico do fármaco (ex: 'Amoxicilina').
               - dosagem: Concentração ou miligramas (ex: '500mg' ou '1 comprimido').
               - frequencia: O intervalo de administração (ex: 'De 8 em 8 horas').
               - duracao: Tempo total de tratamento (ex: '7 dias').

            Retorne única e exclusivamente um objeto JSON válido contendo exatamente este formato estrutural:
            {
                \"medico\": \"string\",
                \"especialidade\": \"string\",
                \"data_emissao\": \"string\",
                \"data_validade\": \"string\",
                \"medicamentos\": [
                    {
                        \"nome\": \"string\",
                        \"dosagem\": \"string\",
                        \"frequencia\": \"string\",
                        \"duracao\": \"string\"
                    }
                ]
            }";

            $jsonResposta = $this->geminiService->analisarDocumento(
                $arquivo->getRealPath(),
                $arquivo->getMimeType(),
                $promptInstrucao
            );

            if (!$jsonResposta) {
                return response()->json([
                    'success' => false,
                    'message' => 'O assistente de IA não conseguiu ler os dados desta receita.'
                ], 422);
            }

            $dadosExtraidos = json_decode($jsonResposta, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de formatação estrutural na resposta interna da IA.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'dados' => $dadosExtraidos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha interna no processamento do documento: ' . $e->getMessage()
            ], 422);
        }
    }

    public function store(Request $request, Paciente $paciente)
    {
        if (Auth::user()->paciente->id !== $paciente->id) {
            abort(403);
        }

        $request->validate([
            'modo_cadastro' => ['required', 'string', 'in:manual,ia'],
            'medico'        => ['required', 'string', 'max:255'],
            'especialidade' => ['required', 'string', 'max:255'],
            'data_emissao'  => ['required', 'date'],
            'data_validade' => ['required', 'date'],
            'medicamentos'  => ['required', 'array', 'min:1'],
            'arquivo'       => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $caminho = null;
        $urlBucket = null;

        // Persistência segura do documento no Supabase Storage
        if ($request->hasFile('arquivo')) {
            $file = $request->file('arquivo');
            $nomeArquivo = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $caminho = "usuario_" . auth()->id() . "/receitas/" . $nomeArquivo;

            Storage::disk('supabase')->put($caminho, file_get_contents($file));
            $urlBucket = Storage::disk('supabase')->url($caminho);
        }

        Auth::user()->receitas()->create([
            'paciente_id'   => $paciente->id,
            'medico'        => $request->medico,
            'especialidade' => $request->especialidade,
            'data_emissao'  => $request->data_emissao,
            'data_validade' => $request->data_validade,
            'medicamentos'  => $request->medicamentos, // O Laravel irá converter o Array em JSON via casting do Model
            'arquivo_path'  => $caminho,
            'arquivo_url'   => $urlBucket,
            'status'        => 'Ativa',
            'origem'        => $request->modo_cadastro === 'ia' ? 'api' : 'manual',
        ]);

        return redirect()->route('receitas.index', $paciente->id)
            ->with('success', 'Prescrição médica cadastrada com sucesso e salva na nuvem!');
    }

    public function destroy(Receita $receita)
    {
        if (Auth::id() !== $receita->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        // Remove o documento anexado do Supabase se ele existir
        if ($receita->arquivo_path && Storage::disk('supabase')->exists($receita->arquivo_path)) {
            Storage::disk('supabase')->delete($receita->arquivo_path);
        }

        $receita->delete();

        return redirect()->back()->with('success', 'Receita médica removida com sucesso.');
    }
}
