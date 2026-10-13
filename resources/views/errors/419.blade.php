@extends('errors.layout')

@section('codigo', '419')
@section('titulo', 'A sessão expirou')
@section('texto', 'Por segurança, a página esteve aberta demasiado tempo sem actividade. Volta a carregá-la e repete o que estavas a fazer.')
@section('accoes')
    <button type="button" class="btn btn--primary btn--md" onclick="location.reload()">Recarregar a página</button>
    <a class="btn btn--secondary btn--md" href="{{ url('/login') }}">Iniciar sessão</a>
@endsection
