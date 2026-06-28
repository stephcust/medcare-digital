<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PerfilPaciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PerfilSaudeController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $paciente = Paciente::firstOrCreate(['user_id' => $user->id]);
        $perfil = PerfilPaciente::firstOrNew(['user_id' => $user->id]);

        return Inertia::render('PerfilSaude/Index', [
            'perfil' => $this->serializar($user, $paciente, $perfil),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'data_nascimento' => ['required', 'date', 'before_or_equal:today'],
            'genero' => ['nullable', 'string', 'max:30'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'tipo_sanguineo' => [
                'nullable',
                Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            ],
            'peso_kg' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'altura_cm' => ['nullable', 'integer', 'min:30', 'max:250'],
            'alergias' => ['array', 'max:30'],
            'alergias.*' => ['string', 'max:255'],
            'condicoes_cronicas' => ['array', 'max:30'],
            'condicoes_cronicas.*' => ['string', 'max:255'],
            'medicamentos_continuos' => ['array', 'max:50'],
            'medicamentos_continuos.*' => ['string', 'max:255'],
            'cirurgias_anteriores' => ['array', 'max:30'],
            'cirurgias_anteriores.*' => ['string', 'max:255'],
            'dispositivos_implantes' => ['array', 'max:30'],
            'dispositivos_implantes.*' => ['string', 'max:255'],
            'observacoes_importantes' => ['nullable', 'string', 'max:3000'],
            'contato_emergencia_nome' => ['nullable', 'string', 'max:150'],
            'contato_emergencia_telefone' => ['nullable', 'string', 'max:30'],
            'contato_emergencia_parentesco' => ['nullable', 'string', 'max:80'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $dados): void {
            $paciente = Paciente::firstOrCreate(['user_id' => $user->id]);
            $perfil = PerfilPaciente::firstOrNew(['user_id' => $user->id]);

            $alergias = $this->limparLista($dados['alergias'] ?? []);

            $paciente->fill([
                'genero' => $dados['genero'] ?? null,
                'telefone' => $dados['telefone'] ?? null,
                'tipo_sanguineo' => $dados['tipo_sanguineo'] ?? null,
                'alergias_conhecidas' => empty($alergias)
                    ? null
                    : json_encode($alergias, JSON_UNESCAPED_UNICODE),
            ])->save();

            $novoPeso = isset($dados['peso_kg']) && $dados['peso_kg'] !== ''
                ? (float) $dados['peso_kg']
                : null;

            $pesoMudou = (string) ($perfil->peso_kg ?? '') !== (string) ($novoPeso ?? '');

            $perfil->fill([
                'user_id' => $user->id,
                'data_nascimento' => $dados['data_nascimento'],
                'tipo_sanguineo' => $dados['tipo_sanguineo'] ?? null,
                'alergias_conhecidas' => empty($alergias)
                    ? null
                    : json_encode($alergias, JSON_UNESCAPED_UNICODE),
                'peso_kg' => $novoPeso,
                'altura_cm' => $dados['altura_cm'] ?? null,
                'peso_atualizado_em' => $pesoMudou ? now() : $perfil->peso_atualizado_em,
                'condicoes_cronicas' => $this->limparLista($dados['condicoes_cronicas'] ?? []),
                'medicamentos_continuos' => $this->limparLista($dados['medicamentos_continuos'] ?? []),
                'cirurgias_anteriores' => $this->limparLista($dados['cirurgias_anteriores'] ?? []),
                'dispositivos_implantes' => $this->limparLista($dados['dispositivos_implantes'] ?? []),
                'observacoes_importantes' => $dados['observacoes_importantes'] ?? null,
                'contato_emergencia_nome' => $dados['contato_emergencia_nome'] ?? null,
                'contato_emergencia_telefone' => $dados['contato_emergencia_telefone'] ?? null,
                'contato_emergencia_parentesco' => $dados['contato_emergencia_parentesco'] ?? null,
            ])->save();
        });

        return redirect()
            ->route('perfil-saude.index')
            ->with('success', 'Perfil de Saúde atualizado com sucesso.');
    }

    private function serializar($user, Paciente $paciente, PerfilPaciente $perfil): array
    {
        return [
            'nome' => $user->name,
            'email' => $user->email,
            'data_nascimento' => $perfil->data_nascimento?->format('Y-m-d'),
            'idade' => $perfil->data_nascimento?->age,
            'genero' => $paciente->genero,
            'telefone' => $paciente->telefone,
            'tipo_sanguineo' => $paciente->tipo_sanguineo
                ?: $perfil->tipo_sanguineo,
            'peso_kg' => $perfil->peso_kg !== null
                ? (float) $perfil->peso_kg
                : null,
            'altura_cm' => $perfil->altura_cm,
            'peso_atualizado_em' => $perfil->peso_atualizado_em
                ?->timezone(config('app.timezone'))
                ->format('d/m/Y H:i'),
            'alergias' => $this->normalizarLista(
                $paciente->alergias_conhecidas ?: $perfil->alergias_conhecidas
            ),
            'condicoes_cronicas' => $perfil->condicoes_cronicas ?? [],
            'medicamentos_continuos' => $perfil->medicamentos_continuos ?? [],
            'cirurgias_anteriores' => $perfil->cirurgias_anteriores ?? [],
            'dispositivos_implantes' => $perfil->dispositivos_implantes ?? [],
            'observacoes_importantes' => $perfil->observacoes_importantes,
            'contato_emergencia_nome' => $perfil->contato_emergencia_nome,
            'contato_emergencia_telefone' => $perfil->contato_emergencia_telefone,
            'contato_emergencia_parentesco' => $perfil->contato_emergencia_parentesco,
            'atualizado_em' => $perfil->exists
                ? $perfil->updated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i')
                : null,
        ];
    }

    private function limparLista(array $itens): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $itens
        ))));
    }

    private function normalizarLista(mixed $valor): array
    {
        if (is_array($valor)) {
            return $this->limparLista($valor);
        }

        if (!is_string($valor) || trim($valor) === '') {
            return [];
        }

        $json = json_decode($valor, true);

        if (is_array($json)) {
            return $this->limparLista($json);
        }

        return $this->limparLista(preg_split('/[\n,;]+/u', $valor) ?: []);
    }
}
