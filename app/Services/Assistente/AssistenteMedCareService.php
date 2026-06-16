<?php

namespace App\Services\Assistente;

use App\Models\User;
use App\Services\IA\GeminiService;

class AssistenteMedCareService
{
    public function __construct(
        private GeminiService $geminiService,
        private MedCareContextService $medCareContextService,
        private LembreteChatService $lembreteChatService)
        {}
    public function responder(User $user, string $mensagemUsuario): string{

        $respostaLembrete = $this->lembreteChatService->processar($user, $mensagemUsuario);

        if ($respostaLembrete !== null) {
            return $respostaLembrete;
        }

        $contextoUsuario = $this->medCareContextService->montar($user);

        $respostaGemini = $this->geminiService->gerarResposta(
            $mensagemUsuario,
            $contextoUsuario
        );

        if ($respostaGemini) {
            return $respostaGemini;
        }

        return $this->responderFallback($user, $mensagemUsuario);
    }

    private function responderFallback(User $user, string $mensagemUsuario): string
    {
        $mensagem = strtolower(trim($mensagemUsuario));

        if (str_contains($mensagem, 'consulta')) {
            return $this->responderConsulta($user);
        }

        if (str_contains($mensagem, 'vacina')) {
            return $this->responderVacinas($user);
        }

        if (str_contains($mensagem, 'plano')) {
            return $this->responderPlano($user);
        }

        if (str_contains($mensagem, 'resumo')) {
            return $this->responderResumo($user);
        }

        if (
            str_contains($mensagem, 'guia') ||
            str_contains($mensagem, 'médico') ||
            str_contains($mensagem, 'medico') ||
            str_contains($mensagem, 'clínica') ||
            str_contains($mensagem, 'clinica')
        ) {
            return $this->responderGuiaMedico($user);
        }

        return $this->responderAjudaInicial($user);
    }

    private function responderConsulta(User $user): string
    {
        return "Encontrei informações úteis para sua consulta.\n\n"
            . "Você poderá levar:\n\n"
            . "✅ Documento com foto\n"
            . "✅ Carteirinha do plano\n"
            . "✅ Exames recentes cadastrados\n"
            . "✅ Receitas ou medicamentos em uso\n"
            . "✅ Lista de sintomas ou dúvidas\n\n"
            . "Lembrete: minhas orientações não substituem atendimento médico profissional.";
    }

    private function responderVacinas(User $user): string
    {
        return "Verifiquei a área de vacinas do MedCare.\n\n"
            . "Na versão inteligente, o sistema poderá identificar vacinas pendentes, próximas doses e registros incompletos.\n\n"
            . "Lembrete: minhas orientações não substituem atendimento médico profissional.";
    }

    private function responderPlano(User $user): string
    {
        return "Posso te ajudar com os dados do seu plano de saúde.\n\n"
            . "Pelo MedCare, você poderá consultar nome do plano, número da carteirinha, validade, coberturas principais e telefones úteis.";
    }

    private function responderResumo(User $user): string
    {
        return "Resumo da sua jornada de saúde no MedCare:\n\n"
            . "• Exames cadastrados\n"
            . "• Vacinas acompanhadas\n"
            . "• Histórico de pronto-socorro\n"
            . "• Dados do plano\n"
            . "• Guia Médico\n\n"
            . "Esse resumo pode ajudar antes de consultas, mas não substitui atendimento médico profissional.";
    }

    private function responderGuiaMedico(User $user): string
    {
        return "Posso te ajudar a consultar o Guia Médico do MedCare.\n\n"
            . "Você poderá buscar médicos, clínicas, especialidades, localização e informações de contato.";
    }

    private function responderAjudaInicial(User $user): string
    {
        return "Olá! Sou o assistente inteligente do MedCare Digital.\n\n"
            . "Posso te ajudar com resumo da saúde, vacinas, preparo para consulta, dados do plano, Guia Médico e lembretes.\n\n"
            . "Exemplo: \"me lembre de tomar remédio amanhã às 8h\".\n\n"
            . "Lembrete: minhas orientações não substituem atendimento médico profissional.";
    }
}