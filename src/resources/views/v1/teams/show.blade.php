@extends('v1.layouts.app')

@section('title', $team->name)

@section('content')
    <h1>{{ $team->name }}</h1>
    <p>Torneo: {{ $team->tournament->name }}</p>
    <p>Capitán: {{ $team->captain->name }}</p>
    <h2>Jugadores</h2>
    <ul>
        @foreach ($team->players as $player)
            <li>{{ $player->name }} #{{ $player->jersey_number }}</li>
        @endforeach
    </ul>
@endsection
