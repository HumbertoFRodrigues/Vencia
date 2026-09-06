@props([
    'placeholder' => 'Pesquisar cliente, serviço, domínio…',
    'shortcut' => '/',
    'name' => 'q',
    'width' => 380,
    'style' => null,
])
{{-- `width` and `style` are dedicated props, matching SearchInput.jsx's
     `width`/`style` (both applied to the wrapper) + `...rest` (spread onto
     the `<input>`) contract — so plain attributes such as wire:model or
     class land on the actual <input> below, while callers that only need to
     nudge the wrapper's position (e.g. TopBar's margin-left) keep working. --}}
<div class="search-input" style="width:{{ is_numeric($width) ? $width.'px' : $width }}{{ $style ? ';'.$style : '' }}">
    <x-ui.icon name="search" size="15" color="var(--text-faint)" />
    <input type="text" name="{{ $name }}" placeholder="{{ $placeholder }}" {{ $attributes->class(['search-input__input']) }} />
    @if($shortcut)<kbd class="search-input__kbd">{{ $shortcut }}</kbd>@endif
</div>
