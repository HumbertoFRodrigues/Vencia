@props([
    'title' => null,
    'search' => true,
    'alertCount' => null,
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
        <div class="top-bar__bell-wrap">
            <x-ui.icon-button icon="bell" label="Notificações" />
            @if($alertCount)<span class="top-bar__badge">{{ $alertCount }}</span>@endif
        </div>
        <div class="top-bar__profile" data-profile-menu>
            <button type="button" class="top-bar__avatar" data-profile-toggle aria-haspopup="true" aria-expanded="false" aria-label="Perfil">
                <x-ui.icon name="circle-user-round" size="20" />
            </button>
            <div class="top-bar__profile-panel" data-profile-panel hidden>
                <div class="top-bar__profile-info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->email }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="top-bar__profile-logout">
                        <x-ui.icon name="log-out" size="16" />
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
