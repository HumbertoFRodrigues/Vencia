{{-- Every stylesheet linked directly with an mtime version, so a deploy is
     picked up immediately instead of a browser keeping a stale cached copy. --}}
@php
    $folhas = ['tokens/fonts', 'tokens/colors', 'tokens/typography', 'tokens/spacing', 'tokens/elevation', 'tokens/motion', 'tokens/base', 'components', 'pessoal'];
@endphp
<link rel="icon" type="image/png" href="{{ asset('assets/logo-mark.png') }}">
<link rel="apple-touch-icon" href="{{ asset('assets/logo-mark.png') }}">
<meta name="theme-color" content="#3348d8">
@foreach($folhas as $folha)
    <link rel="stylesheet" href="{{ asset("css/{$folha}.css") }}?v={{ @filemtime(public_path("css/{$folha}.css")) }}">
@endforeach
