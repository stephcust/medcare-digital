<?php

namespace App\Services\Assistente;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MedCareContextService
{
    public function montar(User $user): string
    {
        $linhas = [
            "Dados básicos do usuário:",
            "- Nome: {$user->name}",
            "- E-mail: {$user->email}",
            "",
            "Resumo dos dados encontrados no MedCare:",
        ];

        $this->adicionarRegistrosDoUsuario($linhas, 'Carteira digital / Plano de saúde', 'carteira_digital', $user);
        $this->adicionarRegistrosDoUsuario($linhas, 'Exames', 'exames', $user);
        $this->adicionarRegistrosDoUsuario($linhas, 'Vacinas', 'vacinas', $user);
        $this->adicionarRegistrosDoUsuario($linhas, 'Receitas', 'receitas', $user);
        $this->adicionarRegistrosDoUsuario($linhas, 'Histórico de pronto-socorro', 'historico_pronto_socorro', $user);
        $this->adicionarRegistrosDoUsuario($linhas, 'Histórico PS', 'historico_ps', $user);

        $this->adicionarRegistrosGerais($linhas, 'Guia Médico / Infraestrutura de saúde', 'infraestrutura_saude');

        $linhas[] = "";
        $linhas[] = "Instrução para a IA:";
        $linhas[] = "- Use apenas os dados listados acima.";
        $linhas[] = "- Se não houver registro em algum módulo, informe que nenhum dado foi encontrado.";
        $linhas[] = "- Não invente exames, vacinas, plano ou histórico que não estejam no contexto.";

        return implode("\n", $linhas);
    }

    private function adicionarRegistrosDoUsuario(array &$linhas, string $titulo, string $tabela, User $user): void
    {
        if (!Schema::hasTable($tabela)) {
            $linhas[] = "";
            $linhas[] = "{$titulo}:";
            $linhas[] = "- Tabela não encontrada no banco.";
            return;
        }

        $colunas = Schema::getColumnListing($tabela);
        $colunaUsuario = $this->encontrarColunaUsuario($colunas);

        if (!$colunaUsuario) {
            $linhas[] = "";
            $linhas[] = "{$titulo}:";
            $linhas[] = "- Não foi possível relacionar esta tabela ao usuário logado.";
            return;
        }

        $colunasSelecionadas = $this->colunasRelevantes($colunas);

        if (empty($colunasSelecionadas)) {
            $linhas[] = "";
            $linhas[] = "{$titulo}:";
            $linhas[] = "- Nenhuma coluna relevante encontrada.";
            return;
        }

        $query = DB::table($tabela)
            ->select($colunasSelecionadas)
            ->where($colunaUsuario, $user->id);

        $total = (clone $query)->count();

        $colunaOrdenacao = $this->colunaOrdenacao($colunas);

        if ($colunaOrdenacao) {
            $query->orderByDesc($colunaOrdenacao);
        }

        $registros = $query->limit(5)->get();

        $linhas[] = "";
        $linhas[] = "{$titulo}:";
        $linhas[] = "- Total de registros encontrados para este usuário: {$total}";

        if ($registros->isEmpty()) {
            $linhas[] = "- Nenhum registro encontrado para este usuário.";
            return;
        }

        foreach ($registros as $registro) {
            $linhas[] = "- " . $this->formatarRegistro((array) $registro);
        }
    }

    private function adicionarRegistrosGerais(array &$linhas, string $titulo, string $tabela): void
    {
        if (!Schema::hasTable($tabela)) {
            return;
        }

        $colunas = Schema::getColumnListing($tabela);
        $colunasSelecionadas = $this->colunasRelevantes($colunas);

        if (empty($colunasSelecionadas)) {
            return;
        }

        $query = DB::table($tabela)->select($colunasSelecionadas);

        $total = DB::table($tabela)->count();

        $colunaOrdenacao = $this->colunaOrdenacao($colunas);

        if ($colunaOrdenacao) {
            $query->orderByDesc($colunaOrdenacao);
        }

        $registros = $query->limit(5)->get();

        $linhas[] = "";
        $linhas[] = "{$titulo}:";
        $linhas[] = "- Total de registros gerais encontrados: {$total}";

        if ($registros->isEmpty()) {
            $linhas[] = "- Nenhum registro encontrado.";
            return;
        }

        foreach ($registros as $registro) {
            $linhas[] = "- " . $this->formatarRegistro((array) $registro);
        }
    }

    private function encontrarColunaUsuario(array $colunas): ?string
    {
        $possiveis = [
            'user_id',
            'usuario_id',
            'paciente_id',
            'id_usuario',
            'id_user',
        ];

        foreach ($possiveis as $coluna) {
            if (in_array($coluna, $colunas)) {
                return $coluna;
            }
        }

        return null;
    }

    private function colunaOrdenacao(array $colunas): ?string
    {
        $possiveis = [
            'created_at',
            'updated_at',
            'data_realizacao',
            'data_aplicacao',
            'data_consulta',
            'data',
            'id',
        ];

        foreach ($possiveis as $coluna) {
            if (in_array($coluna, $colunas)) {
                return $coluna;
            }
        }

        return null;
    }

    private function colunasRelevantes(array $colunas): array
    {
        $permitidas = [
            'id',
            'nome',
            'titulo',
            'tipo',
            'descricao',
            'status',
            'origem',

            'nome_exame',
            'laboratorio',
            'data_realizacao',
            'resultado',
            'visualizado',

            'nome_vacina',
            'vacina',
            'dose',
            'data_aplicacao',
            'data_proxima_dose',

            'nome_plano',
            'plano',
            'operadora',
            'numero_carteirinha',
            'validade',
            'cobertura',

            'medico',
            'especialidade',
            'clinica',
            'hospital',
            'unidade',
            'endereco',
            'bairro',
            'cidade',
            'telefone',

            'data',
            'data_consulta',
            'created_at',
            'updated_at',
        ];

        return array_values(array_intersect($permitidas, $colunas));
    }

    private function formatarRegistro(array $registro): string
    {
        $partes = [];

        foreach ($registro as $campo => $valor) {
            if ($valor === null || $valor === '') {
                continue;
            }

            if (is_bool($valor)) {
                $valor = $valor ? 'sim' : 'não';
            }

            $nomeCampo = str_replace('_', ' ', $campo);
            $partes[] = "{$nomeCampo}: {$valor}";
        }

        return implode('; ', $partes);
    }
}