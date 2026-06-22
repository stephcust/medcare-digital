<?php

namespace App\Services\Assistente;

use App\Models\User;
use App\Services\IA\GeminiService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AssistenteMedCareService
{
    public function __construct(
        private GeminiService $geminiService,
        private MedCareContextService $medCareContextService,
        private LembreteChatService $lembreteChatService
    ) {}

    /**
     * Método principal chamado pelo Controller do Simulador.
     * Aceita o usuário, a mensagem de texto e o arquivo opcional.
     */
    public function responder(User $user, string $mensagemUsuario, ?UploadedFile $arquivo = null): string
    {
        // 1. Se houver arquivo anexo, envia direto para o fluxo de visão computacional
        if ($arquivo) {
            return $this->processarArquivoAnexo($user, $arquivo);
        }

        // 2. Se for apenas texto, roda o processador local de lembretes
        $respostaLembrete = $this->lembreteChatService->processar($user, $mensagemUsuario);
        if ($respostaLembrete !== null) {
            return $respostaLembrete;
        }

        // 3. Caso contrário, monta o contexto clínico e chama o Gemini
        $contextoUsuario = $this->medCareContextService->montar($user);
        $respostaGemini = $this->geminiService->gerarResposta($mensagemUsuario, $contextoUsuario);

        if ($respostaGemini) {
            return $respostaGemini;
        }

        // Fallback caso a IA falhe por instabilidade ou falta de chave SSL
        return $this->responderFallback($user, $mensagemUsuario);
    }

    /**
     * Processa arquivos (imagens/PDFs), extrai os dados via IA e salva no Postgres
     */
    private function processarArquivoAnexo(User $user, UploadedFile $arquivo): string
    {
        $prompt = "
Analise este documento médico e identifique a qual categoria ele pertence: 'exames', 'vacinas' ou 'receitas'.
Extraia os dados estruturando exatamente no formato JSON correspondente abaixo. Não invente ou use chaves que não foram pedidas.

Se for 'exames':
{
  \"categoria\": \"exames\",
  \"dados\": {
    \"nome\": \"Nome do Exame (ex: Hemograma Completo)\",
    \"tipo\": \"Tipo/Categoria do exame (ex: Laboratorial)\",
    \"laboratorio\": \"Nome do Laboratório ou Hospital\",
    \"data_realizacao\": \"AAAA-MM-DD\"
  }
}

Se for 'vacinas':
{
  \"categoria\": \"vacinas\",
  \"dados\": {
    \"nome_vacina\": \"Nome da vacina\",
    \"fabricante\": \"Nome do fabricante (ou null)\",
    \"lote\": \"Número do lote (ou null)\",
    \"numero_dose\": \"Ex: 1ª Dose, 2ª Dose, Dose Única\",
    \"data_aplicacao\": \"AAAA-MM-DD\",
    \"data_proxima_dose\": \"AAAA-MM-DD (ou null)\",
    \"observacoes\": \"Observações (ou null)\"
  }
}

