@props([
    'src' => null,
    'size' => 64,
    'label' => 'Adicionar logo personalizado',
    'hint' => 'PNG, JPG, SVG ou WebP',
])
<div {{ $attributes->class(['logo-slot']) }}>
    <button
        type="button"
        class="logo-slot__btn {{ $src ? 'logo-slot__btn--filled' : '' }}"
        style="width:{{ $size }}px;height:{{ $size }}px;padding:{{ $src ? (int) round($size * 0.1) : 0 }}px"
    >
        @if($src)
            <img src="{{ $src }}" alt="" class="logo-slot__img" />
        @else
            <x-ui.icon name="image-plus" :size="(int) round($size * 0.32)" />
        @endif
    </button>
    <div class="logo-slot__text">
        <span class="logo-slot__label">{{ $label }}</span>
        <span class="logo-slot__hint">{{ $hint }}</span>
    </div>
</div>
