@extends('layouts.auth')

@section('titulo', 'Criar uma conta de empresa')
@section('subtitulo', 'Ainda não fazes parte de nenhuma empresa na Vencia. Cria a tua (começa no plano Grátis) ou pede um convite a quem já usa.')

@section('conteudo')
    <form method="POST" action="{{ route('conta.guardar') }}" class="auth-card__form">
        @csrf
        <x-ui.input name="nome" label="Nome da empresa" value="{{ old('nome', auth()->user()->name) }}" required autofocus :error="$errors->first('nome')" />
        <x-ui.button type="submit" variant="primary" :full-width="true">Criar</x-ui.button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="auth-card__footnote">
        @csrf
        <button type="submit" class="link-button">Terminar sessão</button>
    </form>
@endsection
