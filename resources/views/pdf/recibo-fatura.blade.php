<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <title>Recibo {{ $fatura->referenciaInterna() }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #14171d;
            margin: 0;
            padding: 36px 40px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 28px;
        }
        .header .empresa {
            display: table-cell;
            vertical-align: middle;
        }
        .header .logo {
            display: table-cell;
            vertical-align: middle;
            width: 64px;
            text-align: right;
        }
        .header .logo img {
            max-width: 56px;
            max-height: 56px;
        }
        .empresa-nome {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 2px 0;
        }
        .empresa-email {
            font-size: 11px;
            color: #5a5f6b;
            margin: 0;
        }
        .titulo-bloco {
            border-top: 1px solid #d8dbe0;
            border-bottom: 1px solid #d8dbe0;
            padding: 14px 0;
            margin-bottom: 24px;
            display: table;
            width: 100%;
        }
        .titulo-bloco .titulo {
            display: table-cell;
            font-size: 20px;
            font-weight: bold;
        }
        .titulo-bloco .referencia {
            display: table-cell;
            text-align: right;
            font-size: 12px;
            color: #5a5f6b;
            vertical-align: bottom;
        }
        table.detalhes {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.detalhes td {
            padding: 9px 0;
            border-bottom: 1px solid #eceef1;
            font-size: 12px;
        }
        table.detalhes td.label {
            color: #5a5f6b;
            width: 42%;
        }
        table.detalhes td.valor {
            text-align: right;
            font-weight: bold;
        }
        .valor-bloco {
            border: 1px solid #d8dbe0;
            border-radius: 4px;
            padding: 16px 18px;
            margin-bottom: 28px;
            display: table;
            width: 100%;
        }
        .valor-bloco .rotulo {
            display: table-cell;
            font-size: 13px;
            vertical-align: middle;
        }
        .valor-bloco .montante {
            display: table-cell;
            text-align: right;
            font-size: 22px;
            font-weight: bold;
            vertical-align: middle;
        }
        .fecho {
            font-size: 11px;
            color: #5a5f6b;
            line-height: 1.6;
        }
        .rodape {
            margin-top: 40px;
            padding-top: 14px;
            border-top: 1px solid #eceef1;
            font-size: 10px;
            color: #9aa0ab;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="empresa">
            <p class="empresa-nome">{{ $emissor['nome'] ?? config('app.name') }}</p>
            @if(!empty($emissor['nif']))<p class="empresa-email">NUIT {{ $emissor['nif'] }}</p>@endif
            @if(!empty($emissor['email']))<p class="empresa-email">{{ $emissor['email'] }}</p>@endif
        </div>
        @if($logoDataUri)
            <div class="logo"><img src="{{ $logoDataUri }}" alt="Logótipo"></div>
        @endif
    </div>

    <div class="titulo-bloco">
        <span class="titulo">Recibo</span>
        <span class="referencia">Fatura {{ $fatura->referenciaInterna() }}</span>
    </div>

    <table class="detalhes">
        <tr><td class="label">Cliente</td><td class="valor">{{ $fatura->conta->nome }}</td></tr>
        <tr><td class="label">Plano</td><td class="valor">Vencia {{ $fatura->plano->nome }} ({{ \App\Services\Faturacao::CICLOS[$fatura->ciclo] ?? $fatura->ciclo }})</td></tr>
        <tr><td class="label">Período</td><td class="valor">{{ $fatura->periodo_inicio?->format('d/m/Y') }} a {{ $fatura->periodo_fim?->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Método de pagamento</td><td class="valor">{{ $fatura->metodoLabel() ?? $fatura->gateway }}{{ $fatura->referencia ? ' · '.$fatura->referencia : '' }}</td></tr>
        <tr><td class="label">Data do pagamento</td><td class="valor">{{ $fatura->paga_em?->format('d/m/Y') }}</td></tr>
    </table>

    <div class="valor-bloco">
        <span class="rotulo">Valor pago</span>
        <span class="montante">{{ $valor }}</span>
    </div>

    <p class="fecho">Este recibo confirma o pagamento da subscrição da Vencia acima descrita. Obrigado.</p>

    <div class="rodape">Recibo gerado em {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>