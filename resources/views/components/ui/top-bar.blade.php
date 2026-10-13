@props([
    'title' => null,
    'search' => true,
    'themeToggle' => true,
    'bell' => true,
])
<header {{ $attributes->class(['top-bar']) }}>
    <button type="button" class="icon-btn icon-btn--ghost icon-btn--md top-bar__menu" data-nav-toggle aria-label="Esconder menu" title="Esconder menu" aria-controls="menu-principal" aria-expanded="true">
        <x-ui.icon name="menu" size="18" />
    </button>
    @if($title)<strong class="top-bar__title">{{ $title }}</strong>@endif
    @if($search)
        <div class="top-bar__search" @if($title) style="margin-left:8px" @endif>
            <livewire:shared.global-search />
        </div>
    @endif
    <div class="top-bar__actions">
        {{ $actions ?? '' }}
        @if($themeToggle)<x-ui.theme-toggle />@endif
        @if($bell)<livewire:shared.notification-bell />@endif
        <div class="dropdown">
            <button type="button" class="top-bar__avatar" data-dropdown-toggle data-dropdown-group="profile" aria-haspopup="true" aria-expanded="false" aria-label="Perfil">
                <x-ui.icon name="circle-user-round" size="20" />
            </button>
            <div class="dropdown-panel" data-dropdown-panel data-dropdown-group="profile" hidden>
                <div class="top-bar__profile-info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->email }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-menu-item dropdown-menu-item--danger">
                        <x-ui.icon name="log-out" size="16" />
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
