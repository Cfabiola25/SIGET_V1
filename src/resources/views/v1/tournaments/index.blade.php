@extends('v1.layouts.app')

@section('title', 'Torneos')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Competencias</p><h1 class="siget-display mt-2 text-5xl">Torneos</h1></div>@auth @if (auth()->user()->role === 'organizer') <x-button href="{{ route('tournaments.create') }}" variant="coral">+ Nuevo torneo</x-button> @endif @endauth</div>
    <form class="mt-8 grid gap-3 rounded-xl border border-slate-800 bg-slate-900/60 p-4 sm:grid-cols-2 lg:grid-cols-6" method="GET" action="{{ route('tournaments.index') }}">
        <label class="text-xs font-semibold text-slate-300 lg:col-span-2">Buscar torneo<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre"></label>
        <label class="text-xs font-semibold text-slate-300">Deporte<select class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="sport_type"><option value="">Todos</option>@foreach ($sports as $sport)<option value="{{ $sport->name }}" @selected(($filters['sport_type'] ?? '') === $sport->name)>{{ $sport->name }}</option>@endforeach</select></label>
        <label class="text-xs font-semibold text-slate-300">Estado<select class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="status"><option value="">Todos</option><option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pendiente</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Activo</option><option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completado</option></select></label>
        <label class="text-xs font-semibold text-slate-300">Desde<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"></label>
        <label class="text-xs font-semibold text-slate-300">Hasta<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"></label>
        <div class="flex items-end gap-3 sm:col-span-2 lg:col-span-6"><button class="rounded-lg bg-siget-mint px-4 py-2.5 text-sm font-bold text-siget-ink" type="submit">Buscar</button><a class="text-sm font-semibold text-slate-300 underline underline-offset-4" href="{{ route('tournaments.index') }}">Limpiar filtros</a></div>
    </form>
    <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($tournaments as $tournament)
            <x-card><div class="flex justify-between"><span class="rounded-full bg-siget-mint px-3 py-1 text-xs font-bold uppercase tracking-wider">{{ $tournament->status }}</span><span class="text-siget-coral">{{ $tournament->sport_type }}</span></div><h2 class="mt-8 text-xl font-semibold">{{ $tournament->name }}</h2><p class="mt-2 text-sm text-siget-muted">{{ $tournament->start_date->format('d M Y') }} — {{ $tournament->end_date->format('d M Y') }}</p><a class="mt-6 inline-block text-sm font-semibold underline decoration-siget-coral decoration-2 underline-offset-4" href="{{ route('tournaments.show', $tournament) }}">Ver torneo →</a></x-card>
        @empty <x-card class="md:col-span-2 lg:col-span-3"><p class="text-siget-muted">No hay torneos disponibles.</p></x-card> @endforelse
    </div>
    <div class="mt-6">{{ $tournaments->links() }}</div>
@endsection
