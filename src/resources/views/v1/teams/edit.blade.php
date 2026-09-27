@extends('v1.layouts.app')

@section('title', 'Editar equipo')

@section('content')
    <h1>Editar equipo</h1>
    <form method="POST" action="{{ route('teams.update', $team) }}">
        @csrf
        @method('PUT')
        <input name="name" value="{{ $team->name }}" required>
        <input name="logo_path" value="{{ $team->logo_path }}">
        <button type="submit">Guardar cambios</button>
    </form>
@endsection
