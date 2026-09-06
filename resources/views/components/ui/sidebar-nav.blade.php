@props([
    'items' => [],
    'active' => null,
    'brand' => 'Vencia',
    'logoSrc' => '/assets/logo-mark.png',
])
<nav {{ $attributes->class(['sidebar-nav']) }}>
    <div class="sidebar-nav__brand">
        <img src="{{ $logoSrc }}" alt="" class="sidebar-nav__logo" />
        <span class="sidebar-nav__brand-name">{{ $brand }}</span>
    </div>
    <div class="sidebar-nav__list">
        @foreach($items as $it)
            @php
                $isActive = ($it['id'] ?? null) === $active;
                $href = $it['href'] ?? '#';
            @endphp
            <a href="{{ $href }}" wire:navigate class="sidebar-nav__item {{ $isActive ? 'sidebar-nav__item--active' : '' }}">
                <x-ui.icon :name="$it['icon']" size="16" :color="$isActive ? 'var(--accent)' : 'var(--text-muted)'" />
                <span class="sidebar-nav__label">{{ $it['label'] }}</span>
                @if(!empty($it['badge']))<span class="sidebar-nav__badge">{{ $it['badge'] }}</span>@endif
            </a>
        @endforeach
    </div>
    @if(isset($footer) && trim($footer))
        <div class="sidebar-nav__footer"><span class="sidebar-nav__footer-dot"></span>{{ $footer }}</div>
    @endif
</nav>
