@props([
    'amount' => 0,
    'currency' => null,
    'period' => null,
    'size' => 'md',
    'tone' => 'neutral',
])
@php
    $currency ??= \App\Models\Configuracao::moeda();
    $amt = (float) $amount;
    $neg = $amt < 0;
    $abs = abs($amt);
    $int = (int) floor($abs + 1e-9);
    $frac = (int) round(($abs - $int) * 100);
    $grouped = number_format($int, 0, '', '.');
    $formatted = ($neg ? '-' : '').$grouped.($frac ? ','.str_pad((string) $frac, 2, '0', STR_PAD_LEFT) : '');
@endphp
<span {{ $attributes->class(['money-value', "money-value--{$size}", "money-value--{$tone}"]) }}>
    {{ $formatted }}<span class="money-value__currency">{{ $currency }}{{ $period ? ' / '.$period : '' }}</span>
</span>
