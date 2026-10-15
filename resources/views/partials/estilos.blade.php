{{-- Every stylesheet linked directly with an mtime version, so a deploy is
     picked up immediately instead of a browser keeping a stale cached copy. --}}
@php
    $folhas = ['tokens/fonts', 'tokens/colors', 'tokens/typography', 'tokens/spacing', 'tokens/elevation', 'tokens/motion', 'tokens/base', 'components', 'pessoal', 'saas', 'site'];
@endphp
<link rel="icon" type="image/png" href="{{ asset('assets/app-192.png') }}">
<link rel="apple-touch-icon" href="{{ asset('assets/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#3348d8">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Vencia">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
@foreach($folhas as $folha)
    <link rel="stylesheet" href="{{ asset("css/{$folha}.css") }}?v={{ @filemtime(public_path("css/{$folha}.css")) }}">
@endforeach
