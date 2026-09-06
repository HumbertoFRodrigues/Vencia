@if($url)
    <img
        src="{{ $url }}"
        alt="{{ $name }}"
        title="{{ $name }}"
        data-logo
        {{ $attributes->class(['service-logo-img', $size < 40 ? 'service-logo-img--sm' : null]) }}
        style="width:{{ $size }}px;height:{{ $size }}px;padding:{{ (int) round($size * 0.12) }}px"
    />
@else
    <span
        title="{{ $name }}"
        {{ $attributes->class(['service-logo-mono', $size < 40 ? 'service-logo-mono--sm' : null]) }}
        style="width:{{ $size }}px;height:{{ $size }}px;background:var({{ $bgVar }});color:var({{ $fgVar }});font-size:{{ (int) round($size * 0.36) }}px"
    >
        @if($initials !== '')
            {{ $initials }}
            <span class="service-logo-mono__badge">
                <x-ui.icon :name="$categoryIcon" :size="max(9, (int) round($size * 0.26))" :color="'var('.$fgVar.')'" />
            </span>
        @else
            <x-ui.icon :name="$categoryIcon" :size="(int) round($size * 0.5)" />
        @endif
    </span>
@endif
