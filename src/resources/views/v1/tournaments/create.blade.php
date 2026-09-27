@extends('v1.layouts.app')

@section('title', 'Nuevo torneo')

@section('content')
    <h1>Nuevo torneo</h1>
    <form method="POST" action="{{ route('tournaments.store') }}">
        @csrf
        <input name="name" placeholder="Nombre" required>
        <input name="sport_type" placeholder="Deporte" required>
        <input name="start_date" type="date" required>
        <input name="end_date" type="date" required>
        <button type="submit">Crear torneo</button>
    </form>
@endsection
