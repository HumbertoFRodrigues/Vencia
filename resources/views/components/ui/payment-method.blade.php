<span {{ $attributes->class(['payment-method']) }}>
    <span class="payment-method__badge" style="width:{{ $size }}px;height:{{ $size }}px;background:{{ $bg }}">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $label }}" class="payment-method__img" />
        @elseif($logo)
            <img src="{{ $assetsBase.$logo }}" alt="{{ $label }}" class="payment-method__img" />
        @else
            <x-ui.icon :name="$icon" :size="(int) round($size * 0.62)" :color="$fg" />
        @endif
    </span>
    @if($showLabel)
        <span class="payment-method__label">{{ $label }}</span>
    @endif
</span>
