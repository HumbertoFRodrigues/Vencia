@props([
    'title' => null,
    'search' => true,
    'themeToggle' => true,
])
<header {{ $attributes->class(['top-bar']) }}>
    @if($title)<strong class="top-bar__title">{{ $title }}</strong>@endif
    @if($search)
        <div style="{{ $title ? 'margin-left:8px' : '' }}flex:0 0 auto">
            <livewire:shared.global-search />
        </div>
    @endif
    <div class="top-bar__actions">
        {{ $actions ?? '' }}
        @if($themeToggle)<x-ui.theme-toggle />@endif
        <livewire:shared.notification-bell />
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
