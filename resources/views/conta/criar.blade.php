@extends('layouts.auth')

@php
    $temContas = auth()->user()->contas()->exists();
@endphp

@section('titulo', $temContas ? 'Criar outra empresa' : 'Criar uma conta de empresa')
@section('subtitulo', $temContas
    ? 'Ficas dono da nova empresa, que começa no plano Grátis. Mudas entre empresas no menu do teu perfil.'
    : 'Ainda não fazes parte de nenhuma empresa na Vencia. Cria a tua (começa no plano Grátis) ou pede um convite a quem já usa.')

@section('conteudo')
    <form method="POST" action="{{ route('conta.guardar') }}" class="auth-card__form">
        @csrf
        <x-ui.input name="nome" label="Nome da empresa" value="{{ old('nome', $temContas ? '' : auth()->user()->name) }}" required autofocus size="lg" :error="$errors->first('nome')" />
        <x-ui.button type="submit" variant="primary" size="lg" :full-width="true">Criar empresa</x-ui.button>
    </form>
    @if($temContas)
        <p class="auth-card__footnote"><a href="{{ route('dashboard') }}">← Voltar</a></p>
    @else
        <form method="POST" action="{{ route('logout') }}" class="auth-card__footnote">
            @csrf
            <button type="submit" class="link-button">Terminar sessão</button>
        </form>
    @endif
@endsection
