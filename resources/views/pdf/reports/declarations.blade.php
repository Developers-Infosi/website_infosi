{{-- Guardar como: resources/views/pdf/reports/declarations.blade.php

     A4 em paisagem. O número de colunas é escolhido pelo operador, por isso o
     tamanho da letra acompanha a quantidade seleccionada. --}}
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Declarações de Saúde do Viajante</title>
    @php
        $columnCount = count($columns);
        $fontSize = $columnCount > 16 ? 6 : ($columnCount > 10 ? 7 : 8);
    @endphp
    <style>
        @page { margin: 18px 22px 40px 22px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: {{ $fontSize }}px;
            color: #1a1a1a;
        }

        .header { border-bottom: 2px solid #1F4E79; padding-bottom: 8px; margin-bottom: 10px; }
        .header h1 { font-size: 13px; margin: 0 0 2px; color: #1F4E79; }
        .header h2 { font-size: 9px; margin: 0; font-weight: normal; color: #444; }

        .filters { margin-bottom: 8px; font-size: {{ $fontSize }}px; color: #444; }
        .filters b { color: #1a1a1a; }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            background: #1F4E79;
            color: #fff;
            border: 1px solid #bfbfbf;
            padding: 4px 3px;
            text-align: left;
            font-size: {{ $fontSize }}px;
        }

        tbody td {
            border: 1px solid #bfbfbf;
            padding: 3px;
            vertical-align: top;
            word-wrap: break-word;
        }

        tbody tr:nth-child(even) td { background: #f4f6f9; }

        /* Linha sinalizada: o técnico localiza-a sem ler o questionário todo */
        tbody tr.alert td { background: #fdecec; }

        .empty { text-align: center; padding: 24px; color: #777; }

        .footer {
            position: fixed;
            bottom: -24px;
            left: 0;
            right: 0;
            font-size: 7px;
            color: #777;
            border-top: 1px solid #dcdcdc;
            padding-top: 4px;
        }

        .footer .right { float: right; }
    </style>
</head>
<body>

    <div class="header">
        <h1>REPÚBLICA DE ANGOLA - MINISTÉRIO DA SAÚDE</h1>
        <h2>Direcção Nacional de Saúde Pública | Declarações de Saúde do Viajante</h2>
    </div>

    <div class="filters">
        <b>Estado:</b> {{ $filters['status'] }} &nbsp;|&nbsp;
        <b>Sinalização:</b> {{ $filters['screening'] }} &nbsp;|&nbsp;
        <b>Período de chegada:</b> {{ $filters['period'] }} &nbsp;|&nbsp;
        <b>Companhia:</b> {{ $filters['airline'] }} &nbsp;|&nbsp;
        <b>País sob vigilância:</b> {{ $filters['risk'] }} &nbsp;|&nbsp;
        <b>Total:</b> {{ $declarations->count() }} declaraç{{ $declarations->count() == 1 ? 'ão' : 'ões' }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:18px">#</th>
                @foreach ($columns as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($declarations as $declaration)
                <tr class="{{ $declaration->has_alert || $declaration->has_risk_exposure ? 'alert' : '' }}">
                    <td>{{ $loop->iteration }}</td>
                    @foreach (array_keys($columns) as $key)
                        <td>{{ $formatter($declaration, $key) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" class="empty">
                        Nenhuma declaração corresponde aos filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento gerado pelo sistema em {{ $generatedAt }}. As linhas assinaladas a vermelho exigem triagem sanitária.
        <span class="right">Ministério da Saúde da República de Angola</span>
    </div>

</body>
</html>
