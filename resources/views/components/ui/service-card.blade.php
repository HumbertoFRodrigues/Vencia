@props([
    'name' => '',
    'client' => null,
    'category' => 'outro',
    'logoSrc' => null,
    'description' => null,
    'amount' => 0,
    'currency' => 'MZN',
    'period' => null,
    'dueLabel' => null,
    'status' => 'activo',
    'statusLabel' => null,
    'action' => 'Ver serviço',
    'href' => null,
])
{{-- Ported from components/data/ServiceCard.jsx. Hover raises the card to
     --shadow-raised (was React onMouseEnter/Leave state, now plain CSS
     :hover — no JS needed). `href` is optional: pass it once Serviço
     detalhe (Fase 4) exists; omitted, the action renders disabled instead
     of linking to a route that doesn't exist yet. --}}
<div {{ $attributes->class(['service-card']) }}>
    <div class="service-card__head">
        <x-ui.service-logo :name="$name" :category="$category" :src="$logoSrc" size="40" />
        <div class="service-card__title-wrap">
            <strong class="service-card__name">{{ $name }}</strong>
            @if($client)<span class="service-card__client">{{ $client }}</span>@endif
        </div>
    </div>
    <div class="service-card__body">
        @if($description)<p class="service-card__desc">{{ $description }}</p>@endif
        <x-ui.money-value :amount="$amount" :currency="$currency" :period="$period" size="lg" />
        @if($dueLabel)<span class="service-card__due">{{ $dueLabel }}</span>@endif
    </div>
    <div class="service-card__foot">
        <x-ui.status-badge :status="$status" :label="$statusLabel" size="sm" />
        @if($href)
            <a href="{{ $href }}" class="btn btn--sm btn--ghost">
                {{ $action }}<x-ui.icon name="arrow-right" size="15" class="btn__icon" />
            </a>
        @else
            <x-ui.button size="sm" variant="ghost" iconEnd="arrow-right" disabled>{{ $action }}</x-ui.button>
        @endif
    </div>
</div>
