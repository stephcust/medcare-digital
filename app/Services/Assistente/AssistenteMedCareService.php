<?php

namespace App\Services\Assistente;

use App\Models\User;
use App\Services\IA\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssistenteMedCareService
{
    public function __construct(
        private GeminiService $geminiService,
        private MedCareContextService $medCareContextService,
        private LembreteChatService $lembreteChatService,
        private ResumoJornadaService $resumoJornadaService
    ) {}

    /**
     * Método principal chamado pelo Controller do Simulador.
     * Aceita o usuário, a mensagem de texto e o arquivo opcional.
     */
    public function responder(
        User $user,
        string $mensagemUsuario,
        ?UploadedFile $arquivo = null
    ): string {
        // 1. Se houver arquivo anexo, envia para o fluxo de análise documental.
        if ($arquivo) {
            return $this->processarArquivoAnexo($user, $arquivo);
        }

        // 2. Primeiro tenta processar localmente comandos de lembretes.
        $respostaLembrete = $this->lembreteChatService->processar(
            $user,
            $mensagemUsuario
        );

        if ($respostaLembrete !== null) {
            return $respostaLembrete;
        }

        // 3. Tenta interpretar pedidos de sumário clínico.
        $respostaResumo = $this->resumoJornadaService->processarComando(
            $user,
            $mensagemUsuario
        );

        if ($respostaResumo !== null) {
            return $respostaResumo;
        }

        // 4. Monta os dados pessoais do usuário e acrescenta a política
        // de respostas gerais e educativas de saúde.
        $contextoUsuario = $this->medCareContextService->montar($user)
            . "\n\n"
            . $this->politicaDeRespostaEmSaude();

        $respostaGemini = $this->geminiService->gerarResposta(
            $mensagemUsuario,
            $contextoUsuario
        );

        if ($respostaGemini) {
            return $respostaGemini;
        }

        // Respostas seguras caso o Gemini esteja indisponível.
        return $this->responderFallback($mensagemUsuario);
    }

    /**
     * Define o que o assistente pode e não pode responder.
     */
    private function politicaDeRespostaEmSaude(): string
    {
        return <<<'PROMPT'
POLÍTICA DE RESPOSTA DO MEDCARE:

1. DADOS PESSOAIS DO USUÁRIO
- Para responder sobre exames, vacinas, receitas, histórico clínico, relatos ou lembretes do próprio usuário, use somente os dados existentes no contexto do MedCare.
- Nunca invente um dado pessoal que não esteja cadastrado.

2. INFORMAÇÕES GERAIS DE SAÚDE
- Você pode responder perguntas gerais, educativas, preventivas e de saúde pública.
- Pode explicar, em linguagem simples, para que servem vacinas, efeitos geralmente esperados, prevenção de doenças, termos de exames, orientações gerais de uso seguro de medicamentos e quando procurar atendimento.
- Deixe claro quando a resposta é uma orientação geral e quando depende do produto, da bula, da idade, do histórico clínico ou da avaliação de um profissional.

3. MEDICAMENTOS
- Não prescreva medicamentos.
- Não indique qual medicamento o usuário deve tomar.
- Não defina, aumente, reduza ou altere dose, intervalo ou duração de tratamento.
- Não recomende interromper ou iniciar tratamento.
- Em perguntas sobre dose esquecida, explique que a conduta varia conforme o medicamento e o tempo decorrido.
- Oriente a consultar a seção de dose esquecida da bula, um farmacêutico ou o profissional que prescreveu.
- Não recomende tomar dose dupla para compensar sem orientação específica e confiável.
- Se o usuário não informar o nome do medicamento, finalize perguntando: "Qual é o nome do medicamento? Posso explicar as orientações gerais disponíveis para ele, sem substituir a bula ou a orientação profissional."
- Se o usuário já informar o nome do medicamento, não repita essa pergunta.

4. VACINAS
- Pode informar efeitos geralmente esperados de uma vacina conhecida.
- Se o usuário não disser qual vacina é, pergunte o nome.
- Diferencie reações leves e temporárias de sinais que exigem avaliação.
- Não afirme que todo sintoma ocorrido depois da vacina foi necessariamente causado por ela.

5. LIMITES CLÍNICOS
- Não faça diagnóstico.
- Não confirme doenças com base apenas em sintomas.
- Não substitua consulta, exame físico ou avaliação profissional.
- Pode explicar possibilidades de forma geral, mas sem concluir qual condição o usuário possui.

6. SEGURANÇA
- Diante de sinais potencialmente graves, intensos, persistentes ou de piora rápida, oriente a procurar atendimento de saúde.
- Em situação de emergência, oriente a buscar serviço de urgência ou ligar para o SAMU 192.
- Seja transparente quando não houver informação suficiente ou quando houver incerteza.
- Ao final de respostas gerais, educativas ou de saúde pública, acrescente: "Informação educativa geral. Para uma orientação individual, consulte um profissional de saúde."
- Não acrescente essa frase em respostas operacionais do sistema, como criação de lembretes, listagem de dados cadastrados ou confirmação de salvamento.
PROMPT;
    }

    /**
     * Processa arquivos, extrai dados via IA e salva no banco.
     */
    private function processarArquivoAnexo(
        User $user,
        UploadedFile $arquivo
    ): string {
        $prompt = <<<'PROMPT'
Analise este documento médico e identifique a qual categoria ele pertence: "exames", "vacinas" ou "receitas".
Extraia os dados estruturando exatamente no formato JSON correspondente abaixo.
Não invente informações e não use chaves que não foram solicitadas.

Se for "exames":
{
  "categoria": "exames",
  "dados": {
    "nome": "Nome do Exame",
    "tipo": "Tipo ou categoria do exame",
    "laboratorio": "Nome do laboratório ou hospital",
    "data_realizacao": "AAAA-MM-DD"
  }
}

Se for "vacinas":
{
  "categoria": "vacinas",
  "dados": {
    "nome_vacina": "Nome da vacina",
    "fabricante": "Nome do fabricante ou null",
    "lote": "Número do lote ou null",
    "numero_dose": "Ex.: 1ª Dose, 2ª Dose ou Dose Única",
    "data_aplicacao": "AAAA-MM-DD",
    "data_proxima_dose": "AAAA-MM-DD ou null",
    "observacoes": "Observações ou null"
  }
}

Se for "receitas":
{
  "categoria": "receitas",
  "dados": {
    "medico": "Nome completo do médico",
    "especialidade": "Especialidade",
    "medicamentos": [
      {
        "nome": "Nome do medicamento",
        "dosagem": "Ex.: 500 mg",
        "orientacao": "Como tomar"
      }
    ],
    "data_emissao": "AAAA-MM-DD"
  }
}
PROMPT;

        $jsonResposta = $this->geminiService->analisarDocumento(
            $arquivo->getRealPath(),
            $arquivo->getMimeType(),
            $prompt
        );

        if (!$jsonResposta) {
            return '❌ Desculpe, não consegui ler ou processar este arquivo. '
                . 'Verifique se o documento está legível.';
        }

        try {
            $resultado = json_decode($jsonResposta, true);
            $categoria = $resultado['categoria'] ?? null;
            $dados = $resultado['dados'] ?? [];

            if (!$categoria || empty($dados)) {
                return '⚠️ O documento foi lido, mas os dados não puderam ser validados.';
            }

            if ($categoria === 'exames') {
                $dados['user_id'] = $user->id;
                $dados['arquivo_path'] = 'uploads/exames/' . $arquivo->hashName();
                $dados['visualizado'] = false;
                $dados['origem'] = 'api';
                $dados['created_at'] = now();
                $dados['updated_at'] = now();

                DB::table('exames')->insert($dados);

                return "✅ Exame extraído com sucesso!\n\n"
                    . "• Exame: {$dados['nome']}\n"
                    . "• Laboratório: {$dados['laboratorio']}\n"
                    . '• **Data:** '
                    . Carbon::parse($dados['data_realizacao'])->format('d/m/Y')
                    . "\n\nInserido com sucesso no seu módulo de exames!";
            }

            if ($categoria === 'vacinas') {
                $pacienteId = $this->buscarPacienteIdPorUsuario($user->id);

                if (!$pacienteId) {
                    return '⚠️ Dados extraídos, mas nenhum perfil clínico de '
                        . 'paciente foi localizado para o seu usuário.';
                }

                $dados['paciente_id'] = $pacienteId;
                $dados['created_at'] = now();
                $dados['updated_at'] = now();

                DB::table('vacinacoes')->insert($dados);

                return "✅ Imunização registrada!\n\n"
                    . "• Vacina: {$dados['nome_vacina']}\n"
                    . "• Dose: {$dados['numero_dose']}\n"
                    . '• **Data:** '
                    . Carbon::parse($dados['data_aplicacao'])->format('d/m/Y')
                    . "\n\nHistórico vacinal atualizado!";
            }

            if ($categoria === 'receitas') {
                $dados['user_id'] = $user->id;
                $dados['status'] = 'Ativa';
                $dados['caminho_arquivo'] = 'uploads/receitas/'
                    . $arquivo->hashName();
                $dados['medicamentos'] = json_encode(
                    $dados['medicamentos'],
                    JSON_UNESCAPED_UNICODE
                );

                $dataEmissao = $dados['data_emissao']
                    ?? now()->toDateString();

                $dados['data_emissao'] = $dataEmissao;
                $dados['data_validade'] = Carbon::parse($dataEmissao)
                    ->addDays(30)
                    ->toDateString();

                $dados['created_at'] = now();
                $dados['updated_at'] = now();

                DB::table('receitas')->insert($dados);

                return "✅ Receita médica cadastrada!\n\n"
                    . "• Médico(a): {$dados['medico']}\n"
                    . "• Especialidade: {$dados['especialidade']}\n\n"
                    . 'Os medicamentos prescritos foram interpretados e '
                    . 'arquivados no sistema.';
            }
        } catch (\Throwable $e) {
            Log::error(
                'Falha ao inserir dados do anexo: ' . $e->getMessage()
            );

            return '❌ Erro ao salvar os dados extraídos. '
                . 'Verifique o documento e tente novamente.';
        }

        return 'Documento processado, mas a categoria encontrada não '
            . 'corresponde aos módulos ativos.';
    }

    /**
     * Garante o vínculo do paciente_id para tabelas dependentes.
     */
    private function buscarPacienteIdPorUsuario(int $userId): ?int
    {
        $pacienteId = DB::table('pacientes')
            ->where('user_id', $userId)
            ->value('id');

        if ($pacienteId) {
            return (int) $pacienteId;
        }

        try {
            return DB::table('pacientes')->insertGetId([
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Falha ao localizar ou criar paciente: ' . $e->getMessage()
            );

            return null;
        }
    }

    /**
     * Respostas seguras de contingência caso o Gemini esteja offline.
     */
    private function responderFallback(string $mensagemUsuario): string
    {
        $msg = Str::lower(Str::ascii(trim($mensagemUsuario)));

        if (
            Str::contains($msg, [
                'perdi o horario',
                'esqueci de tomar',
                'dose esquecida',
                'esqueci o remedio',
                'esqueci o medicamento',
            ])
        ) {
            return 'A orientação para uma dose esquecida varia conforme o '
                . 'medicamento e o tempo que passou. Consulte na bula a seção '
                . '"O que devo fazer quando me esquecer de usar este '
                . 'medicamento?" ou confirme com um farmacêutico ou com o '
                . 'profissional que prescreveu. Não tome uma dose dupla para '
                . 'compensar sem orientação específica.'
                . "\n\n"
                . 'Qual é o nome do medicamento? Posso explicar as orientações '
                . 'gerais disponíveis para ele, sem substituir a bula ou a '
                . 'orientação profissional.'
                . "\n\n"
                . 'Informação educativa geral. Para uma orientação individual, '
                . 'consulte um profissional de saúde.';
        }

        if (
            Str::contains($msg, ['vacina'])
            && Str::contains($msg, [
                'efeito',
                'reacao',
                'sintoma',
                'faz mal',
            ])
        ) {
            return 'Os efeitos esperados variam conforme a vacina. Em geral, '
                . 'podem ocorrer reações leves e temporárias, mas preciso do '
                . 'nome da vacina para dar uma explicação mais específica. '
                . 'Se os sintomas forem intensos, persistentes ou causarem '
                . 'preocupação, procure um serviço de saúde.'
                . "\n\n"
                . 'Informação educativa geral. Para uma orientação individual, '
                . 'consulte um profissional de saúde.';
        }

        if (
            Str::contains($msg, [
                'qual doenca eu tenho',
                'me diagnostique',
                'qual meu diagnostico',
                'que remedio devo tomar',
                'qual remedio tomar',
            ])
        ) {
            return 'Posso explicar informações gerais de saúde, mas não posso '
                . 'diagnosticar nem indicar um tratamento. Para uma orientação '
                . 'individual, é necessária avaliação de um profissional de saúde.'
                . "\n\n"
                . 'Informação educativa geral. Para uma orientação individual, '
                . 'consulte um profissional de saúde.';
        }

        if (Str::contains($msg, 'vacina')) {
            return 'Posso explicar informações gerais sobre vacinas e também '
                . 'consultar os registros salvos no seu perfil. Diga o nome da '
                . 'vacina ou o que você deseja saber.';
        }

        if (Str::contains($msg, 'exame')) {
            return 'Posso explicar termos gerais de exames e consultar os '
                . 'registros salvos no seu MedCare, sem realizar diagnóstico.';
        }

        return 'Olá! Posso ajudar com informações gerais e educativas de '
            . 'saúde, consultar seus dados cadastrados e organizar lembretes. '
            . 'Não realizo diagnóstico, prescrição nem alteração de tratamento.';
    }
}
