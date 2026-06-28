<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Exame;
use App\Services\IA\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ExameController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Lista todos os exames do usuário logado de forma segura.
     */
    public function index()
    {
        $exames = Exame::where('user_id', auth()->id())
            ->orderBy('data_realizacao', 'desc')
            ->get();

        return Inertia::render('Exame/Index', [
            'exames' => $exames,
            'success' => session('success')
        ]);
    }

    /**
     * Renderiza a view unificada de cadastro de exames.
     */
    public function create()
    {
        return Inertia::render('Exame/Create');
    }

    /**
     * Endpoint assíncrono que processa o arquivo temporário usando o GeminiService.
     * Não realiza o upload persistente no Supabase, apenas extrai os metadados.
     */
    public function analisarComIA(Request $request)
    {
        $request->validate([
            'arquivo' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240']
        ]);

        try {
            $arquivo = $request->file('arquivo');

            $promptInstrucao = "Você é o assistente inteligente do MedCare Digital especializado em triagem de laudos.
            Analise o documento anexo e extraia com precisão as seguintes informações clínicas:
            1. nome: O nome comercial ou descritivo do exame (ex: 'Hemograma Completo').
            2. tipo: Classifique obrigatoriamente em uma destas opções: 'Sangue', 'Imagem', 'Urina' ou 'Outros'.
            3. laboratorio: O nome do laboratório clínico ou hospital emissor. Se não encontrar, retorne 'Não informado'.
            4. data_realizacao: A data em formato 'YYYY-MM-DD'. Se não encontrar, use a data atual: " . now()->format('Y-m-d') . ".

            Retorne única e exclusivamente um objeto JSON válido contendo exatamente este formato:
            {
                \"nome\": \"string\",
                \"tipo\": \"string\",
                \"laboratorio\": \"string\",
                \"data_realizacao\": \"string\"
            }";

            // Invoca o serviço de Inteligência Artificial passando o arquivo temporário
            $jsonResposta = $this->geminiService->analisarDocumento(
                $arquivo->getRealPath(),
                $arquivo->getMimeType(),
                $promptInstrucao
            );

            if (!$jsonResposta) {
                return response()->json([
                    'success' => false,
                    'message' => 'O assistente de IA não conseguiu ler o documento.'
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
                'message' => 'Falha interna no processamento: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Grava permanentemente o exame no banco de dados e faz o upload definitivo para o Supabase.
     */
    public function store(Request $request)
    {
        $request->validate([
            'modo_cadastro'   => ['required', 'string', 'in:manual,ia'],
            'nome'            => ['required', 'string', 'max:255'],
            'tipo'            => ['required', 'string', 'max:255'],
            'laboratorio'     => ['nullable', 'string', 'max:255'],
            'data_realizacao' => ['required', 'date'],
            'arquivo'         => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ], [
            'arquivo.required' => 'O arquivo do exame é obrigatório para validação e armazenamento seguro.',
            'nome.required' => 'O nome do exame é obrigatório.',
            'tipo.required' => 'O tipo de exame é obrigatório.',
            'data_realizacao.required' => 'A data de realização é obrigatória.'
        ]);

        $path = null;

        if ($request->hasFile('arquivo')) {
            $file = $request->file('arquivo');

            // 💡 Sincronização Estrita: O UUID é gerado e fixado nesta variável local estável
            $nomeArquivo = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = "usuario_" . auth()->id() . "/exames/" . $nomeArquivo;

            // Envia o arquivo uma única vez usando exatamente o caminho definido acima
            Storage::disk('supabase')->put($path, file_get_contents($file));
        }

        // Persiste no banco utilizando rigorosamente o mesmo $path do upload
        Exame::create([
            'user_id'         => auth()->id(),
            'nome'            => $request->nome,
            'tipo'            => $request->tipo,
            'laboratorio'     => $request->laboratorio ?? 'Não informado',
            'data_realizacao' => $request->data_realizacao,
            'arquivo_path'    => $path,
            'visualizado'     => true,
            'origem'          => $request->modo_cadastro === 'ia' ? 'api' : 'manual',
        ]);

        return redirect()->route('exames.index')->with('success', 'Exame salvo e armazenado em segurança na nuvem!');
    }

    /**
     * Exibe os detalhes de um exame específico com validação de escopo.
     */
    public function show(Exame $exame)
    {
        if ($exame->user_id !== auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        if (!$exame->visualizado) {
            $exame->update(['visualizado' => true]);
        }

        return Inertia::render('Exame/Show', [
            'exame' => $exame
        ]);
    }

    /**
     * Abre o arquivo do exame no navegador.
     */
    public function visualizar(Exame $exame)
    {
        return $this->responderArquivo($exame, 'inline');
    }

    /**
     * Baixa o arquivo do exame.
     */
    public function download(Exame $exame)
    {
        return $this->responderArquivo($exame, 'attachment');
    }

    private function responderArquivo(Exame $exame, string $disposicao)
    {
        if ($exame->user_id !== auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        if (!$exame->arquivo_path) {
            abort(404, 'Nenhum arquivo foi associado a este exame.');
        }

        try {
            $extensao = strtolower(
                pathinfo($exame->arquivo_path, PATHINFO_EXTENSION)
            );

            $nomeDownload = Str::slug($exame->nome ?: 'exame')
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

            $conteudoArquivo = Storage::disk('supabase')
                ->get($exame->arquivo_path);

            if ($conteudoArquivo === '') {
                throw new \RuntimeException(
                    'O arquivo do exame foi retornado vazio.'
                );
            }

            return response($conteudoArquivo, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => $disposicao
                    . '; filename="' . $nomeDownload . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Erro ao recuperar exame do Supabase: ' . $e->getMessage()
            );

            abort(
                404,
                'Não foi possível recuperar o arquivo do exame.'
            );
        }
    }

    /**
     * Remove o exame do banco de dados e elimina o arquivo vinculado de dentro do Supabase.
     */
    public function destroy(Exame $exame)
    {
        if ($exame->user_id !== auth()->id()) {
            abort(403);
        }

        // Deleta fisicamente o arquivo do bucket para não deixar lixo eletrônico órfão
        if ($exame->arquivo_path && Storage::disk('supabase')->exists($exame->arquivo_path)) {
            Storage::disk('supabase')->delete($exame->arquivo_path);
        }

        $exame->delete();

        return redirect()->route('exames.index')->with('success', 'Exame removido com sucesso!');
    }
}
