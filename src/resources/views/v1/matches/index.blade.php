@extends('v1.layouts.app')

@section('title', 'Partidos')

@section('content')
    <div class="flex items-end justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Competición</p><h1 class="siget-display mt-2 text-5xl">Partidos</h1></div>@auth @if (in_array(auth()->user()->role, ['admin', 'organizer'], true)) <x-button href="{{ route('matches.create') }}" variant="coral">+ Programar</x-button> @endif @endauth</div>
    <form class="mt-8 grid gap-3 rounded-xl border border-slate-800 bg-slate-900/60 p-4 sm:grid-cols-2 lg:grid-cols-6" method="GET" action="{{ route('matches.index') }}">
        <label class="text-xs font-semibold text-slate-300 lg:col-span-2">Buscar partido<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Torneo o equipo"></label>
        <label class="text-xs font-semibold text-slate-300">Deporte<select class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="sport_type"><option value="">Todos</option>@foreach ($sports as $sport)<option value="{{ $sport->name }}" @selected(($filters['sport_type'] ?? '') === $sport->name)>{{ $sport->name }}</option>@endforeach</select></label>
        <label class="text-xs font-semibold text-slate-300">Estado<select class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="status"><option value="">Todos</option><option value="scheduled" @selected(($filters['status'] ?? '') === 'scheduled')>Programado</option><option value="played" @selected(($filters['status'] ?? '') === 'played')>Jugado</option><option value="suspended" @selected(($filters['status'] ?? '') === 'suspended')>Suspendido</option></select></label>
        <label class="text-xs font-semibold text-slate-300">Desde<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"></label>
        <label class="text-xs font-semibold text-slate-300">Hasta<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"></label>
        <div class="flex items-end gap-3 sm:col-span-2 lg:col-span-6"><button class="rounded-lg bg-siget-mint px-4 py-2.5 text-sm font-bold text-siget-ink" type="submit">Buscar</button><a class="text-sm font-semibold text-slate-300 underline underline-offset-4" href="{{ route('matches.index') }}">Limpiar filtros</a></div>
    </form>
    <div class="mt-8 space-y-3">
        @forelse ($matches as $match)
            <x-card class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><p class="text-xs font-bold uppercase tracking-wider text-siget-coral">{{ $match->tournament->name }} · {{ $match->tournament->sport_type }}</p><h2 class="mt-2 text-lg font-semibold">{{ $match->homeTeam->name }} <span class="font-normal text-siget-muted">vs</span> {{ $match->awayTeam->name }}</h2><p class="mt-1 text-sm text-siget-muted">{{ $match->match_date->format('d/m/Y · H:i') }}</p></div><div class="flex items-center gap-5"><strong class="text-2xl">{{ $match->status === 'played' ? $match->home_score.' - '.$match->away_score : '—' }}</strong><span class="rounded-full bg-siget-mint px-3 py-1 text-xs font-bold uppercase">{{ $match->status }}</span>@auth @if (in_array(auth()->user()->role, ['admin', 'organizer'], true)) <a class="text-sm font-semibold text-siget-coral" href="{{ route('matches.edit', $match) }}">Resultado</a> @endif @endauth</div></x-card>
        @empty
            <x-card><p class="text-siget-muted">No hay partidos que coincidan con los filtros.</p></x-card>
        @endforelse
    </div>
    <div class="mt-6">{{ $matches->links() }}</div>
@endsection
