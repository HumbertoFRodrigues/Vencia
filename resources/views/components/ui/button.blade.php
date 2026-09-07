@php
    $wireClick = $attributes->wire('click')->value();
@endphp
<button
    type="{{ $type }}"
    @disabled($disabled)
    @if($wireClick)
        wire:loading.attr="disabled"
        wire:target="{{ $wireClick }}"
    @endif
    {{ $attributes->class(['btn', "btn--{$variant}", "btn--{$size}", $fullWidth ? 'btn--full-width' : null]) }}
>
    @if($icon)
        <x-ui.icon :name="$icon" :size="$iconSize" class="btn__icon" />
    @endif
    {{ $slot }}
    @if($iconEnd)
        <x-ui.icon :name="$iconEnd" :size="$iconSize" class="btn__icon" />
    @endif
</button>
