@extends('v1.layouts.app')

@section('title', 'Editar torneo')

@section('content')
    <h1>Editar torneo</h1>
    <form method="POST" action="{{ route('tournaments.update', $tournament) }}">
        @csrf
        @method('PUT')
        <input name="name" value="{{ $tournament->name }}" required>
        <input name="sport_type" value="{{ $tournament->sport_type }}" required>
        <input name="start_date" type="date" value="{{ $tournament->start_date->format('Y-m-d') }}" required>
        <input name="end_date" type="date" value="{{ $tournament->end_date->format('Y-m-d') }}" required>
        <select name="status"><option value="pending">Pendiente</option><option value="active">Activo</option><option value="completed">Completado</option></select>
        <button type="submit">Guardar cambios</button>
    </form>
@endsection
