<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Resumo do Atendimento</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.45; }
        h1 { color: #1d4ed8; font-size: 21px; margin-bottom: 4px; }
        .aviso { color: #7c2d12; background: #fff7ed; border: 1px solid #fed7aa; padding: 9px; margin: 14px 0; }
        .meta { background: #eff6ff; border: 1px solid #bfdbfe; padding: 10px; margin-bottom: 14px; }
        .secao { margin-top: 14px; }
        .secao h2 { font-size: 13px; color: #334155; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td { border: 1px solid #e2e8f0; padding: 7px; vertical-align: top; }
        td:first-child { width: 28%; font-weight: bold; background: #f8fafc; }
        .tag { display: inline-block; border: 1px solid #bfdbfe; background: #eff6ff; padding: 3px 6px; margin: 2px; border-radius: 4px; }
        .rodape { margin-top: 22px; font-size: 9px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <h1>Resumo pessoal do atendimento</h1>
    <div>MedCare Digital</div>

    <div class="aviso">
        <strong>Aviso:</strong> este resumo foi organizado pelo MedCare Digital a partir dos dados cadastrados pelo usuário ou extraídos de um documento. Não substitui relatório médico, prontuário, laudo ou documento emitido pelo hospital.
    </div>

    <div class="meta">
        <strong>Paciente:</strong> {{ $paciente?->nome_completo ?: $paciente?->user?->name ?: 'Usuário do MedCare' }}<br>
        <strong>Data do atendimento:</strong> {{ $registro->data_atendimento?->format('d/m/Y H:i') ?: 'Não informada' }}<br>
        <strong>Origem do registro:</strong> {{ $registro->origem ?: 'manual' }}
    </div>

    <table>
        <tr><td>Motivo</td><td>{{ $registro->motivo_atendimento ?: 'Não informado' }}</td></tr>
        <tr><td>Gravidade</td><td>{{ $registro->gravidade ?: 'Não informada' }}</td></tr>
        <tr><td>Local</td><td>{{ $registro->local_atendimento ?: 'Não informado' }}</td></tr>
        <tr><td>Médico(a)</td><td>{{ $registro->medico_nome ?: 'Não informado' }}</td></tr>
        <tr><td>Diagnóstico informado</td><td>{{ $registro->diagnostico ?: 'Não informado' }}</td></tr>
        <tr><td>Tratamento</td><td>{{ $registro->tratamento ?: 'Não informado' }}</td></tr>
        <tr><td>Desfecho</td><td>{{ $registro->desfecho ?: 'Não informado' }}</td></tr>
        <tr><td>Acompanhamento</td><td>{{ $registro->acompanhamento ?: 'Não informado' }}</td></tr>
        <tr><td>Observações</td><td>{{ $registro->observacoes ?: 'Nenhuma' }}</td></tr>
    </table>

    <div class="secao">
        <h2>Exames realizados</h2>
        @forelse(($registro->exames_realizados ?? []) as $exame)
            <span class="tag">{{ is_array($exame) ? json_encode($exame, JSON_UNESCAPED_UNICODE) : $exame }}</span>
        @empty
            <p>Nenhum exame informado.</p>
        @endforelse
    </div>

    <div class="secao">
        <h2>Medicamentos aplicados</h2>
        @forelse(($registro->medicamentos ?? []) as $medicamento)
            @php
                $nome = is_array($medicamento) ? ($medicamento['nome'] ?? 'Medicamento') : $medicamento;
                $dosagem = is_array($medicamento) ? ($medicamento['dosagem'] ?? null) : null;
            @endphp
            <span class="tag">{{ $nome }}{{ $dosagem ? ' — ' . $dosagem : '' }}</span>
        @empty
            <p>Nenhum medicamento informado.</p>
        @endforelse
    </div>

    <div class="rodape">
        Gerado em {{ now()->format('d/m/Y H:i') }} pelo MedCare Digital.
    </div>
</body>
</html>
