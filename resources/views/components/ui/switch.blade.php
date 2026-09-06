@props([
    'label' => null,
    'checked' => false,
    'disabled' => false,
    'id' => null,
])
@php
    $uid = $id ?? 'sw-'.uniqid();
    $labelClass = 'switch'.($disabled ? ' switch--disabled' : '');
@endphp
<label for="{{ $uid }}" class="{{ $labelClass }}">
    <input id="{{ $uid }}" type="checkbox" @checked($checked) @disabled($disabled) {{ $attributes->class(['switch__input']) }} />
    <span class="switch__track"><span class="switch__thumb"></span></span>
    @if($label)<span class="switch__label">{{ $label }}</span>@endif
</label>
