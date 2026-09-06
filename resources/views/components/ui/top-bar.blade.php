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
        <span class="top-bar__avatar"><x-ui.icon name="circle-user-round" size="20" /></span>
    </div>
</header>
