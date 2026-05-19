<?php

namespace App\Http\Requests\Exames;

use Illuminate\Foundation\Http\FormRequest;

class StoreExameRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer este request.
     */
    public function authorize(): bool
    {
        // Como o usuário precisa estar logado (passando pelo middleware auth), permitimos aqui.
        return true;
    }

    /**
     * Regras de validação aplicadas ao formulário de envio de exame.
     */
    public function rules(): array
    {
        return [
            'nome'             => 'required|string|max:255',
            'tipo'             => 'required|string|max:100',
            'laboratorio'      => 'nullable|string|max:255',
            'data_realizacao'  => 'required|date|before_or_equal:today',
            // Validação do arquivo: máximo 10MB, formatos PDF ou imagens comuns
            'arquivo'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    /**
     * Mensagens personalizadas de erro (opcional, mas melhora a experiência no Vue).
     */
    public function messages(): array
    {
        return [
            'nome.required'             => 'O nome do exame é obrigatório.',
            'tipo.required'             => 'Selecione o tipo do exame.',
            'data_realizacao.required'  => 'A data de realização é obrigatória.',
            'data_realizacao.before_or_equal' => 'A data não pode ser futura.',
            'arquivo.required'          => 'O documento do exame é obrigatório.',
            'arquivo.mimes'             => 'O arquivo deve ser do tipo: PDF, JPG, JPEG ou PNG.',
            'arquivo.max'               => 'O arquivo não pode passar de 10MB.',
        ];
    }
}