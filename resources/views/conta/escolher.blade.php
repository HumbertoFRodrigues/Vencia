@extends('layouts.auth')

@section('titulo', 'Escolher a conta')
@section('subtitulo', 'Fazes parte de várias empresas. Escolhe com qual queres trabalhar agora; o teu painel pessoal é o mesmo em todas.')

@section('conteudo')
    <ul class="lista-simples">
        @foreach($contas as $conta)
            <li>
                <div class="lista-simples__corpo">
                    <span class="lista-simples__titulo">{{ $conta->nome }}</span>
                    <span class="lista-simples__meta">{{ \App\Enums\Papel::from($conta->pivot->papel)->label() }}@if($conta->suspensa()) · suspensa@endif</span>
                </div>
                <form method="POST" action="{{ route('conta.mudar') }}">
                    @csrf
                    <input type="hidden" name="conta" value="{{ $conta->id }}">
                    <x-ui.button type="submit" size="sm" :variant="auth()->user()->conta_actual_id === $conta->id ? 'primary' : 'secondary'">
                        {{ auth()->user()->conta_actual_id === $conta->id ? 'Actual' : 'Entrar' }}
                    </x-ui.button>
                </form>
            </li>
        @endforeach
    </ul>
    <p class="auth-card__footnote"><a href="{{ route('dashboard') }}">Voltar</a></p>
@endsection
