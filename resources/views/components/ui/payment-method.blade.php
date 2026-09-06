<span {{ $attributes->class(['payment-method']) }}>
    <span class="payment-method__badge" style="width:{{ $size }}px;height:{{ $size }}px;background:{{ $bg }}">
        @if($logo)
            <img src="{{ $assetsBase.$logo }}" alt="{{ $label }}" class="payment-method__img" />
        @else
            <x-ui.icon :name="$icon" :size="(int) round($size * 0.62)" :color="$fg" />
        @endif
    </span>
    @if($showLabel)
        <span class="payment-method__label">{{ $label }}</span>
    @endif
</span>
