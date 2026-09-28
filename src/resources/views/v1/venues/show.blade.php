@extends('v1.layouts.app')

@section('title', 'Sede: ' . $venue->name)

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('venues.index') }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
                ← Volver a Sedes
            </a>
            <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">{{ $venue->name }}</h1>
            <p class="text-sm text-slate-400">{{ $venue->address ?? 'Dirección no registrada' }} @if($venue->city) • {{ $venue->city }} @endif</p>
        </div>

        <div class="flex items-center gap-3">
            @if ($venue->navigation_url)
                <a href="{{ $venue->navigation_url }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-md transition hover:bg-emerald-400">
                    <span>📍</span> Abrir en Google Maps / Waze
                </a>
            @endif
            @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
                <form method="POST" action="{{ route('venues.destroy', $venue) }}" onsubmit="return confirm('¿Eliminar esta sede?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-2.5 text-sm font-bold text-rose-400 hover:bg-rose-500/20 transition">
                        Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-400">
            {{ session('status') }}
        </div>
    @endif

    <!-- Ficha técnica de la sede -->
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Canchas Reglamentarias</span>
            <div class="mt-2 text-3xl font-black text-white">{{ $venue->field_count }}</div>
            <span class="text-xs text-slate-500">Capacidad para partidos simultáneos</span>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Superficie de Juego</span>
            <div class="mt-2 text-2xl font-black text-emerald-400">{{ ucfirst($venue->surface_type) }}</div>
            <span class="text-xs text-slate-500">Tipo de terreno</span>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Partidos Programados</span>
            <div class="mt-2 text-3xl font-black text-white">{{ $venue->matches->count() }}</div>
            <span class="text-xs text-slate-500">Encuentros en fixture</span>
        </div>
    </div>

    @if ($venue->notes)
        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-5">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Instrucciones y Normas de la Cancha</h2>
            <p class="mt-1 text-sm text-slate-300">{{ $venue->notes }}</p>
        </div>
    @endif

    <!-- Partidos programados en la sede -->
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
        <h2 class="text-lg font-bold text-white">Partidos Asignados en esta Sede</h2>
        @if ($venue->matches->isEmpty())
            <p class="mt-2 text-sm text-slate-400">Aún no hay partidos programados en este escenario deportivo.</p>
        @else
            <div class="mt-4 divide-y divide-slate-800">
                @foreach ($venue->matches as $match)
                    <div class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span class="text-xs font-semibold text-emerald-400">{{ $match->tournament->name }}</span>
                            <div class="font-bold text-white">{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }}</div>
                            <span class="text-xs text-slate-400">{{ $match->match_date->format('d/m/Y H:i') }} • {{ $match->field_number ?? 'Cancha Principal' }}</span>
                        </div>
                        <a href="{{ route('matches.show', $match) }}" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:bg-slate-700 transition self-start sm:self-auto">
                            Ver Partido
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
