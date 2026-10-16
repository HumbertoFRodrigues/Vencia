@extends('layouts.auth')

@section('titulo', 'Criar conta')
@section('subtitulo')
    @if($convite)
        Cria a tua conta para entrares na equipa de {{ $convite->conta->nome }}.
    @else
        A tua empresa e as tuas finanças pessoais num só sítio.@if($diasTeste > 0) Os primeiros {{ $diasTeste }} dias têm tudo do plano Pro.@endif
    @endif
@endsection

@section('conteudo')
    @if(! $aberto && ! $convite)
        <x-ui.alert-banner tone="info">Os registos estão fechados de momento. Se recebeste um convite, usa o link do email.</x-ui.alert-banner>
        <p class="auth-card__footnote"><a href="{{ route('login') }}">Já tens conta? Entrar</a></p>
    @else
        <form method="POST" action="{{ route('registar.store') }}" class="auth-card__form">
            @csrf
            @if($convite)
                <input type="hidden" name="convite" value="{{ $token }}">
            @endif
            {{-- Bots fill every field; people never see this one. --}}
            <div aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden">
                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <x-ui.input name="name" label="O teu nome" value="{{ old('name') }}" required autofocus autocomplete="name" :error="$errors->first('name')" />
            <x-ui.input type="email" name="email" label="Email" value="{{ old('email', $convite?->email) }}" required autocomplete="email" :error="$errors->first('email')" :readonly="(bool) $convite" />
            @unless($convite)
                <x-ui.input name="empresa" label="Nome da empresa (opcional)" value="{{ old('empresa') }}" placeholder="Pode ser o teu nome, se trabalhas por conta própria" autocomplete="organization" :error="$errors->first('empresa')" />
            @endunless
            <x-ui.input type="password" name="password" label="Palavra-passe" required autocomplete="new-password" hint="Pelo menos 8 caracteres." :error="$errors->first('password')" />
            <x-ui.input type="password" name="password_confirmation" label="Repetir a palavra-passe" required autocomplete="new-password" size="lg" />
            <x-ui.button type="submit" variant="primary" size="lg" :full-width="true">Criar conta</x-ui.button>
            <p class="auth-card__footnote" style="font-size:var(--text-xs)">Ao criar conta aceitas os <a href="{{ route('termos') }}" target="_blank">Termos</a> e a <a href="{{ route('privacidade') }}" target="_blank">Política de privacidade</a>.</p>
        </form>
        <p class="auth-card__footnote"><a href="{{ route('login') }}">Já tens conta? Entrar</a></p>
    @endif
@endsection
