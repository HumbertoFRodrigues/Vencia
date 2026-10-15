@extends('layouts.auth')

@section('titulo', 'Verificação em dois passos')
@section('subtitulo', 'Abre a app autenticadora no telemóvel e escreve o código de 6 dígitos da Vencia.')

@section('conteudo')
    <form method="POST" action="{{ route('login.dois-passos.store') }}" class="auth-card__form">
        @csrf
        <x-ui.input name="codigo" label="Código" inputmode="numeric" autocomplete="one-time-code" placeholder="123 456" required autofocus size="lg" />
        <x-ui.button type="submit" variant="primary" size="lg" :full-width="true">Confirmar</x-ui.button>
    </form>

    <p class="auth-card__footnote">Sem o telemóvel? Usa um dos códigos de recuperação que guardaste.<br><a href="{{ route('login') }}">← Voltar</a></p>
@endsection
