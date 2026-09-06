@php
    $extraStyle = $attributes->get('style');
    $baseStyle = "width:{$size}px;height:{$size}px;background:{$color};-webkit-mask-image:url('{$url}');mask-image:url('{$url}')";
    $fullStyle = $extraStyle ? $baseStyle.';'.$extraStyle : $baseStyle;
@endphp
<span aria-hidden="true" {{ $attributes->except('style')->class(['icon']) }} style="{{ $fullStyle }}"></span>
