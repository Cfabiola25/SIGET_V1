@extends('v1.layouts.app')

@section('title', 'Inscribir equipo')

@section('content')
    <h1>Inscribir equipo</h1>
    <form method="POST" action="{{ route('teams.store') }}">
        @csrf
        <select name="tournament_id" required>
            @foreach ($tournaments as $tournament)
                <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
            @endforeach
        </select>
        <input name="name" placeholder="Nombre del equipo" required>
        <input name="logo_path" placeholder="Ruta del logo">
        <button type="submit">Inscribir equipo</button>
    </form>
@endsection
