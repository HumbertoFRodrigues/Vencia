@props([
    'title' => null,
    'description' => null,
    'width' => 460,
])
{{-- Ported from components/feedback/Dialog.jsx. The parent view controls
     visibility (wrap the whole component in @if/@livewire visibility state)
     and provides the close affordance via the `close` slot, e.g.
     <x-slot:close><x-ui.icon-button icon="x" label="Fechar" wire:click="close" /></x-slot:close> --}}
<div {{ $attributes->class(['dialog-scrim']) }} onclick="if (event.target === this) { this.dispatchEvent(new CustomEvent('dialog:backdrop', { bubbles: true })); }">
    <div class="dialog-panel" style="width:{{ $width }}px" onclick="event.stopPropagation()">
        <div class="dialog-panel__head">
            <div class="dialog-panel__head-text">
                @if($title)<h3 class="dialog-panel__title">{{ $title }}</h3>@endif
                @if($description)<p class="dialog-panel__desc">{{ $description }}</p>@endif
            </div>
            {{ $close ?? '' }}
        </div>
        <div class="dialog-panel__body">{{ $slot }}</div>
        @if(isset($footer) && trim($footer))
            <div class="dialog-panel__footer">{{ $footer }}</div>
        @endif
    </div>
</div>
