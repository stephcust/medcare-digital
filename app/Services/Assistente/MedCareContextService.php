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
            'Exames',
            'exames',
            $user
        );

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

        $this->adicionarHistoricoClinico(
            $linhas,
            $user
        );

        $linhas[] = '';
        $linhas[] = 'Instruções para a IA:';
        $linhas[] = '- Para perguntas sobre os dados pessoais do usuário no MedCare, use apenas os registros listados acima.';
        $linhas[] = '- Não invente exames, vacinas, receitas, histórico clínico ou qualquer outro dado pessoal que não esteja no contexto.';
        $linhas[] = '- Para perguntas gerais e educativas de saúde pública, você pode usar conhecimento geral confiável, mesmo quando a informação não estiver cadastrada no MedCare.';
        $linhas[] = '- Pode explicar prevenção, vacinação, efeitos geralmente esperados, termos de exames, uso seguro de medicamentos e outras orientações gerais.';
        $linhas[] = '- Não faça diagnóstico, não prescreva medicamentos, não defina ou altere doses, não interrompa tratamentos e não substitua avaliação profissional.';
        $linhas[] = '- Quando a orientação depender do medicamento, da vacina ou da condição específica, faça perguntas para esclarecer e diga que a conduta exata deve ser confirmada na bula, com farmacêutico ou com o profissional responsável.';
        $linhas[] = '- Em caso de sinais intensos, persistentes ou potencialmente graves, oriente a procurar atendimento de saúde.';

        return implode("\n", $linhas);
    }

    /**
     * Monta somente o contexto escolhido pelo usuário para o sumário clínico.
     */
    public function montarResumo(
        User $user,
        array $secoes,
        string $periodo
    ): string {
        $secoes = array_values(array_unique($secoes));
        $dias = $this->diasDoPeriodo($periodo);

        $linhas = [
            'CONTEXTO SELECIONADO PARA O SUMÁRIO DE PREPARAÇÃO CLÍNICA',
            'Período solicitado: ' . $this->nomePeriodo($periodo),
            '',
        ];

        if (in_array('dados_pessoais', $secoes, true)) {
            $linhas[] = 'Dados básicos do usuário:';
            $linhas[] = "- Nome: {$user->name}";
            $linhas[] = "- E-mail: {$user->email}";
        }

        if (in_array('relatos', $secoes, true)) {
            $this->adicionarRegistrosDoUsuarioParaResumo(
                $linhas,
                'Sintomas, queixas e relatos da Jornada Inteligente',
                'relatos_saude',
                $user,
                $dias,
                true
            );
        }

        if (in_array('exames', $secoes, true)) {
            $this->adicionarRegistrosDoUsuarioParaResumo(
                $linhas,
                'Exames',
                'exames',
                $user,
                $dias
            );
        }

        if (in_array('receitas', $secoes, true)) {
            $this->adicionarRegistrosDoUsuarioParaResumo(
                $linhas,
                'Receitas',
                'receitas',
                $user,
                $dias
            );
        }

        if (in_array('vacinas', $secoes, true)) {
            $this->adicionarRegistrosDoPacienteParaResumo(
                $linhas,
                'Vacinas',
                'vacinacoes',
                $user,
                $dias
            );
        }

        if (in_array('historico_clinico', $secoes, true)) {
            $this->adicionarHistoricoClinicoParaResumo(
                $linhas,
                $user,
                $dias
            );
        }

        $linhas[] = '';
        $linhas[] = 'REGRAS OBRIGATÓRIAS PARA O SUMÁRIO:';
        $linhas[] = '- Use somente as informações presentes neste contexto.';
        $linhas[] = '- Não invente registros ausentes.';
        $linhas[] = '- Diferencie claramente relatos do usuário de documentos e registros do sistema.';
        $linhas[] = '- Não faça diagnóstico, não prescreva medicamentos e não altere tratamentos.';
        $linhas[] = '- Quando não houver dados em uma seção selecionada, informe isso de forma breve.';

        return implode("\n", $linhas);
    }

    public function nomePeriodo(string $periodo): string
    {
        return match ($periodo) {
            '30' => 'Últimos 30 dias',
            '60' => 'Últimos 60 dias',
            '90' => 'Últimos 90 dias',
            default => 'Todo o histórico disponível',
        };
    }

    private function diasDoPeriodo(string $periodo): ?int
    {
        return match ($periodo) {
            '30' => 30,
            '60' => 60,
            '90' => 90,
            default => null,
        };
    }

    private function adicionarHistoricoClinico(
        array &$linhas,
        User $user
    ): void {
        $tabela = $this->tabelaHistoricoClinico();

        if ($tabela === null) {
            $linhas[] = '';
            $linhas[] = 'Histórico Clínico:';
            $linhas[] = '- Nenhum registro encontrado para este usuário.';
            return;
        }

        $this->adicionarRegistrosDoUsuario(
            $linhas,
            'Histórico Clínico',
            $tabela,
            $user
        );
    }

    private function adicionarHistoricoClinicoParaResumo(
        array &$linhas,
        User $user,
        ?int $dias
    ): void {
        $tabela = $this->tabelaHistoricoClinico();

        if ($tabela === null) {
            $linhas[] = '';
            $linhas[] = 'Histórico Clínico:';
            $linhas[] = '- Nenhum registro disponível.';
            return;
        }

        $this->adicionarRegistrosDoUsuarioParaResumo(
            $linhas,
            'Histórico Clínico',
            $tabela,
            $user,
            $dias
        );
    }

    private function tabelaHistoricoClinico(): ?string
    {
        $possiveis = [
            'historico_clinico',
            'historicos_clinicos',
            'historico_ps',
            'historico_pronto_socorro',
        ];

        foreach ($possiveis as $tabela) {
            if (Schema::hasTable($tabela)) {
                return $tabela;
            }
        }

        return null;
    }

    private function adicionarRegistrosDoUsuarioParaResumo(
        array &$linhas,
        string $titulo,
        string $tabela,
        User $user,
        ?int $dias,
        bool $somenteMarcados = false
    ): void {
        if (!Schema::hasTable($tabela)) {
            $linhas[] = '';
            $linhas[] = "{$titulo}:";
            $linhas[] = '- Nenhum registro disponível.';
            return;
        }

        $colunas = Schema::getColumnListing($tabela);
        $colunaUsuario = $this->encontrarColunaUsuario($colunas);

        if (!$colunaUsuario) {
            $linhas[] = '';
            $linhas[] = "{$titulo}:";
            $linhas[] = '- Não foi possível relacionar os registros ao usuário.';
            return;
        }

        $this->adicionarRegistrosFiltradosParaResumo(
            $linhas,
            $titulo,
            $tabela,
            $colunas,
            $colunaUsuario,
            $user->id,
            $dias,
            $somenteMarcados
        );
    }

    private function adicionarRegistrosDoPacienteParaResumo(
        array &$linhas,
        string $titulo,
        string $tabela,
        User $user,
        ?int $dias
    ): void {
        if (!Schema::hasTable('pacientes') || !Schema::hasTable($tabela)) {
            $linhas[] = '';
            $linhas[] = "{$titulo}:";
            $linhas[] = '- Nenhum registro disponível.';
            return;
        }

        $pacienteId = DB::table('pacientes')
            ->where('user_id', $user->id)
            ->value('id');

        if (!$pacienteId) {
            $linhas[] = '';
            $linhas[] = "{$titulo}:";
            $linhas[] = '- Nenhum paciente vinculado ao usuário foi encontrado.';
            return;
        }

        $colunas = Schema::getColumnListing($tabela);

        if (!in_array('paciente_id', $colunas, true)) {
            $linhas[] = '';
            $linhas[] = "{$titulo}:";
            $linhas[] = '- Não foi possível relacionar as vacinas ao paciente.';
            return;
        }

        $this->adicionarRegistrosFiltradosParaResumo(
            $linhas,
            $titulo,
            $tabela,
            $colunas,
            'paciente_id',
            (int) $pacienteId,
            $dias
        );
    }

    private function adicionarRegistrosFiltradosParaResumo(
        array &$linhas,
        string $titulo,
        string $tabela,
        array $colunas,
        string $colunaFiltro,
        int $valorFiltro,
        ?int $dias,
        bool $somenteMarcados = false
    ): void {
        $colunasSelecionadas = $this->colunasRelevantes($colunas);

        $linhas[] = '';
        $linhas[] = "{$titulo}:";

        if (empty($colunasSelecionadas)) {
            $linhas[] = '- Nenhuma informação relevante foi encontrada.';
            return;
        }

        $query = DB::table($tabela)
            ->select($colunasSelecionadas)
            ->where($colunaFiltro, $valorFiltro);

        if (
            $somenteMarcados
            && in_array('incluir_no_resumo', $colunas, true)
        ) {
            $query->where('incluir_no_resumo', true);
        }

        $colunaData = $this->colunaData($colunas);

        if ($dias !== null && $colunaData !== null) {
            $query->where(
                $colunaData,
                '>=',
                now()->subDays($dias)->startOfDay()
            );
        }

        $total = (clone $query)->count();
        $colunaOrdenacao = $this->colunaOrdenacao($colunas);

        if ($colunaOrdenacao) {
            $query->orderByDesc($colunaOrdenacao);
        }

        $registros = $query->limit(50)->get();

        $linhas[] = "- Total encontrado no período: {$total}";

        if ($registros->isEmpty()) {
            $linhas[] = '- Nenhum registro encontrado no período selecionado.';
            return;
        }

        foreach ($registros as $registro) {
            $linhas[] = '- ' . $this->formatarRegistro((array) $registro);
        }
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

        array_pop($linhas);
        array_pop($linhas);

        $this->adicionarRegistrosFiltrados(
            $linhas,
            $titulo,
            $tabela,
            $colunas,
            'paciente_id',
            (int) $pacienteId
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
            'data_ocorrencia',
            'data_realizacao',
            'data_aplicacao',
            'data_emissao',
            'data_consulta',
            'data_atendimento',
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

    private function colunaData(array $colunas): ?string
    {
        $possiveis = [
            'data_ocorrencia',
            'data_realizacao',
            'data_aplicacao',
            'data_emissao',
            'data_consulta',
            'data_atendimento',
            'created_at',
            'updated_at',
            'data',
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
            'observacao',


            'medico',
            'especialidade',
            'medicamentos',
            'data_emissao',
            'data_validade',

            'clinica',
            'hospital',
            'nome_hospital',
            'unidade',
            'endereco',
            'bairro',
            'cidade',
            'telefone',
            'motivo',
            'queixa_principal',
            'procedimentos',
            'data_atendimento',
            'alta_em',
            'diagnostico',
            'tratamento',
            'condicao',
            'alergias',
            'antecedentes',
            'observacao_clinica',

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

            if (is_array($valor)) {
                $valor = json_encode($valor, JSON_UNESCAPED_UNICODE);
            }

            $nomeCampo = str_replace('_', ' ', $campo);
            $partes[] = "{$nomeCampo}: {$valor}";
        }

        return implode('; ', $partes);
    }
}
