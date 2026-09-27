@extends('v1.layouts.app')

@section('title', 'Detalle del torneo')

@section('content')
    <h1>{{ $tournament->name }}</h1>
    <p>{{ $tournament->sport_type }} | {{ $tournament->status }}</p>
    <h2>Partidos</h2>
    <ul>
        @foreach ($tournament->matches as $match)
            <li>{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }} - {{ $match->match_date }}</li>
        @endforeach
    </ul>
@endsection
