@extends('layouts.auth')

@section('titulo', 'Confirma o teu email')
@section('subtitulo')
    Enviámos um link para <strong>{{ auth()->user()->email }}</strong>. Abre-o para activar a conta. Se não o encontrares, vê também a pasta de spam.
@endsection

@section('conteudo')
    {{-- Local testing only: with mail going to the log, nobody receives the link, so it is shown here. --}}
    @if(app()->isLocal() && config('mail.default') === 'log')
        <x-ui.alert-banner tone="info" title="Modo local">
            Os emails ficam em <code>storage/logs/laravel.log</code>.
            <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify', now()->addHours(48), ['id' => auth()->id(), 'hash' => sha1(auth()->user()->getEmailForVerification())]) }}">Confirmar o email agora</a>
        </x-ui.alert-banner>
    @endif
    <form method="POST" action="{{ route('verification.send') }}" class="auth-card__form">
        @csrf
        <x-ui.button type="submit" variant="primary" :full-width="true">Enviar o link outra vez</x-ui.button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="auth-card__footnote">
        @csrf
        <button type="submit" class="link-button">Usar outro email (terminar sessão)</button>
    </form>
@endsection
