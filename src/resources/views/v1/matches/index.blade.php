@extends('v1.layouts.app')

@section('title', 'Partidos')

@section('content')
    <div class="flex items-end justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Competición</p><h1 class="siget-display mt-2 text-5xl">Partidos</h1></div>@auth @if (in_array(auth()->user()->role, ['admin', 'organizer'], true)) <x-button href="{{ route('matches.create') }}" variant="coral">+ Programar</x-button> @endif @endauth</div>
    <div class="mt-8 space-y-3">@forelse ($matches as $match)<x-card class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><p class="text-xs font-bold uppercase tracking-wider text-siget-coral">{{ $match->tournament->name }}</p><h2 class="mt-2 text-lg font-semibold">{{ $match->homeTeam->name }} <span class="font-normal text-siget-muted">vs</span> {{ $match->awayTeam->name }}</h2><p class="mt-1 text-sm text-siget-muted">{{ $match->match_date->format('d/m/Y · H:i') }}</p></div><div class="flex items-center gap-5"><strong class="text-2xl">{{ $match->status === 'played' ? $match->home_score.' - '.$match->away_score : '—' }}</strong><span class="rounded-full bg-siget-mint px-3 py-1 text-xs font-bold uppercase">{{ $match->status }}</span>@auth @if (in_array(auth()->user()->role, ['admin', 'organizer'], true)) <a class="text-sm font-semibold text-siget-coral" href="{{ route('matches.edit', $match) }}">Resultado</a> @endif @endauth</div></x-card>@empty <x-card><p class="text-siget-muted">No hay partidos programados.</p></x-card>@endforelse</div>
@endsection
