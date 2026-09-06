@props([
    'eyebrow' => null,
    'title' => null,
    'meta' => null,
    'back' => null,
    'backHref' => '#',
])
<div {{ $attributes->class(['page-header']) }}>
    <div class="page-header__main">
        @if($back)
            <a href="{{ $backHref }}" class="page-header__back">
                <x-ui.icon name="arrow-left" size="14" />{{ $back }}
            </a>
        @elseif($eyebrow)
            <span class="page-header__eyebrow">{{ $eyebrow }}</span>
        @endif
        <h1 class="page-header__title">{{ $title }}</h1>
        @if($meta)<div class="page-header__meta">{{ $meta }}</div>@endif
    </div>
    @if(isset($actions) && trim($actions))
        <div class="page-header__actions">{{ $actions }}</div>
    @endif
</div>
