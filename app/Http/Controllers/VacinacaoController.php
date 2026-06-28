<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\Vacinacao;
use App\Services\IA\GeminiService; // Importação do seu serviço de IA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VacinacaoController extends Controller
{
    protected $geminiService;

    // Injeção do serviço de IA idêntica ao ExameController
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function index(Paciente $paciente)
    {
        $this->autorizarPaciente($paciente);

        $vacinacoes = $paciente->vacinacoes()
            ->orderBy('data_aplicacao', 'desc')
            ->get();

        return Inertia::render('Vacinacoes/Index', [
            'paciente' => $paciente,
            'vacinacoes' => $vacinacoes,
            'success' => session('success'),
        ]);
    }

    /**
     * Endpoint assíncrono que processa o comprovante de vacina usando o GeminiService.
     */
    public function analisarComIA(Request $request)
    {
        $request->validate([
            'comprovante' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240']
        ]);

        try {
            $arquivo = $request->file('comprovante');

            // Prompt estruturado especificamente para mapear a caderneta de vacinação
            $promptInstrucao = "Você é o assistente inteligente do MedCare Digital especializado em triagem de cartões de vacinação.
            Analise o documento anexo e extraia com precisão as seguintes informações:
            1. nome_vacina: O nome comercial ou descritivo da vacina (ex: 'Tríplice Viral (SCR)', 'COVID-19', 'Hepatite B').
            2. fabricante: O nome do laboratório ou fabricante (ex: 'Fiocruz', 'Pfizer', 'Butantan'). Se não encontrar, retorne 'Não informado'.
            3. lote: O lote de fabricação da vacina. Se não encontrar, retorne 'Não informado'.
            4. numero_dose: Classifique obrigatoriamente em uma destas opções: '1ª Dose', '2ª Dose', '3ª Dose', 'Dose Única' ou 'Reforço'.
            5. data_aplicacao: A data em formato 'YYYY-MM-DD'. Se não encontrar, use a data atual: " . now()->format('Y-m-d') . ".
            6. data_proxima_dose: A data de agendamento da próxima dose em formato 'YYYY-MM-DD'. Se não houver previsão ou indicação, retorne uma string vazia ''.

            Retorne única e exclusivamente um objeto JSON válido contendo exatamente este formato:
            {
                \"nome_vacina\": \"string\",
                \"fabricante\": \"string\",
                \"lote\": \"string\",
                \"numero_dose\": \"string\",
                \"data_aplicacao\": \"string\",
                \"data_proxima_dose\": \"string\"
            }";

            // Executa a chamada oficial ao modelo Gemini
            $jsonResposta = $this->geminiService->analisarDocumento(
                $arquivo->getRealPath(),
                $arquivo->getMimeType(),
                $promptInstrucao
            );

            if (!$jsonResposta) {
                return response()->json([
                    'success' => false,
                    'message' => 'O assistente de IA não conseguiu ler o comprovante de vacinação.'
                ], 422);
            }

            $dadosExtraidos = json_decode($jsonResposta, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de formatação estrutural na resposta da IA.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'dados' => $dadosExtraidos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha interna no processamento do comprovante: ' . $e->getMessage()
            ], 422);
        }
    }

    public function store(Request $request, Paciente $paciente)
    {
        $this->autorizarPaciente($paciente);

        $request->validate([
            'modo_cadastro'     => ['required', 'string', 'in:manual,ia'],
            'nome_vacina'       => ['required', 'string', 'max:255'],
            'fabricante'        => ['nullable', 'string', 'max:255'],
            'lote'              => ['nullable', 'string', 'max:100'],
            'numero_dose'       => ['required', 'string'],
            'data_aplicacao'    => ['required', 'date'],
            'data_proxima_dose' => ['nullable', 'date'],
            'observacoes'       => ['nullable', 'string'],
            'comprovante'       => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ], [
            'nome_vacina.required' => 'O nome da vacina é obrigatório.',
            'data_aplicacao.required' => 'A data de aplicação é obrigatória.',
        ]);

        $caminho = null;

        try {
            if ($request->hasFile('comprovante')) {
                $arquivo = $request->file('comprovante');
                $extensao = strtolower(
                    $arquivo->getClientOriginalExtension() ?: 'pdf'
                );
                $nomeArquivo = Str::uuid() . '.' . $extensao;
                $caminho = "usuario_" . Auth::id()
                    . "/vacinas/" . $nomeArquivo;

                $conteudo = file_get_contents($arquivo->getRealPath());

                if ($conteudo === false) {
                    throw new \RuntimeException(
                        'Não foi possível ler o comprovante selecionado.'
                    );
                }

                $salvo = Storage::disk('supabase')->put(
                    $caminho,
                    $conteudo
                );

                if (!$salvo) {
                    throw new \RuntimeException(
                        'Não foi possível salvar o comprovante na nuvem.'
                    );
                }
            }

            Vacinacao::create([
                'paciente_id'       => $paciente->id,
                'nome_vacina'       => $request->nome_vacina,
                'fabricante'        => $request->fabricante
                    ?: 'Não informado',
                'lote'              => $request->lote ?: 'Não informado',
                'numero_dose'       => $request->numero_dose,
                'data_aplicacao'    => $request->data_aplicacao,
                'data_proxima_dose' => $request->data_proxima_dose ?: null,
                'observacoes'       => $request->observacoes,
                'arquivo_path'      => $caminho,
                'arquivo_url'       => null,
            ]);
        } catch (\Throwable $e) {
            if ($caminho) {
                try {
                    Storage::disk('supabase')->delete($caminho);
                } catch (\Throwable) {
                    // Evita ocultar o erro principal.
                }
            }

            Log::error(
                'Falha ao cadastrar vacinação: ' . $e->getMessage()
            );

            return back()->withErrors([
                'comprovante' => 'Não foi possível salvar o registro de '
                    . 'vacinação. Tente novamente.',
            ]);
        }

        return redirect()->route('vacinacoes.index', $paciente->id)
            ->with(
                'success',
                'Registro de vacinação guardado com sucesso!'
            );
    }

    public function destroy(Vacinacao $vacinacao)
    {
        $this->autorizarVacinacao($vacinacao);
        $pacienteId = $vacinacao->paciente_id;

        if (
            $vacinacao->arquivo_path
            && Storage::disk('supabase')->exists($vacinacao->arquivo_path)
        ) {
            Storage::disk('supabase')->delete($vacinacao->arquivo_path);
        }

        $vacinacao->delete();

        return redirect()->route('vacinacoes.index', $pacienteId)
            ->with(
                'success',
                'Registro de vacinação removido com sucesso.'
            );
    }

    public function visualizar(Vacinacao $vacinacao)
    {
        return $this->responderArquivo($vacinacao, true);
    }

    public function download(Vacinacao $vacinacao)
    {
        return $this->responderArquivo($vacinacao, false);
    }

    private function responderArquivo(
        Vacinacao $vacinacao,
        bool $visualizar
    ) {
        $this->autorizarVacinacao($vacinacao);

        if (!$vacinacao->arquivo_path) {
            abort(404, 'Nenhum comprovante foi associado a esta vacinação.');
        }

        try {
            $disco = Storage::disk('supabase');

            if (!$disco->exists($vacinacao->arquivo_path)) {
                abort(404, 'O comprovante não foi encontrado na nuvem.');
            }

            $conteudo = $disco->get($vacinacao->arquivo_path);
            $extensao = strtolower(
                pathinfo($vacinacao->arquivo_path, PATHINFO_EXTENSION)
            );

            $mimeTypes = [
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
            ];

            $contentType = $mimeTypes[$extensao]
                ?? 'application/octet-stream';

            $data = $vacinacao->data_aplicacao
                ? $vacinacao->data_aplicacao->format('Y-m-d')
                : 'sem-data';

            $nome = 'comprovante-'
                . Str::slug($vacinacao->nome_vacina ?: 'vacina')
                . '-'
                . $data
                . '.'
                . ($extensao ?: 'pdf');

            $disposicao = $visualizar ? 'inline' : 'attachment';

            return response($conteudo, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => $disposicao
                    . '; filename="' . $nome . '"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error(
                'Falha ao recuperar comprovante de vacinação: '
                . $e->getMessage()
            );

            abort(
                500,
                'Não foi possível recuperar o comprovante de vacinação.'
            );
        }
    }

    private function autorizarPaciente(Paciente $paciente): void
    {
        $pacienteDoUsuario = Auth::user()?->paciente;

        if (
            !$pacienteDoUsuario
            || (int) $pacienteDoUsuario->id !== (int) $paciente->id
        ) {
            abort(403, 'Acesso não autorizado.');
        }
    }

    private function autorizarVacinacao(Vacinacao $vacinacao): void
    {
        $vacinacao->loadMissing('paciente');

        if (
            !$vacinacao->paciente
            || (int) $vacinacao->paciente->user_id !== (int) Auth::id()
        ) {
            abort(403, 'Acesso não autorizado.');
        }
    }
}
