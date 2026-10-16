@extends('layouts.auth')

@section('titulo', 'Recuperar a palavra-passe')
@section('subtitulo', 'Indica o teu email e enviamos uma ligação para definires uma nova.')

@section('conteudo')
    <form method="POST" action="{{ route('password.email') }}" class="auth-card__form">
        @csrf
        <x-ui.input type="email" name="email" label="Email" value="{{ old('email') }}" placeholder="o-teu@email.co.mz" autocomplete="email" required autofocus size="lg" />
        <x-ui.button type="submit" variant="primary" size="lg" :full-width="true">Enviar ligação</x-ui.button>
    </form>

    <p class="auth-card__footnote"><a href="{{ route('login') }}">← Voltar a iniciar sessão</a></p>
@endsection
