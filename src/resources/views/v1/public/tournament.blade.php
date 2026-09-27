@extends('v1.layouts.app')

@section('title', 'Torneo')

@section('content')
    <div class="flex flex-col justify-between gap-6 border-b border-siget-ink/10 pb-8 sm:flex-row sm:items-end">
        <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Torneo en vivo</p><h1 class="siget-display mt-2 text-5xl leading-none">{{ $tournament->name }}</h1><p class="mt-4 text-siget-muted">{{ $tournament->sport_type }} · {{ $tournament->teams->count() }} equipos</p></div>
        <span class="w-fit rounded-full bg-siget-mint px-4 py-2 text-sm font-bold uppercase tracking-wider">{{ $tournament->status }}</span>
    </div>
    <div class="mt-8 grid gap-6 lg:grid-cols-[1.2fr_.8fr]">
        <x-card title="Fixture" eyebrow="Calendario">
            <div class="space-y-3">
                @forelse ($tournament->matches->sortBy('match_date') as $match)
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-siget-paper px-4 py-4"><div class="min-w-0 text-sm"><p class="truncate font-semibold">{{ $match->homeTeam->name }} <span class="mx-1 text-siget-coral">vs</span> {{ $match->awayTeam->name }}</p><p class="mt-1 text-xs text-siget-muted">{{ $match->match_date->format('d/m/Y · H:i') }}</p></div><strong class="shrink-0 text-lg">{{ $match->status === 'played' ? $match->home_score.' - '.$match->away_score : '—' }}</strong></div>
                @empty <p class="text-siget-muted">El fixture aún no está publicado.</p> @endforelse
            </div>
        </x-card>
        <x-card title="Tabla de posiciones" eyebrow="Al día">
            <div class="space-y-2">
                @forelse ($tournament->standings->sortByDesc('points') as $standing)
                    <div class="grid grid-cols-[2rem_1fr_repeat(2,2.5rem)] items-center gap-2 border-b border-siget-ink/5 py-3 text-sm"><span class="font-bold text-siget-coral">{{ $loop->iteration }}</span><span class="truncate font-semibold">{{ $standing->team->name }}</span><span class="text-center text-siget-muted">{{ $standing->matches_played }}</span><strong class="text-center">{{ $standing->points }}</strong></div>
                @empty <p class="text-siget-muted">La tabla se actualizará al comenzar los partidos.</p> @endforelse
            </div>
            <div class="mt-4 grid grid-cols-[2rem_1fr_repeat(2,2.5rem)] text-[10px] uppercase tracking-wider text-siget-muted"><span></span><span>Equipo</span><span class="text-center">PJ</span><span class="text-center">PTS</span></div>
        </x-card>
    </div>
@endsection
