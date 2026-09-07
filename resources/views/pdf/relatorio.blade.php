<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10.5px;
            color: #14171d;
            margin: 0;
            padding: 34px 40px 60px;
        }

        /* ---------- Header: logo (right) + empresa (left), título/subtítulo below ---------- */
        .header { display: table; width: 100%; margin-bottom: 18px; }
        .header .empresa { display: table-cell; vertical-align: middle; }
        .header .logo { display: table-cell; vertical-align: middle; width: 64px; text-align: right; }
        .header .logo img { max-width: 56px; max-height: 56px; }
        .empresa-nome { font-size: 15px; font-weight: bold; margin: 0 0 2px 0; color: #14171d; }
        .empresa-email { font-size: 10px; color: #6c7484; margin: 0; }

        .titulo-bloco {
            border-top: 1px solid #e5e8ef;
            border-bottom: 1px solid #e5e8ef;
            padding: 12px 0;
            margin-bottom: 18px;
            display: table;
            width: 100%;
        }
        .titulo-bloco .principal { display: table-cell; vertical-align: bottom; }
        .titulo-bloco .titulo { font-size: 17px; font-weight: bold; color: #14171d; display: block; }
        .titulo-bloco .subtitulo { font-size: 10.5px; color: #6c7484; margin-top: 2px; display: block; }
        .titulo-bloco .gerado { display: table-cell; text-align: right; vertical-align: bottom; font-size: 9.5px; color: #9aa2b4; white-space: nowrap; }

        /* ---------- Resumo tiles (Finanças only) ---------- */
        .resumo { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 0 18px -6px; }
        .resumo td {
            background: #f8f9fb;
            border: 1px solid #e5e8ef;
            border-radius: 6px;
            padding: 9px 12px;
            width: 1%;
            white-space: nowrap;
        }
        .resumo .rotulo { display: block; font-size: 8.5px; text-transform: uppercase; letter-spacing: .04em; color: #6c7484; margin-bottom: 3px; }
        .resumo .valor { display: block; font-size: 13px; font-weight: bold; color: #14171d; }

        /* ---------- Data table ---------- */
        table.dados { width: 100%; border-collapse: collapse; }
        table.dados thead th {
            background: #eef1fe;
            color: #1f2c8c;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .03em;
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1px solid #bcc7fb;
        }
        table.dados thead th.numerica { text-align: right; }
        table.dados tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #f1f3f7;
            font-size: 10px;
            color: #343a47;
        }
        table.dados tbody td.numerica { text-align: right; font-weight: bold; color: #14171d; }
        table.dados tbody tr.par td { background: #fcfcfd; }
        table.dados tbody tr.vazio td { text-align: center; color: #9aa2b4; padding: 22px 0; font-style: italic; }

        /* ---------- Footer (fixed, repeats on every page) ---------- */
        .rodape {
            position: fixed;
            left: 0; right: 0; bottom: -42px;
            height: 30px;
            padding: 8px 40px 0;
            border-top: 1px solid #f1f3f7;
            display: table;
            width: 100%;
        }
        .rodape .marca { display: table-cell; font-size: 8.5px; color: #9aa2b4; }
        .rodape .pagina { display: table-cell; text-align: right; font-size: 8.5px; color: #9aa2b4; }
    </style>
</head>
<body>
    <div class="header">
        <div class="empresa">
            <p class="empresa-nome">{{ $empresa['nome'] ?? config('app.name') }}</p>
            @if(!empty($empresa['email']))
                <p class="empresa-email">{{ $empresa['email'] }}</p>
            @endif
        </div>
        @if($logoDataUri)
            <div class="logo">
                <img src="{{ $logoDataUri }}" alt="Logótipo">
            </div>
        @endif
    </div>

    <div class="titulo-bloco">
        <div class="principal">
            <span class="titulo">{{ $titulo }}</span>
            @if($subtitulo !== '')
                <span class="subtitulo">{{ $subtitulo }}</span>
            @endif
        </div>
        <div class="gerado">Gerado em {{ $geradoEm->format('d/m/Y') }} às {{ $geradoEm->format('H:i') }}</div>
    </div>

    @if(!empty($resumo))
        <table class="resumo">
            <tr>
                @foreach($resumo as $rotulo => $valor)
                    <td>
                        <span class="rotulo">{{ $rotulo }}</span>
                        <span class="valor">{{ $valor }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <table class="dados">
        <thead>
            <tr>
                @foreach($colunas as $i => $coluna)
                    <th @class(['numerica' => in_array($i, $colunasNumericas, true)])>{{ $coluna }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($linhas as $index => $linha)
                <tr @class(['par' => $index % 2 === 1])>
                    @foreach($linha as $i => $celula)
                        <td @class(['numerica' => in_array($i, $colunasNumericas, true)])>{{ $celula }}</td>
                    @endforeach
                </tr>
            @empty
                <tr class="vazio">
                    <td colspan="{{ count($colunas) }}">Sem registos para este relatório.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="rodape">
        <div class="marca">Vencia</div>
        <div class="pagina">
            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->getFont('Helvetica', 'normal');
                    $size = 8.5;
                    $texto = "Página {$PAGE_NUM} de {$PAGE_COUNT}";
                    $largura = $fontMetrics->getTextWidth($texto, $font, $size);
                    $pdf->page_text(555 - $largura, 805, $texto, $font, $size, array(0.60, 0.63, 0.67));
                }
            </script>
        </div>
    </div>
</body>
</html>
