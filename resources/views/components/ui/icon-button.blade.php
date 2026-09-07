@php
    $wireClick = $attributes->wire('click')->value();
@endphp
<button
    type="{{ $type }}"
    aria-label="{{ $label }}"
    title="{{ $label }}"
    @disabled($disabled)
    @if($wireClick)
        wire:loading.attr="disabled"
        wire:target="{{ $wireClick }}"
    @endif
    {{ $attributes->class(['icon-btn', "icon-btn--{$variant}", "icon-btn--{$size}"]) }}
>
    <x-ui.icon :name="$icon" :size="$iconSize" />
</button>
