@props([
    'label' => null,
    'description' => null,
    'checked' => false,
    'disabled' => false,
    'id' => null,
])
@php
    $uid = $id ?? 'cb-'.uniqid();
    $labelClass = 'checkbox'.($description ? ' checkbox--with-description' : '').($disabled ? ' checkbox--disabled' : '');
@endphp
<label for="{{ $uid }}" class="{{ $labelClass }}">
    <input id="{{ $uid }}" type="checkbox" @checked($checked) @disabled($disabled) {{ $attributes->class(['checkbox__input']) }} />
    <span class="checkbox__box">
        <span class="checkbox__check"><x-ui.icon name="check" size="12" color="var(--on-accent)" /></span>
    </span>
    @if($label)
        <span class="checkbox__text">
            <span class="checkbox__label">{{ $label }}</span>
            @if($description)<span class="checkbox__description">{{ $description }}</span>@endif
        </span>
    @endif
</label>
