@extends('layouts.auth')

@if(! $convite)
    @section('titulo', 'Convite inválido')
    @section('subtitulo', 'Este convite já expirou ou já foi usado. Pede um novo a quem te convidou.')
    @section('conteudo')
        <p class="auth-card__footnote"><a href="{{ route('inicio') }}">Ir para a página inicial</a></p>
    @endsection
@else
    @section('titulo', 'Convite para '.$convite->conta->nome)
    @section('subtitulo')
        {{ $convite->convidadoPor?->name ?? 'Alguém' }} convidou {{ $convite->email }} para a equipa, com o papel <strong>{{ $convite->papel->label() }}</strong>: {{ mb_lcfirst($convite->papel->descricao()) }}
    @endsection
    @section('conteudo')
        @if($user)
            @if(strcasecmp($user->email, $convite->email) === 0)
                <form method="POST" action="{{ route('convite.aceitar', $token) }}" class="auth-card__form">
                    @csrf
                    <x-ui.button type="submit" variant="primary" :full-width="true">Aceitar e entrar na equipa</x-ui.button>
                </form>
            @else
                <x-ui.alert-banner tone="info">Tens sessão iniciada como {{ $user->email }}. Este convite é para {{ $convite->email }}.</x-ui.alert-banner>
                <form method="POST" action="{{ route('logout') }}" class="auth-card__form">
                    @csrf
                    <x-ui.button type="submit" :full-width="true">Terminar sessão e entrar com {{ $convite->email }}</x-ui.button>
                </form>
            @endif
        @else
            <div class="auth-card__form">
                <a class="btn btn--primary btn--md btn--full-width" href="{{ route('registar', ['convite' => $token]) }}">Criar conta e aceitar</a>
                <a class="btn btn--secondary btn--md btn--full-width" href="{{ route('login') }}">Já tenho conta: entrar</a>
            </div>
        @endif
    @endsection
@endif
