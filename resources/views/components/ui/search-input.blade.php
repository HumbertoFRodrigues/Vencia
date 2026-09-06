@props([
    'placeholder' => 'Pesquisar cliente, serviço, domínio…',
    'shortcut' => '/',
    'name' => 'q',
])
<div {{ $attributes->class(['search-input']) }}>
    <x-ui.icon name="search" size="15" color="var(--text-faint)" />
    <input type="text" name="{{ $name }}" placeholder="{{ $placeholder }}" class="search-input__input" />
    @if($shortcut)<kbd class="search-input__kbd">{{ $shortcut }}</kbd>@endif
</div>
