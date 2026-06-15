<?php

namespace App\Services\Assistente;

use App\Models\User;

class AssistenteMedCareService
{
    public function responder(User $user, string $mensagemUsuario): string
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
            . "Com base nos dados cadastrados no MedCare, você poderá levar:\n\n"
            . "✅ Documento com foto\n"
            . "✅ Carteirinha do plano\n"
            . "✅ Exames recentes cadastrados\n"
            . "✅ Receitas ou medicamentos em uso\n"
            . "✅ Lista de sintomas ou dúvidas\n\n"
            . "Quando a IA Gemini estiver integrada, esse resumo será gerado usando seus dados reais, como exames, vacinas, histórico PS e plano de saúde.\n\n"
            . "Lembrete: minhas orientações não substituem atendimento médico profissional.";
    }

    private function responderVacinas(User $user): string
    {
        return "Verifiquei a área de vacinas do MedCare.\n\n"
            . "Na versão inteligente, o sistema poderá identificar:\n\n"
            . "🔔 Vacinas pendentes\n"
            . "🔔 Próximas doses\n"
            . "🔔 Vacinas com registro incompleto\n\n"
            . "Exemplo de alerta:\n"
            . "“Você possui uma vacina pendente ou próxima da data de acompanhamento.”\n\n"
            . "Deseja que eu registre um lembrete para acompanhar suas vacinas?";
    }

    private function responderPlano(User $user): string
    {
        return "Posso te ajudar com os dados do seu plano de saúde.\n\n"
            . "Pelo MedCare, você poderá consultar rapidamente:\n\n"
            . "• Nome do plano\n"
            . "• Número da carteirinha\n"
            . "• Validade\n"
            . "• Coberturas principais\n"
            . "• Telefones úteis\n\n"
            . "Na integração com IA, essas informações serão usadas para gerar respostas mais contextualizadas.";
    }

    private function responderResumo(User $user): string
    {
        return "Resumo da sua jornada de saúde no MedCare:\n\n"
            . "• Exames recentes cadastrados\n"
            . "• Vacinas acompanhadas pelo sistema\n"
            . "• Histórico de pronto-socorro disponível\n"
            . "• Dados do plano organizados\n"
            . "• Guia Médico para apoio na busca de atendimento\n\n"
            . "Esse resumo poderá ser usado antes de consultas para ajudar o usuário a lembrar informações importantes.\n\n"
            . "Com o Gemini, esse resumo será personalizado com base nos dados reais do usuário.";
    }

    private function responderGuiaMedico(User $user): string
    {
        return "Posso te ajudar a consultar o Guia Médico do MedCare.\n\n"
            . "Você poderá buscar por:\n\n"
            . "• Médicos\n"
            . "• Clínicas\n"
            . "• Especialidades\n"
            . "• Localização\n"
            . "• Informações de contato\n\n"
            . "Exemplo:\n"
            . "“Encontrei clínicas relacionadas à sua busca. Deseja filtrar por especialidade ou bairro?”";
    }

    private function responderAjudaInicial(User $user): string
    {
        return "Olá! Sou o assistente do MedCare Digital.\n\n"
            . "Posso te ajudar com:\n\n"
            . "• Resumo da sua saúde\n"
            . "• Vacinas pendentes\n"
            . "• Preparo para consulta\n"
            . "• Dados do plano\n"
            . "• Guia Médico\n\n"
            . "Digite, por exemplo:\n"
            . "“Tenho consulta amanhã”\n"
            . "“Quero ver minhas vacinas”\n"
            . "“Gerar resumo da minha saúde”\n"
            . "“Preciso de uma clínica perto de mim”";
    }
}