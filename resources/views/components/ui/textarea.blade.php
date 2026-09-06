@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'rows' => 3,
    'id' => null,
])
@php
    $uid = $id ?? 'ta-'.uniqid();
    $textareaClass = 'field__textarea'.($error ? ' field__textarea--error' : '');
@endphp
<div class="field">
    @if($label)<label for="{{ $uid }}" class="field__label">{{ $label }}</label>@endif
    <textarea id="{{ $uid }}" rows="{{ $rows }}" {{ $attributes->class([$textareaClass]) }}>{{ $slot }}</textarea>
    @if($error || $hint)
        <span class="field__hint {{ $error ? 'field__hint--error' : '' }}">{{ $error ?: $hint }}</span>
    @endif
</div>
