@props([
    'items' => [],
    'active' => null,
    'brand' => 'Vencia',
    'logoSrc' => '/assets/logo-mark.png',
])
{{-- Collapse state lives as data-sidebar-collapsed on <html> (like data-theme
     for the theme toggle), not a class here — <html> sits outside what
     wire:navigate morphs, so the collapsed state survives page-to-page
     navigation without needing to re-run any init script per page. Which
     icon/label shows is pure CSS keyed off that attribute (see .sidebar-nav
     rules in components.css), same technique as ThemeToggle. --}}
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
            <a href="{{ $href }}" wire:navigate class="sidebar-nav__item {{ $isActive ? 'sidebar-nav__item--active' : '' }}" title="{{ $it['label'] }}">
                <x-ui.icon :name="$it['icon']" size="16" :color="$isActive ? 'var(--accent)' : 'var(--text-muted)'" />
                <span class="sidebar-nav__label">{{ $it['label'] }}</span>
                @if(!empty($it['badge']))<span class="sidebar-nav__badge">{{ $it['badge'] }}</span>@endif
            </a>
        @endforeach
    </div>
    <div class="sidebar-nav__bottom">
        @if(isset($footer) && trim($footer))
            <div class="sidebar-nav__footer"><span class="sidebar-nav__footer-dot"></span><span class="sidebar-nav__footer-text">{{ $footer }}</span></div>
        @endif
        <button type="button" class="sidebar-nav__toggle" data-sidebar-toggle title="Ocultar/expandir menu">
            <x-ui.icon name="panel-left-close" size="15" class="sidebar-nav__toggle-icon--expanded" />
            <x-ui.icon name="panel-left-open" size="15" class="sidebar-nav__toggle-icon--collapsed" />
            <span class="sidebar-nav__toggle-label">Ocultar menu</span>
        </button>
    </div>
</nav>
