@extends('v1.layouts.app')

@section('title', 'Registrar resultado')

@section('content')
    <h1>Registrar resultado</h1>
    <form method="POST" action="{{ route('matches.update', $match) }}">
        @csrf
        @method('PUT')
        <label>{{ $match->homeTeam->name }} <input name="home_score" type="number" min="0" value="{{ $match->home_score }}" required></label>
        <label>{{ $match->awayTeam->name }} <input name="away_score" type="number" min="0" value="{{ $match->away_score }}" required></label>
        <button type="submit">Guardar resultado</button>
    </form>
@endsection
