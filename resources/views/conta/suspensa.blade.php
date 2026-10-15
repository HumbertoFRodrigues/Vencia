@extends('errors.layout')

@section('codigo', 'conta suspensa')
@section('titulo', 'A conta '.$conta->nome.' está suspensa')
@section('texto')
    O acesso aos dados desta empresa está parado.@if($conta->motivo_suspensao) Motivo: {{ $conta->motivo_suspensao }}.@endif
    Os dados continuam guardados. Contacta o suporte da Vencia para reactivar.
@endsection
@section('accoes')
    @if(auth()->user()->contas()->count() > 1)
        <a class="btn btn--primary btn--md" href="{{ route('conta.escolher') }}">Mudar de conta</a>
    @endif
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn--secondary btn--md">Terminar sessão</button></form>
@endsection
