@extends('layouts.auth')

@section('titulo', 'Bem-vindo de volta')
@section('subtitulo', 'Entra na tua conta para continuar.')

@section('conteudo')
    <form method="POST" action="{{ route('login.store') }}" class="auth-card__form">
        @csrf
        <x-ui.input type="email" name="email" label="Email" value="{{ old('email') }}" placeholder="o-teu@email.co.mz" autocomplete="email" required autofocus size="lg" />
        <div class="acesso__campo-com-link">
            <a href="{{ route('password.request') }}">Esqueceste-te?</a>
            <x-ui.input type="password" name="password" label="Palavra-passe" autocomplete="current-password" required size="lg" />
        </div>
        <x-ui.button type="submit" variant="primary" size="lg" :full-width="true">Entrar</x-ui.button>
    </form>

    @if(\App\Http\Controllers\Auth\RegistoController::opcoes()['aberto'])
        <div class="acesso__separador"><span>Ainda não tens conta?</span></div>
        <a class="btn btn--secondary btn--lg btn--full-width" href="{{ route('registar') }}">Criar conta grátis</a>
    @endif
@endsection
