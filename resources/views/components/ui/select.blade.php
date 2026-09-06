@props([
    'label' => null,
    'hint' => null,
    'options' => [],
    'id' => null,
])
@php
    $uid = $id ?? 'se-'.uniqid();
@endphp
<div class="field">
    @if($label)<label for="{{ $uid }}" class="field__label">{{ $label }}</label>@endif
    <div class="field__select-wrap">
        <select id="{{ $uid }}" {{ $attributes->class(['field__select']) }}>
            @foreach($options as $o)
                @php
                    $value = is_string($o) ? $o : ($o['value'] ?? $o['label'] ?? '');
                    $optLabel = is_string($o) ? $o : ($o['label'] ?? $value);
                @endphp
                <option value="{{ $value }}">{{ $optLabel }}</option>
            @endforeach
        </select>
        <x-ui.icon name="chevron-down" size="15" color="var(--text-faint)" class="field__select-chevron" />
    </div>
    @if($hint)<span class="field__hint">{{ $hint }}</span>@endif
</div>
