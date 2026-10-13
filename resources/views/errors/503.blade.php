@extends('errors.layout')

@section('codigo', '503')
@section('titulo', 'Em manutenção')
@section('texto', 'A Vencia está a ser actualizada e volta dentro de minutos.')
@section('accoes')
    <button type="button" class="btn btn--primary btn--md" onclick="location.reload()">Tentar de novo</button>
@endsection
