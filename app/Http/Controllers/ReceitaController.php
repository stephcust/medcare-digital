<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Receita;
use App\Services\IA\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReceitaController extends Controller
{
    public function __construct(
        private GeminiService $geminiService
    ) {}

    public function index(Paciente $paciente)
    {
        $this->autorizarPaciente($paciente);

        $receitas = Receita::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('data_emissao')
            ->get();

        return Inertia::render('Receita/Index', [
            'paciente' => $paciente,
            'receitas' => $receitas,
            'success' => session('success'),
        ]);
    }

    public function create(Paciente $paciente)
    {
        $this->autorizarPaciente($paciente);

        return Inertia::render('Receita/Create', [
            'paciente' => $paciente,
        ]);
    }

    public function analisarComIA(Request $request)
    {
        $request->validate([
            'arquivo' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ]);

        try {
            $arquivo = $request->file('arquivo');

            $promptInstrucao = "Você é o assistente inteligente do MedCare "
                . "Digital especializado em triagem de receitas e prescrições "
                . "médicas. Analise o documento e extraia: medico, "
                . "especialidade, data_emissao, data_validade e medicamentos. "
                . "Cada medicamento deve conter nome, dosagem, frequencia e "
                . "duracao. Retorne somente JSON válido.";

            $jsonResposta = $this->geminiService->analisarDocumento(
                $arquivo->getRealPath(),
                $arquivo->getMimeType(),
                $promptInstrucao
            );

            if (!$jsonResposta) {
                return response()->json([
                    'success' => false,
                    'message' => 'A IA não conseguiu ler esta receita.',
                ], 422);
            }

            $dadosExtraidos = json_decode($jsonResposta, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'A IA retornou dados em formato inválido.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'dados' => $dadosExtraidos,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao processar o documento.',
            ], 422);
        }
    }

    public function store(Request $request, Paciente $paciente)
    {
        $this->autorizarPaciente($paciente);

        $dados = $request->validate([
            'modo_cadastro' => ['required', 'string', 'in:manual,ia'],
            'medico' => ['required', 'string', 'max:255'],
            'especialidade' => ['required', 'string', 'max:255'],
            'data_emissao' => ['required', 'date'],
            'data_validade' => ['required', 'date'],
            'medicamentos' => ['required', 'array', 'min:1'],
            'arquivo' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ]);

        $arquivo = $request->file('arquivo');
        $nomeArquivo = Str::uuid()
            . '.'
            . strtolower($arquivo->getClientOriginalExtension());

        $caminho = 'usuario_'
            . Auth::id()
            . '/receitas/'
            . $nomeArquivo;

        $salvo = Storage::disk('supabase')->put(
            $caminho,
            file_get_contents($arquivo->getRealPath())
        );

        if (!$salvo) {
            return back()->withErrors([
                'arquivo' => 'Não foi possível salvar o PDF na nuvem.',
            ]);
        }

        try {
            Receita::create([
                'user_id' => Auth::id(),
                'medico' => $dados['medico'],
                'especialidade' => $dados['especialidade'],
                'data_emissao' => $dados['data_emissao'],
                'data_validade' => $dados['data_validade'],
                'medicamentos' => $dados['medicamentos'],
                'arquivo_path' => $caminho,
                'arquivo_url' => null,
                'status' => 'Ativa',
            ]);
        } catch (\Throwable $e) {
            Storage::disk('supabase')->delete($caminho);
            throw $e;
        }

        return redirect()
            ->route('receitas.index', $paciente->id)
            ->with(
                'success',
                'Prescrição cadastrada e salva na nuvem.'
            );
    }

    public function visualizar(Receita $receita)
    {
        return $this->responderArquivo($receita, 'inline');
    }

    public function download(Receita $receita)
    {
        return $this->responderArquivo($receita, 'attachment');
    }

    public function destroy(Receita $receita)
    {
        $this->autorizarReceita($receita);

        if (
            $receita->arquivo_path
            && Storage::disk('supabase')->exists($receita->arquivo_path)
        ) {
            Storage::disk('supabase')->delete($receita->arquivo_path);
        }

        $receita->delete();

        return redirect()
            ->back()
            ->with('success', 'Receita removida com sucesso.');
    }

    private function responderArquivo(
        Receita $receita,
        string $disposicao
    ) {
        $this->autorizarReceita($receita);

        if (!$receita->arquivo_path) {
            abort(404, 'Esta receita não possui um PDF anexado.');
        }

        try {
            $extensao = strtolower(
                pathinfo($receita->arquivo_path, PATHINFO_EXTENSION)
            );

            $nomeArquivo = 'prescricao-'
                . Str::slug($receita->medico ?: 'medico')
                . '-'
                . ($receita->data_emissao
                    ? $receita->data_emissao->format('Y-m-d')
                    : 'sem-data')
                . '.'
                . ($extensao ?: 'pdf');

            $mimeTypes = [
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
            ];

            $contentType = $mimeTypes[$extensao]
                ?? 'application/octet-stream';

            $conteudo = Storage::disk('supabase')
                ->get($receita->arquivo_path);

            if ($conteudo === '') {
                throw new \RuntimeException(
                    'O arquivo da receita foi retornado vazio.'
                );
            }

            return response($conteudo, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => $disposicao
                    . '; filename="' . $nomeArquivo . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Erro ao recuperar receita do Supabase: '
                . $e->getMessage()
            );

            abort(
                404,
                'Não foi possível recuperar o PDF da receita.'
            );
        }
    }

    private function autorizarPaciente(Paciente $paciente): void
    {
        $pacienteDoUsuario = Auth::user()?->paciente;

        if (
            !$pacienteDoUsuario
            || $pacienteDoUsuario->id !== $paciente->id
        ) {
            abort(403, 'Acesso não autorizado.');
        }
    }

    private function autorizarReceita(Receita $receita): void
    {
        if ((int) $receita->user_id !== (int) Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }
    }
}
