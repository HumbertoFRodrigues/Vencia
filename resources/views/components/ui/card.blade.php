@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 16,
    'interactive' => false,
])
<section {{ $attributes->class(['card', $interactive ? 'card--interactive' : null]) }}>
    @if($title || (isset($action) && trim($action)))
        <header class="card__header">
            <div class="card__header-text">
                @if($title)<h3 class="card__title">{{ $title }}</h3>@endif
                @if($subtitle)<span class="card__subtitle">{{ $subtitle }}</span>@endif
            </div>
            {{ $action ?? '' }}
        </header>
    @endif
    <div class="card__body {{ (int) $padding === 0 ? 'card__body--flush' : '' }}" @if((int) $padding !== 0 && (int) $padding !== 16) style="padding:{{ (int) $padding }}px" @endif>
        {{ $slot }}
    </div>
</section>
