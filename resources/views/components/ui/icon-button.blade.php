<button
    type="{{ $type }}"
    aria-label="{{ $label }}"
    title="{{ $label }}"
    @disabled($disabled)
    {{ $attributes->class(['icon-btn', "icon-btn--{$variant}", "icon-btn--{$size}"]) }}
>
    <x-ui.icon :name="$icon" :size="$iconSize" />
</button>
