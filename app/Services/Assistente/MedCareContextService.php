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
            'Dados básicos do usuário:',
            "- Nome: {$user->name}",
            "- E-mail: {$user->email}",
            '',
            'Resumo dos dados encontrados no MedCare:',
        ];

        $this->adicionarRegistrosDoUsuario(
            $linhas,
            'Relatos do paciente / Jornada Inteligente',
            'relatos_saude',
            $user
        );

        $this->adicionarRegistrosDoUsuario(
            $linhas,
            'Carteira digital / Plano de saúde',
            'carteira_digital',
            $user
        );

        $this->adicionarRegistrosDoUsuario(
            $linhas,
            'Exames',
            'exames',
            $user
        );

        // As vacinas pertencem ao paciente, e não diretamente ao usuário.
        $this->adicionarRegistrosDoPaciente(
            $linhas,
            'Vacinas',
            'vacinacoes',
            $user
        );

        $this->adicionarRegistrosDoUsuario(
            $linhas,
            'Receitas',
            'receitas',
            $user
        );

        $this->adicionarRegistrosDoUsuario(
            $linhas,
            'Histórico de pronto-socorro',
            'historico_pronto_socorro',
            $user
        );

        $this->adicionarRegistrosDoUsuario(
            $linhas,
            'Histórico PS',
            'historico_ps',
            $user
        );

        $this->adicionarRegistrosGerais(
            $linhas,
            'Guia Médico / Infraestrutura de saúde',
            'infraestrutura_saude'
        );

        $linhas[] = '';
        $linhas[] = 'Instruções para a IA:';
        $linhas[] = '- Para perguntas sobre os dados pessoais do usuário no MedCare, use apenas os registros listados acima.';
        $linhas[] = '- Não invente exames, vacinas, receitas, plano, histórico ou qualquer outro dado pessoal que não esteja no contexto.';
        $linhas[] = '- Para perguntas gerais e educativas de saúde pública, você pode usar conhecimento geral confiável, mesmo quando a informação não estiver cadastrada no MedCare.';
        $linhas[] = '- Pode explicar prevenção, vacinação, efeitos geralmente esperados, termos de exames, uso seguro de medicamentos e outras orientações gerais.';
        $linhas[] = '- Não faça diagnóstico, não prescreva medicamentos, não defina ou altere doses, não interrompa tratamentos e não substitua avaliação profissional.';
        $linhas[] = '- Quando a orientação depender do medicamento, da vacina ou da condição específica, faça perguntas para esclarecer e diga que a conduta exata deve ser confirmada na bula, com farmacêutico ou com o profissional responsável.';
        $linhas[] = '- Em caso de sinais intensos, persistentes ou potencialmente graves, oriente a procurar atendimento de saúde.';

        return implode("\n", $linhas);
    }

    private function adicionarRegistrosDoUsuario(
        array &$linhas,
        string $titulo,
        string $tabela,
        User $user
    ): void {
        if (!Schema::hasTable($tabela)) {
            $linhas[] = '';
            $linhas[] = "{$titulo}:";
            $linhas[] = '- Tabela não encontrada no banco.';
            return;
        }

        $colunas = Schema::getColumnListing($tabela);
        $colunaUsuario = $this->encontrarColunaUsuario($colunas);

        if (!$colunaUsuario) {
            $linhas[] = '';
            $linhas[] = "{$titulo}:";
            $linhas[] = '- Não foi possível relacionar esta tabela ao usuário logado.';
            return;
        }

        $this->adicionarRegistrosFiltrados(
            $linhas,
            $titulo,
            $tabela,
            $colunas,
            $colunaUsuario,
            $user->id
        );
    }

    private function adicionarRegistrosDoPaciente(
        array &$linhas,
        string $titulo,
        string $tabela,
        User $user
    ): void {
        $linhas[] = '';
        $linhas[] = "{$titulo}:";

        if (!Schema::hasTable('pacientes')) {
            $linhas[] = '- A tabela de pacientes não foi encontrada no banco.';
            return;
        }

        if (!Schema::hasTable($tabela)) {
            $linhas[] = "- A tabela {$tabela} não foi encontrada no banco.";
            return;
        }

        $pacienteId = DB::table('pacientes')
            ->where('user_id', $user->id)
            ->value('id');

        if (!$pacienteId) {
            $linhas[] = '- Nenhum paciente vinculado ao usuário logado foi encontrado.';
            return;
        }

        $colunas = Schema::getColumnListing($tabela);

        if (!in_array('paciente_id', $colunas, true)) {
            $linhas[] = '- A tabela não possui a coluna paciente_id.';
            return;
        }

        // O título já foi adicionado acima.
        array_pop($linhas);
        array_pop($linhas);

        $this->adicionarRegistrosFiltrados(
            $linhas,
            $titulo,
            $tabela,
            $colunas,
            'paciente_id',
            $pacienteId
        );
    }

    private function adicionarRegistrosFiltrados(
        array &$linhas,
        string $titulo,
        string $tabela,
        array $colunas,
        string $colunaFiltro,
        int $valorFiltro
    ): void {
        $colunasSelecionadas = $this->colunasRelevantes($colunas);

        $linhas[] = '';
        $linhas[] = "{$titulo}:";

        if (empty($colunasSelecionadas)) {
            $linhas[] = '- Nenhuma coluna relevante encontrada.';
            return;
        }

        $query = DB::table($tabela)
            ->select($colunasSelecionadas)
            ->where($colunaFiltro, $valorFiltro);

        $total = (clone $query)->count();

        $colunaOrdenacao = $this->colunaOrdenacao($colunas);

        if ($colunaOrdenacao) {
            $query->orderByDesc($colunaOrdenacao);
        }

        $registros = $query->limit(5)->get();

        $linhas[] = "- Total de registros encontrados para este usuário: {$total}";

        if ($registros->isEmpty()) {
            $linhas[] = '- Nenhum registro encontrado para este usuário.';
            return;
        }

        foreach ($registros as $registro) {
            $linhas[] = '- ' . $this->formatarRegistro((array) $registro);
        }
    }

    private function adicionarRegistrosGerais(
        array &$linhas,
        string $titulo,
        string $tabela
    ): void {
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

        $linhas[] = '';
        $linhas[] = "{$titulo}:";
        $linhas[] = "- Total de registros gerais encontrados: {$total}";

        if ($registros->isEmpty()) {
            $linhas[] = '- Nenhum registro encontrado.';
            return;
        }

        foreach ($registros as $registro) {
            $linhas[] = '- ' . $this->formatarRegistro((array) $registro);
        }
    }

    private function encontrarColunaUsuario(array $colunas): ?string
    {
        $possiveis = [
            'user_id',
            'usuario_id',
            'id_usuario',
            'id_user',
        ];

        foreach ($possiveis as $coluna) {
            if (in_array($coluna, $colunas, true)) {
                return $coluna;
            }
        }

        return null;
    }

    private function colunaOrdenacao(array $colunas): ?string
    {
        $possiveis = [
            'data_realizacao',
            'data_aplicacao',
            'data_consulta',
            'data_ocorrencia',
            'created_at',
            'updated_at',
            'data',
            'id',
        ];

        foreach ($possiveis as $coluna) {
            if (in_array($coluna, $colunas, true)) {
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
            'categoria',
            'relato',
            'data_ocorrencia',
            'incluir_no_resumo',
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
            'fabricante',
            'lote',
            'numero_dose',
            'dose',
            'data_aplicacao',
            'data_proxima_dose',
            'observacoes',

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
