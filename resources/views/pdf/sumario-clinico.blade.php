<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resumo['titulo'] ?? 'Sumário de Preparação Clínica' }}</title>
    <style>
        @page { margin: 22mm 18mm 20mm 18mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            background: #ffffff;
        }
        .print-toolbar {
            margin: 0 auto 18px auto;
            max-width: 900px;
            padding: 12px;
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            background: #f8fafc;
            text-align: right;
        }
        .print-toolbar button {
            border: 0;
            background: #4f46e5;
            color: white;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .documento {
            max-width: 900px;
            margin: 0 auto;
        }
        .cabecalho {
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .marca {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .selo {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 8px;
            border-radius: 4px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .6px;
        }
        h1 {
            margin: 16px 0 4px 0;
            font-size: 22px;
            color: #111827;
        }
        .subtitulo { color: #64748b; }
        .meta {
            width: 100%;
            margin: 16px 0 20px 0;
            border-collapse: collapse;
        }
        .meta td {
            border: 1px solid #dbe3ef;
            padding: 8px 10px;
            vertical-align: top;
        }
        .meta .rotulo {
            width: 24%;
            background: #f8fafc;
            color: #475569;
            font-weight: bold;
        }
        .secao {
            margin: 0 0 18px 0;
            page-break-inside: avoid;
        }
        .secao h2 {
            margin: 0 0 8px 0;
            padding: 8px 10px;
            border-left: 4px solid #4f46e5;
            background: #f8fafc;
            color: #1e293b;
            font-size: 14px;
        }
        ul, ol { margin: 0; padding-left: 20px; }
        li { margin-bottom: 6px; }
        .aviso {
            margin-top: 24px;
            padding: 12px;
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 9px;
        }
        .rodape {
            margin-top: 16px;
            text-align: center;
            color: #94a3b8;
            font-size: 8px;
        }
        @media print {
            .print-toolbar { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body @if($modoImpressao ?? false) onload="window.print()" @endif>
@if($modoImpressao ?? false)
    <div class="print-toolbar">
        <button type="button" onclick="window.print()">Imprimir agora</button>
    </div>
@endif

<div class="documento">
    <header class="cabecalho">
        <div class="marca">MedCare Digital</div>
        <span class="selo">Documento auxiliar</span>
        <h1>{{ $resumo['titulo'] ?? 'Sumário de Preparação Clínica' }}</h1>
        <div class="subtitulo">
            Documento organizado a partir dos dados cadastrados pelo próprio usuário na plataforma.
        </div>
    </header>

    <table class="meta">
        <tr>
            <td class="rotulo">Paciente</td>
            <td>{{ $usuario->name }}</td>
        </tr>
        <tr>
            <td class="rotulo">Período</td>
            <td>{{ $resumo['periodo'] ?? 'Todo o histórico disponível' }}</td>
        </tr>
        <tr>
            <td class="rotulo">Gerado em</td>
            <td>{{ $resumo['gerado_em'] ?? optional($resumoRegistro->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="rotulo">Origem</td>
            <td>{{ $resumoRegistro->origem === 'simulador' ? 'Simulador do MedCare' : 'Jornada Inteligente' }}</td>
        </tr>
    </table>

    @foreach(($resumo['secoes'] ?? []) as $secao)
        <section class="secao">
            <h2>{{ $secao['titulo'] ?? 'Seção' }}</h2>
            <ul>
                @foreach(($secao['itens'] ?? []) as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </section>
    @endforeach

    @if(!empty($resumo['perguntas_medico']))
        <section class="secao">
            <h2>Perguntas sugeridas para a consulta</h2>
            <ol>
                @foreach($resumo['perguntas_medico'] as $pergunta)
                    <li>{{ $pergunta }}</li>
                @endforeach
            </ol>
        </section>
    @endif

    <div class="aviso">
        Este é um resumo pessoal e auxiliar gerado pelo MedCare Digital. Ele não substitui prontuário, laudo, receita, atestado, relatório hospitalar ou avaliação de um profissional de saúde. Informações declaradas pelo usuário devem ser confirmadas durante a consulta.
    </div>

    <div class="rodape">
        MedCare Digital - Sumário de Preparação Clínica
    </div>
</div>
</body>
</html>