Se for 'receitas':
{
  \"categoria\": \"receitas\",
  \"dados\": {
    \"medico\": \"Nome completo do médico\",
    \"especialidade\": \"Especialidade (ex: Clínico Geral)\",
    \"medicamentos\": [
      {
        \"nome\": \"Nome do medicamento\",
        \"dosagem\": \"Ex: 500mg\",
        \"orientacao\": \"Como tomar\"
      }
    ],
    \"data_emissao\": \"AAAA-MM-DD\"
  }
}
";

        $jsonResposta = $this->geminiService->analisarDocumento(
            $arquivo->getRealPath(),
            $arquivo->getMimeType(),
            $prompt
        );

        if (!$jsonResposta) {
            return "❌ Desculpe, não consegui ler ou processar este arquivo. Verifique se o documento está legível.";
        }

        try {
            $resultado = json_decode($jsonResposta, true);
            $categoria = $resultado['categoria'] ?? null;
            $dados = $resultado['dados'] ?? [];

            if (!$categoria || empty($dados)) {
                return "⚠️ O documento foi lido, mas os dados não puderam ser validados.";
            }

            if ($categoria === 'exames') {
                $dados['user_id'] = $user->id;
                $dados['arquivo_path'] = 'uploads/exames/' . $arquivo->hashName();
                $dados['visualizado'] = false;
                $dados['origem'] = 'api';
                $dados['created_at'] = now();
                $dados['updated_at'] = now();

                DB::table('exames')->insert($dados);

                return "✅ **Exame Extraído com Sucesso!**\n\n• **Exame:** {$dados['nome']}\n• **Laboratório:** {$dados['laboratorio']}\n• **Data:** " . Carbon::parse($dados['data_realizacao'])->format('d/m/Y') . "\n\nInserido com sucesso no seu módulo de exames!";
            }

            if ($categoria === 'vacinas') {
                $pacienteId = $this->buscarPacienteIdPorUsuario($user->id);

                if (!$pacienteId) {
                    return "⚠️ Dados extraídos, mas nenhum perfil clínico de paciente foi localizado para o seu usuário.";
                }

                $dados['paciente_id'] = $pacienteId;
                $dados['created_at'] = now();
                $dados['updated_at'] = now();

                DB::table('vacinacoes')->insert($dados);

                return "✅ **Imunização Registrada!**\n\n• **Vacina:** {$dados['nome_vacina']}\n• **Dose:** {$dados['numero_dose']}\n• **Data:** " . Carbon::parse($dados['data_aplicacao'])->format('d/m/Y') . "\n\nHistórico vacinal atualizado!";
            }

            if ($categoria === 'receitas') {
                $dados['user_id'] = $user->id;
                $dados['status'] = 'Ativa';
                $dados['caminho_arquivo'] = 'uploads/receitas/' . $arquivo->hashName();
                $dados['medicamentos'] = json_encode($dados['medicamentos']);

                $dataEmissao = $dados['data_emissao'] ?? now()->toDateString();
                $dados['data_emissao'] = $dataEmissao;
                $dados['data_validade'] = Carbon::parse($dataEmissao)->addDays(30)->toDateString();

                $dados['created_at'] = now();
                $dados['updated_at'] = now();

                DB::table('receitas')->insert($dados);

                return "✅ **Receita Médica Cadastrada!**\n\n• **Médico(a):** {$dados['medico']}\n• **Especialidade:** {$dados['especialidade']}\n\nOs medicamentos prescritos foram interpretados e arquivados no sistema.";
            }

        } catch (\Exception $e) {
            Log::error('Falha ao inserir dados do anexo: ' . $e->getMessage());
            return "❌ Erro ao salvar dados extraídos: " . $e->getMessage();
        }

        return "Documento processado, mas a categoria encontrada não corresponde aos módulos ativos.";
    }

    /**
     * Garante o vínculo do paciente_id para tabelas que dependem dele
     */
    private function buscarPacienteIdPorUsuario(int $userId): ?int
    {
        $paciente = DB::table('pacientes')->where('user_id', $userId)->first();
        if ($paciente) {
            return $paciente->id;
        }

        // Cria na hora caso o usuário de testes ainda não tenha a linha na tabela pacientes
        try {
            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) return null;

            return DB::table('pacientes')->insertGetId([
                'user_id'    => $userId,
                'nome'       => $user->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Respostas de contingência simplificadas caso o Gemini esteja offline
     */
    private function responderFallback(User $user, string $mensagemUsuario): string
    {
        $msg = strtolower(trim($mensagemUsuario));
        if (str_contains($msg, 'vacina')) return "Você pode gerenciar seu calendário de vacinas no MedCare pelo menu lateral.";
        if (str_contains($msg, 'exame')) return "Seus laudos e exames laboratoriais ficam salvos na central de Exames.";
        return "Olá! Sou o assistente do MedCare. Posso ajudar a processar exames, vacinas ou organizar seus lembretes de saúde.";
    }
}
