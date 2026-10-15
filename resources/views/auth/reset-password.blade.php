@extends('layouts.auth')

@section('titulo', 'Nova palavra-passe')
@section('subtitulo', 'Escolhe uma palavra-passe nova para a tua conta.')

@section('conteudo')
    <form method="POST" action="{{ route('password.update') }}" class="auth-card__form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-ui.input type="email" name="email" label="Email" value="{{ old('email', $email) }}" autocomplete="email" required size="lg" />
        <x-ui.input type="password" name="password" label="Nova palavra-passe" autocomplete="new-password" hint="Pelo menos 8 caracteres." required autofocus size="lg" />
        <x-ui.input type="password" name="password_confirmation" label="Repetir a palavra-passe" autocomplete="new-password" required size="lg" />
        <x-ui.button type="submit" variant="primary" size="lg" :full-width="true">Guardar e entrar</x-ui.button>
    </form>
@endsection
