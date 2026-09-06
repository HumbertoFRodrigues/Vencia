<span {{ $attributes->class(['tag', "tag--{$tone}"]) }}>
    @if($icon)
        <x-ui.icon :name="$icon" size="12" />
    @endif
    {{ $slot }}
</span>
