<?php

namespace App\Http\Controllers;

use App\Models\HistoricoClinico;
use App\Services\IA\GeminiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class HistoricoClinicoController extends Controller
{
    public function __construct(
        private GeminiService $geminiService
    ) {}

    public function index(Request $request): Response
    {
        $paciente = $request->user()->paciente()->firstOrCreate([]);

        $historico = HistoricoClinico::query()
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('data_atendimento')
            ->orderByDesc('criado_em')
            ->get();

        $ultimoAtendimento = $historico->first();

        return Inertia::render('HistoricoPs/Index', [
            'historico' => $historico,
            'estatisticas' => [
                'total' => $historico->count(),
                'ultimo_data' => $ultimoAtendimento?->data_atendimento
                    ?->format('d/m/Y'),
                'ultimo_local' => $ultimoAtendimento?->local_atendimento,
            ],
            'success' => session('success'),
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

            $prompt = <<<'PROMPT'
Você é o assistente do MedCare Digital especializado em organizar informações de atendimentos de pronto-socorro, urgência e emergência.

Analise o documento enviado e extraia somente informações que realmente estejam presentes. Não invente diagnóstico, tratamento, medicamento, exame, profissional ou data.

Retorne exclusivamente um JSON válido com esta estrutura:
{
  "motivo_atendimento": "motivo principal do atendimento ou Não informado",
  "gravidade": "Alta Gravidade, Média Gravidade, Baixa Gravidade ou Não informada",
  "data_atendimento": "AAAA-MM-DDTHH:MM",
  "local_atendimento": "hospital, pronto-socorro ou unidade",
  "medico_nome": "nome do profissional ou Não informado",
  "diagnostico": "diagnóstico informado no documento ou Não informado",
  "tratamento": "tratamento ou conduta realizada ou Não informado",
  "exames_realizados": ["exame 1", "exame 2"],
  "medicamentos": [
    {
      "nome": "nome do medicamento",
      "dosagem": "dosagem ou Não informada"
    }
  ],
  "desfecho": "alta, internação, transferência, observação ou Não informado",
  "acompanhamento": "recomendação de acompanhamento ou null",
  "observacoes": "outras informações relevantes ou null"
}

Regras:
- Use a data e a hora do atendimento, não a data de impressão, quando ambas existirem.
- Se a hora não estiver presente, use 12:00.
- Se a data não estiver presente, use a data atual.
- Gravidade deve usar exatamente uma das quatro opções solicitadas.
- Não faça inferências clínicas além do texto do documento.
PROMPT;

            $jsonResposta = $this->geminiService->analisarDocumento(
                $arquivo->getRealPath(),
                $arquivo->getMimeType(),
                $prompt
            );

            if (!$jsonResposta) {
                return response()->json([
                    'success' => false,
                    'message' => 'A IA não conseguiu ler o documento.',
                ], 422);
            }

            $dados = json_decode($jsonResposta, true);

            if (!is_array($dados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A resposta da IA não veio em um formato válido.',
                ], 422);
            }

            $dados['data_atendimento'] = $this->normalizarDataParaFormulario(
                $dados['data_atendimento'] ?? null
            );
            $dados['gravidade'] = $this->normalizarGravidade(
                $dados['gravidade'] ?? null
            );
            $dados['exames_realizados'] = array_values(array_filter(
                is_array($dados['exames_realizados'] ?? null)
                    ? $dados['exames_realizados']
                    : []
            ));
            $dados['medicamentos'] = array_values(array_filter(
                is_array($dados['medicamentos'] ?? null)
                    ? $dados['medicamentos']
                    : []
            ));

            return response()->json([
                'success' => true,
                'dados' => $dados,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível analisar o documento.',
            ], 422);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $paciente = $request->user()->paciente()->firstOrCreate([]);

        $dados = $request->validate([
            'modo_cadastro' => ['required', 'string', 'in:manual,ia'],
            'motivo_atendimento' => ['required', 'string', 'max:255'],
            'gravidade' => [
                'required',
                'string',
                'in:Alta Gravidade,Média Gravidade,Baixa Gravidade,Não informada',
            ],
            'data_atendimento' => ['required', 'date'],
            'local_atendimento' => ['required', 'string', 'max:255'],
            'medico_nome' => ['nullable', 'string', 'max:255'],
            'diagnostico' => ['nullable', 'string'],
            'tratamento' => ['nullable', 'string'],
            'exames_texto' => ['nullable', 'string'],
            'medicamentos_texto' => ['nullable', 'string'],
            'desfecho' => ['nullable', 'string', 'max:255'],
            'acompanhamento' => ['nullable', 'string'],
            'observacoes' => ['nullable', 'string'],
            'arquivo' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ]);

        if (
            $dados['modo_cadastro'] === 'ia'
            && !$request->hasFile('arquivo')
        ) {
            return back()->withErrors([
                'arquivo' => 'Selecione o documento que foi analisado pela IA.',
            ]);
        }

        $caminhoArquivo = null;

        try {
            if ($request->hasFile('arquivo')) {
                $arquivo = $request->file('arquivo');
                $extensao = strtolower(
                    $arquivo->getClientOriginalExtension() ?: 'pdf'
                );
                $nomeArquivo = Str::uuid() . ".{$extensao}";
                $caminhoArquivo = "usuario_{$request->user()->id}"
                    . "/historico-clinico/{$nomeArquivo}";

                $conteudo = file_get_contents($arquivo->getRealPath());

                if ($conteudo === false) {
                    throw new \RuntimeException(
                        'Não foi possível ler o documento enviado.'
                    );
                }

                if (!Storage::disk('supabase')->put($caminhoArquivo, $conteudo)) {
                    throw new \RuntimeException(
                        'Não foi possível salvar o documento no Supabase.'
                    );
                }
            }

            HistoricoClinico::create([
                'paciente_id' => $paciente->id,
                'motivo_atendimento' => $dados['motivo_atendimento'],
                'gravidade' => $dados['gravidade'],
                'data_atendimento' => $dados['data_atendimento'],
                'local_atendimento' => $dados['local_atendimento'],
                'medico_nome' => $dados['medico_nome'] ?? 'Não informado',
                'diagnostico' => $dados['diagnostico'] ?? 'Não informado',
                'tratamento' => $dados['tratamento'] ?? 'Não informado',
                'exames_realizados' => $this->separarItens(
                    $dados['exames_texto'] ?? null
                ),
                'medicamentos' => $this->separarMedicamentos(
                    $dados['medicamentos_texto'] ?? null
                ),
                'desfecho' => $dados['desfecho'] ?? 'Não informado',
                'acompanhamento' => $dados['acompanhamento'] ?? null,
                'observacoes' => $dados['observacoes'] ?? null,
                'arquivo_path' => $caminhoArquivo,
                'arquivo_url' => null,
                'origem' => $dados['modo_cadastro'] === 'ia'
                    ? 'documento'
                    : 'manual',
                'relato_original' => null,
            ]);
        } catch (Throwable $e) {
            if ($caminhoArquivo) {
                Storage::disk('supabase')->delete($caminhoArquivo);
            }

            report($e);

            return back()->withErrors([
                'arquivo' => 'Não foi possível salvar o atendimento. '
                    . 'Tente novamente.',
            ]);
        }

        return redirect()->route('historico.ps')
            ->with('success', 'Atendimento registrado com sucesso.');
    }

    public function visualizar(
        Request $request,
        HistoricoClinico $historico
    ) {
        $this->autorizar($request, $historico);

        return $this->responderDocumento($historico, false);
    }

    public function download(
        Request $request,
        HistoricoClinico $historico
    ) {
        $this->autorizar($request, $historico);

        return $this->responderDocumento($historico, true);
    }

    public function relatorio(
        Request $request,
        HistoricoClinico $historico
    ) {
        $this->autorizar($request, $historico);

        $pdf = $this->gerarRelatorio($historico);

        return $pdf->download($this->nomeRelatorio($historico));
    }

    public function destroy(
        Request $request,
        HistoricoClinico $historico
    ): RedirectResponse {
        $this->autorizar($request, $historico);

        if (
            $historico->arquivo_path
            && Storage::disk('supabase')->exists($historico->arquivo_path)
        ) {
            Storage::disk('supabase')->delete($historico->arquivo_path);
        }

        $historico->delete();

        return redirect()->route('historico.ps')
            ->with('success', 'Atendimento removido com sucesso.');
    }

    private function autorizar(
        Request $request,
        HistoricoClinico $historico
    ): void {
        $historico->loadMissing('paciente.user');

        if (
            !$historico->paciente
            || $historico->paciente->user_id !== $request->user()->id
        ) {
            abort(403, 'Acesso não autorizado.');
        }
    }

    private function responderDocumento(
        HistoricoClinico $historico,
        bool $download
    ) {
        if (
            $historico->arquivo_path
            && Storage::disk('supabase')->exists($historico->arquivo_path)
        ) {
            try {
                $conteudo = Storage::disk('supabase')
                    ->get($historico->arquivo_path);
                $extensao = strtolower(pathinfo(
                    $historico->arquivo_path,
                    PATHINFO_EXTENSION
                ));
                $mime = $this->mimeDaExtensao($extensao);
                $nome = $this->nomeDocumentoOriginal($historico, $extensao);
                $disposicao = $download ? 'attachment' : 'inline';

                return response($conteudo, 200, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => $disposicao
                        . '; filename="' . $nome . '"',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            } catch (Throwable $e) {
                Log::warning(
                    'Falha ao recuperar documento do histórico clínico: '
                    . $e->getMessage()
                );
            }
        }

        $pdf = $this->gerarRelatorio($historico);
        $nome = $this->nomeRelatorio($historico);

        return $download ? $pdf->download($nome) : $pdf->stream($nome);
    }

    private function gerarRelatorio(HistoricoClinico $historico)
    {
        $historico->loadMissing('paciente.user');

        return Pdf::loadView('pdf.historico-clinico', [
            'registro' => $historico,
            'paciente' => $historico->paciente,
        ])->setPaper('a4');
    }

    private function separarItens(?string $texto): array
    {
        if (!$texto) {
            return [];
        }

        $itens = preg_split('/[\r\n,;]+/', $texto) ?: [];

        return array_values(array_filter(array_map(
            fn ($item) => trim($item),
            $itens
        )));
    }

    private function separarMedicamentos(?string $texto): array
    {
        return array_map(function (string $item) {
            $partes = array_map('trim', preg_split('/[|\-]/', $item, 2));

            return [
                'nome' => $partes[0] ?: 'Medicamento não informado',
                'dosagem' => $partes[1] ?? 'Não informada',
            ];
        }, $this->separarItens($texto));
    }

    private function normalizarDataParaFormulario(?string $data): string
    {
        if (!$data) {
            return now()->format('Y-m-d\\TH:i');
        }

        try {
            return \Carbon\Carbon::parse($data)->format('Y-m-d\\TH:i');
        } catch (Throwable) {
            return now()->format('Y-m-d\\TH:i');
        }
    }

    private function normalizarGravidade(?string $gravidade): string
    {
        $permitidas = [
            'Alta Gravidade',
            'Média Gravidade',
            'Baixa Gravidade',
            'Não informada',
        ];

        return in_array($gravidade, $permitidas, true)
            ? $gravidade
            : 'Não informada';
    }

    private function mimeDaExtensao(string $extensao): string
    {
        return match ($extensao) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };
    }

    private function nomeDocumentoOriginal(
        HistoricoClinico $historico,
        string $extensao
    ): string {
        $data = $historico->data_atendimento
            ?->format('Y-m-d') ?? 'sem-data';

        return 'atendimento-'
            . Str::slug($historico->local_atendimento ?: 'clinico')
            . "-{$data}."
            . ($extensao ?: 'pdf');
    }

    private function nomeRelatorio(HistoricoClinico $historico): string
    {
        $data = $historico->data_atendimento
            ?->format('Y-m-d') ?? 'sem-data';

        return "resumo-atendimento-{$data}.pdf";
    }
}
