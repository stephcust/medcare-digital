<?php

namespace App\Services\IA;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function gerarResposta(string $mensagemUsuario, string $contextoUsuario = ''): ?string
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        if (!$apiKey) {
            Log::warning('Chave da Gemini não configurada.');
            return null;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [
                            [
                                'text' => $this->instrucoesDoSistema(),
                            ],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                [
                                    'text' => $this->montarMensagem($mensagemUsuario, $contextoUsuario),
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 1200,
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Erro na API Gemini', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $partes = $response->json('candidates.0.content.parts', []);

            $texto = collect($partes)
                ->pluck('text')
                ->filter()
                ->implode("\n");

            return trim($texto) ?: null;
        } catch (\Throwable $e) {
            Log::error('Exceção ao chamar Gemini', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function analisarDocumento(string $caminhoArquivo, string $mimeType, string $promptInstrucao): ?string
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        if (!$apiKey) return null;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        // Converte o arquivo físico para base64 binário estruturado que a API exige
        $dadosBase64 = base64_encode(file_get_contents($caminhoArquivo));

        try {
            $response = Http::timeout(45)
                ->withoutVerifying() // Ignora problemas de SSL locais
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'inlineData' => [
                                        'mimeType' => $mimeType,
                                        'data' => $dadosBase64
                                    ]
                                ],
                                [
                                    'text' => $promptInstrucao
                                ]
                            ]
                        ]
                    ],
                    // Força a resposta da IA a vir estritamente como formato JSON limpo
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            Log::error('Erro na análise multimodal Gemini: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Exceção multimodal Gemini: ' . $e->getMessage());
            return null;
        }
    }

    private function instrucoesDoSistema(): string
    {
        return "
Você é o assistente inteligente do MedCare Digital.

Você conversa como um atendente virtual dentro de uma experiência parecida com WhatsApp.

Regras obrigatórias:
- Responda sempre em português do Brasil.
- Responda diretamente a pergunta do usuário.
- Não comece toda resposta reapresentando o MedCare.
- Não repita sempre 'Olá, tudo bem?'.
- Seja curto, natural e útil.
- Use no máximo 2 parágrafos curtos.
- Use listas apenas quando ajudar.
- Não invente dados específicos que não estejam no contexto.
- Não diga que algo já está cadastrado se o contexto não informar isso.
- Não dê diagnóstico médico.
- Não prescreva remédios.
- Não substitua atendimento médico profissional.
- Se o usuário perguntar se o sistema pode fazer algo, responda claramente se pode, se ainda é simulação ou se depende de dados cadastrados.
- Se a pergunta envolver sintomas, urgência, exame alterado ou tratamento, oriente a procurar um profissional de saúde.

Objetivo do assistente:
Ajudar o usuário a entender e usar o MedCare Digital, incluindo exames, vacinas, consultas, plano de saúde, histórico de pronto-socorro, receitas e guia médico.
";
    }

    private function montarMensagem(string $mensagemUsuario, string $contextoUsuario): string
    {
        return "
Contexto disponível:
{$contextoUsuario}

Pergunta do usuário:
{$mensagemUsuario}

Responda exatamente essa pergunta, sem fugir do assunto.
";
    }
}
