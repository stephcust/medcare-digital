<?php

namespace App\Http\Controllers\WhatsappSimulador;

use App\Http\Controllers\Controller;
use App\Models\ConversaAssistente;
use App\Services\Assistente\AssistenteMedCareService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class WhatsappSimuladorController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $historico = ConversaAssistente::query()
            ->with([
                'exame',
                'receita',
                'vacinacao.paciente',
                'historicoClinico.paciente',
                'resumoJornada',
            ])
            ->where('user_id', $userId)
            ->where('canal', 'simulador')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->reverse()
            ->values()
            ->map(function (ConversaAssistente $mensagem) use ($userId) {
                $examePermitido = $mensagem->exame
                    && (int) $mensagem->exame->user_id === (int) $userId;

                $receitaPermitida = $mensagem->receita
                    && (int) $mensagem->receita->user_id === (int) $userId;

                $vacinacaoPermitida = $mensagem->vacinacao
                    && $mensagem->vacinacao->paciente
                    && (int) $mensagem->vacinacao->paciente->user_id === (int) $userId;

                $historicoPermitido = $mensagem->historicoClinico
                    && $mensagem->historicoClinico->paciente
                    && (int) $mensagem->historicoClinico->paciente->user_id === (int) $userId;

                $resumoPermitido = $mensagem->resumoJornada
                    && (int) $mensagem->resumoJornada->user_id === (int) $userId;

                $downloadUrl = null;
                $visualizarUrl = null;
                $documentoTipo = null;

                if ($examePermitido) {
                    $downloadUrl = route('exames.download', $mensagem->exame->id);
                    $visualizarUrl = route('exames.visualizar', $mensagem->exame->id);
                    $documentoTipo = 'exame';
                } elseif ($receitaPermitida) {
                    $downloadUrl = route('receitas.download', $mensagem->receita->id);
                    $visualizarUrl = route('receitas.visualizar', $mensagem->receita->id);
                    $documentoTipo = 'receita';
                } elseif ($vacinacaoPermitida) {
                    $downloadUrl = route('vacinacoes.download', $mensagem->vacinacao->id);
                    $visualizarUrl = route('vacinacoes.visualizar', $mensagem->vacinacao->id);
                    $documentoTipo = 'vacina';
                } elseif ($historicoPermitido) {
                    $downloadUrl = route(
                        'historico-clinico.download',
                        $mensagem->historicoClinico->id
                    );
                    $visualizarUrl = route(
                        'historico-clinico.visualizar',
                        $mensagem->historicoClinico->id
                    );
                    $documentoTipo = 'historico';
                } elseif ($resumoPermitido) {
                    $downloadUrl = route(
                        'jornada-inteligente.resumos.download',
                        $mensagem->resumoJornada->id
                    );
                    $visualizarUrl = route(
                        'jornada-inteligente.resumos.visualizar',
                        $mensagem->resumoJornada->id
                    );
                    $documentoTipo = 'sumario';
                }

                return [
                    'id' => $mensagem->id,
                    'autor' => $mensagem->autor,
                    'texto' => $mensagem->texto,
                    'arquivo_nome' => $mensagem->arquivo_nome,
                    'exame_id' => $examePermitido ? $mensagem->exame->id : null,
                    'receita_id' => $receitaPermitida ? $mensagem->receita->id : null,
                    'vacinacao_id' => $vacinacaoPermitida ? $mensagem->vacinacao->id : null,
                    'historico_clinico_id' => $historicoPermitido
                        ? $mensagem->historicoClinico->id
                        : null,
                    'resumo_jornada_id' => $resumoPermitido
                        ? $mensagem->resumoJornada->id
                        : null,
                    'documento_tipo' => $documentoTipo,
                    'download_url' => $downloadUrl,
                    'visualizar_url' => $visualizarUrl,
                    'hora' => $mensagem->created_at
                        ->timezone(config('app.timezone'))
                        ->format('H:i'),
                ];
            });

        return Inertia::render('WhatsappSimulador/Index', [
            'historico' => $historico,
        ]);
    }

    public function enviar(
        Request $request,
        AssistenteMedCareService $assistenteMedCareService
    ): RedirectResponse {
        $dados = $request->validate([
            'mensagem' => ['nullable', 'string', 'max:1000'],
            'arquivo' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ]);

        $mensagem = trim($dados['mensagem'] ?? '');
        $arquivo = $request->file('arquivo');

        if ($mensagem === '' && !$arquivo) {
            return back()->withErrors([
                'mensagem' => 'Digite uma mensagem ou selecione um arquivo.',
            ]);
        }

        $textoExibido = $mensagem;

        if ($arquivo) {
            $anexo = "📎 Arquivo: {$arquivo->getClientOriginalName()}";
            $textoExibido = $textoExibido !== ''
                ? "{$textoExibido}\n\n{$anexo}"
                : $anexo;
        }

        ConversaAssistente::create([
            'user_id' => $request->user()->id,
            'canal' => 'simulador',
            'autor' => 'usuario',
            'texto' => $textoExibido,
            'arquivo_nome' => $arquivo?->getClientOriginalName(),
            'exame_id' => null,
            'receita_id' => null,
            'vacinacao_id' => null,
            'historico_clinico_id' => null,
            'resumo_jornada_id' => null,
        ]);

        try {
            $resposta = $assistenteMedCareService->responderComMetadados(
                $request->user(),
                $mensagem,
                $arquivo
            );
        } catch (Throwable $e) {
            report($e);

            $resposta = [
                'texto' => 'Houve um erro ao processar sua mensagem. Tente novamente.',
                'exame_id' => null,
                'receita_id' => null,
                'vacinacao_id' => null,
                'historico_clinico_id' => null,
                'resumo_jornada_id' => null,
                'arquivo_nome' => null,
            ];
        }

        ConversaAssistente::create([
            'user_id' => $request->user()->id,
            'canal' => 'simulador',
            'autor' => 'assistente',
            'texto' => $resposta['texto'],
            'arquivo_nome' => $resposta['arquivo_nome'] ?? null,
            'exame_id' => $resposta['exame_id'] ?? null,
            'receita_id' => $resposta['receita_id'] ?? null,
            'vacinacao_id' => $resposta['vacinacao_id'] ?? null,
            'historico_clinico_id' => $resposta['historico_clinico_id'] ?? null,
            'resumo_jornada_id' => $resposta['resumo_jornada_id'] ?? null,
        ]);

        return redirect()->route('whatsapp-simulador.index');
    }

    public function destruirMensagem(
        Request $request,
        ConversaAssistente $mensagem
    ): RedirectResponse {
        if (
            (int) $mensagem->user_id !== (int) $request->user()->id
            || $mensagem->canal !== 'simulador'
        ) {
            abort(403, 'Você não pode apagar esta mensagem.');
        }

        $mensagem->delete();

        return redirect()->route('whatsapp-simulador.index');
    }

    public function limparConversa(Request $request): RedirectResponse
    {
        ConversaAssistente::query()
            ->where('user_id', $request->user()->id)
            ->where('canal', 'simulador')
            ->delete();

        return redirect()->route('whatsapp-simulador.index');
    }
}
