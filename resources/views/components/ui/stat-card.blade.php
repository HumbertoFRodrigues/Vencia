@props([
    'label' => null,
    'value' => null,
    'currency' => null,
    'period' => null,
    'delta' => null,
    'deltaTone' => 'neutral',
    'icon' => null,
    'tone' => 'neutral',
    'footnote' => null,
    'clickable' => false,
])
<div {{ $attributes->class(['stat-card', $clickable ? 'stat-card--clickable' : null]) }}>
    <div class="stat-card__head">
        <span class="stat-card__label">{{ $label }}</span>
        @if($icon)<x-ui.icon :name="$icon" size="15" color="var(--text-faint)" />@endif
    </div>
    @if($currency)
        <x-ui.money-value :amount="$value" :currency="$currency" :period="$period" size="metric" :tone="in_array($tone, ['in', 'out'], true) ? $tone : 'neutral'" />
    @else
        <span class="stat-card__value stat-card__value--{{ $tone }}">{{ $value }}</span>
    @endif
    @if($delta || $footnote)
        <div class="stat-card__foot">
            @if($delta)<span class="stat-card__delta stat-card__delta--{{ $deltaTone }}">{{ $delta }}</span>@endif
            {{ $footnote }}
        </div>
    @endif
</div>
