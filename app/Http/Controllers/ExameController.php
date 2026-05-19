<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exames\StoreExameRequest;
use App\Models\Exame;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ExameController extends Controller
{
    /**
     * Exibe a linha do tempo com os exames do paciente.
     */
    public function index()
    {
        // Garante que o paciente só veja os SEUS próprios exames (Regra de Negócio)
        $exames = Exame::where('user_id', auth()->id())
            ->orderBy('data_realizacao', 'desc')
            ->get();

        return Inertia::render('Exame/Index', [
            'exames' => $exames,
            'success' => session('success')
        ]);
    }

    /**
     * Exibe o formulário de cadastro manual.
     */
    public function create()
    {
        return Inertia::render('Exame/Create');
    }

    /**
     * Processa o upload e salva o registro no banco de dados.
     */
    public function store(StoreExameRequest $request)
    {
        // Captura o arquivo enviado pelo formulário Vue
        $file = $request->file('arquivo');

        // Armazenamento Seguro: Salva na pasta privada do sistema de arquivos
        // Não usamos o disco 'public' por questões de segurança de dados médicos
        $path = $file->store('exames_pacientes', 'local');

        // Criação do registro associando ao usuário autenticado
        Exame::create([
            'user_id'         => auth()->id(),
            'nome'            => $request->nome,
            'tipo'            => $request->tipo,
            'laboratorio'     => $request->laboratorio ?? 'Não informado',
            'data_realizacao' => $request->data_realizacao,
            'arquivo_path'    => $path,
            'visualizado'     => true, // Como foi ele quem postou, já conta como visto
            'origem'          => 'manual',
        ]);

        // Redireciona o usuário usando a sessão do Inertia de volta para a listagem
        return redirect()->route('exames.index')->with('success', 'Exame anexado com sucesso!');
    }

    /**
     * Exibe os detalhes do exame e marca como visualizado (limpa o alerta da Home).
     */
    public function show(Exame $exame)
    {
        // Regra de Negócio/Privacidade: Validação de segurança de escopo do SO de dados
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
     * Endpoint seguro para download do arquivo protegido da memória do servidor.
     */
    public function download(Exame $exame)
    {
        if ($exame->user_id !== auth()->id()) {
            abort(403, 'Acesso não autorizado.');
        }

        if (!Storage::disk('local')->exists($exame->arquivo_path)) {
            abort(444, 'Arquivo não encontrado no sistema.');
        }

        return Storage::disk('local')->download($exame->arquivo_path, $exame->nome . '.pdf');
    }

    /**
     * Remove o exame do banco e elimina o arquivo físico do HD.
     */
    public function destroy(Exame $exame)
    {
        if ($exame->user_id !== auth()->id()) {
            abort(403);
        }

        // Chamada de sistema para desalocar o arquivo físico antes de limpar o registro
        if (Storage::disk('local')->exists($exame->arquivo_path)) {
            Storage::disk('local')->delete($exame->arquivo_path);
        }

        $exame->delete();

        return redirect()->route('exames.index')->with('success', 'Exame removido com sucesso!');
    }
}
