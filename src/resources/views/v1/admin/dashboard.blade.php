@extends('v1.layouts.app')

@section('title', 'Panel Admin')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Espacio de trabajo</p><h1 class="siget-display mt-2 text-5xl">Mis torneos</h1><p class="mt-3 max-w-xl text-siget-muted">Gestiona exclusivamente las competiciones asignadas a tu cuenta.</p></div>
        <span class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-400">{{ auth()->user()->role }}</span>
    </div>

    <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($tournaments as $tournament)
            <article class="flex flex-col rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md transition hover:border-slate-700">
                <div class="flex items-center justify-between gap-3"><span class="rounded-full bg-siget-mint px-3 py-1 text-xs font-bold uppercase tracking-wider">{{ $tournament->status }}</span><span class="text-sm text-siget-coral">{{ $tournament->sport_type }}</span></div>
                <h2 class="mt-8 text-2xl font-semibold">{{ $tournament->name }}</h2>
                <p class="mt-2 text-sm text-siget-muted">{{ $tournament->start_date->format('d M Y') }} - {{ $tournament->end_date->format('d M Y') }}</p>
                <div class="mt-6 grid grid-cols-2 gap-3 border-y border-siget-ink/10 py-4 text-sm"><span><strong class="block text-lg text-siget-ink">{{ $tournament->teams_count }}</strong>Equipos</span><span><strong class="block text-lg text-siget-ink">{{ $tournament->matches_count }}</strong>Partidos</span></div>
                <a class="mt-auto pt-6 text-sm font-semibold text-siget-coral underline decoration-2 underline-offset-4" href="{{ route('tournaments.show', $tournament) }}">Gestionar torneo</a>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-siget-ink/20 p-8 text-siget-muted md:col-span-2 lg:col-span-3">No tienes torneos asignados.</div>
        @endforelse
    </div>
@endsection
