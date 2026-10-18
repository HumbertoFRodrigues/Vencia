{{-- Google AdSense (auto ads), only when switched on in Admin → Plataforma. --}}
@php
    $anuncioCliente = \App\Support\Anuncios::cliente();
@endphp
@if($anuncioCliente)
    <meta name="google-adsense-account" content="{{ $anuncioCliente }}">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $anuncioCliente }}" crossorigin="anonymous"></script>
@endif
