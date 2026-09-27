@extends('v1.layouts.app')

@section('title', 'Programar partido')

@section('content')
    <h1>Programar partido</h1>
    <form method="POST" action="{{ route('matches.store') }}">
        @csrf
        <select name="tournament_id" required>
            @foreach ($tournaments as $tournament)
                <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
            @endforeach
        </select>
        <input name="home_team_id" type="number" placeholder="ID equipo local" required>
        <input name="away_team_id" type="number" placeholder="ID equipo visitante" required>
        <input name="match_date" type="datetime-local" required>
        <button type="submit">Programar</button>
    </form>
@endsection
