@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'icon' => null,
    'suffix' => null,
    'size' => 'md',
    'id' => null,
])
@php
    $uid = $id ?? 'in-'.uniqid();
    $controlClass = 'field__control'.($size === 'sm' ? ' field__control--sm' : ($size === 'lg' ? ' field__control--lg' : '')).($error ? ' field__control--error' : '');
@endphp
<div class="field">
    @if($label)<label for="{{ $uid }}" class="field__label">{{ $label }}</label>@endif
    <div class="{{ $controlClass }}">
        @if($icon)<x-ui.icon :name="$icon" size="15" color="var(--text-faint)" />@endif
        <input id="{{ $uid }}" {{ $attributes->class(['field__input']) }} />
        @if($suffix)<span class="field__suffix">{{ $suffix }}</span>@endif
    </div>
    @if($error || $hint)
        <span class="field__hint {{ $error ? 'field__hint--error' : '' }}">{{ $error ?: $hint }}</span>
    @endif
</div>
