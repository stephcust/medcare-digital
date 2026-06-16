<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Vacinacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VacinacaoController extends Controller
{
    public function index(Paciente $paciente)
    {
        // Segurança: Garante que o usuário logado só veja as vacinas do SEU próprio paciente
        if (Auth::user()->paciente->id !== $paciente->id) {
            abort(403, 'Acesso não autorizado ao prontuário deste paciente.');
        }

        $vacinacoes = $paciente->vacinacoes()
            ->orderBy('data_aplicacao', 'desc')
            ->get();

        return Inertia::render('Vacinacoes/Index', [
            'paciente' => $paciente,
            'vacinacoes' => $vacinacoes
        ]);
    }

    public function store(Request $request, Paciente $paciente)
    {
        $dadosValidados = $request->validate([
            'modo_cadastro' => 'required|string|in:manual,arquivo',
            'nome_vacina' => 'required_if:modo_cadastro,manual|nullable|string|max:255',
            'fabricante' => 'nullable|string|max:255',
            'lote' => 'nullable|string|max:100',
            'numero_dose' => 'required_if:modo_cadastro,manual|nullable|string',
            'data_aplicacao' => 'required|date',
            'data_proxima_dose' => 'nullable|date|after_or_equal:data_aplicacao',
            'observacoes' => 'nullable|string',
            'comprovante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // Max 10MB
        ], [
            'nome_vacina.required_if' => 'O nome da vacina é obrigatório para o cadastro manual.',
            'data_aplicacao.required' => 'A data de aplicação ou recepção do documento é obrigatória.',
            'data_proxima_dose.after_or_equal' => 'A data da próxima dose deve ser posterior à data de aplicação.'
        ]);

        $dadosValidados['paciente_id'] = $paciente->id;
        $dadosValidados['usuario_id'] = auth()->id();

        // Se o usuário estiver submetendo um comprovante físico/digital
        if ($request->hasFile('comprovante')) {
            $arquivo = $request->file('comprovante');

            // Define o nome único e o caminho organizado por usuário no Supabase
            $nomeArquivo = Str::uuid() . '.' . $arquivo->getClientOriginalExtension();
            $caminho = "usuario_" . auth()->id() . "/vacinas/" . $nomeArquivo;

            // Upload para o Bucket do Supabase
            Storage::disk('supabase')->put($caminho, file_get_contents($arquivo));

            // Salva as referências do arquivo no array de criação
            $dadosValidados['arquivo_path'] = $caminho;
            $dadosValidados['arquivo_url'] = Storage::disk('supabase')->url($caminho);

            // Caso seja apenas upload rápido, preenchemos o título automaticamente
            if ($dadosValidados['modo_cadastro'] === 'arquivo') {
                $dadosValidados['nome_vacina'] = $dadosValidados['nome_vacina'] ?? 'Comprovante de Vacinação Anexado';
                $dadosValidados['numero_dose'] = $dadosValidados['numero_dose'] ?? 'Dose Não Especificada';
            }
        }

        Vacinacao::create($dadosValidados);

        return redirect()->route('vacinacoes.index', $paciente->id)
            ->with('success', 'Registro de vacinação atualizado com sucesso!');
    }

    public function destroy(Vacinacao $vacinacao)
    {
        $pacienteId = $vacinacao->paciente_id;

        // Remove o arquivo do Supabase caso exista antes de deletar o registro do banco
        if ($vacinacao->arquivo_path) {
            Storage::disk('supabase')->delete($vacinacao->arquivo_path);
        }

        $vacinacao->delete();

        return redirect()->route('vacinacoes.index', $pacienteId)
            ->with('success', 'Registro de vacinação removido com sucesso.');
    }
}
