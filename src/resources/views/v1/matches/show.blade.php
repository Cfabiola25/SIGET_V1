@extends('v1.layouts.app')

@section('title', 'Partido')

@section('content')
    <h1>{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }}</h1>
    <p>{{ $match->home_score }} - {{ $match->away_score }}</p>
    <p>{{ $match->status }} | {{ $match->match_date }}</p>
@endsection
