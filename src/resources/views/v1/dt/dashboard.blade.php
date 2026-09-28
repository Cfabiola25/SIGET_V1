@extends('v1.layouts.app')

@section('title', 'Panel de DT - ' . $team->name)

@section('content')
<div class="space-y-8">
    <!-- Header del Director Técnico -->
    <div class="rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900/90 to-emerald-950/40 p-6 md:p-8 backdrop-blur-xl">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <span class="grid size-16 place-items-center rounded-2xl bg-emerald-500/20 text-3xl font-black text-emerald-400 shadow-inner">
                    📋
                </span>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-400">
                        <span>Panel de Director Técnico (DT)</span>
                        <span>•</span>
                        <span>{{ $team->tournament->name }}</span>
                    </div>
                    <h1 class="text-2xl font-black text-white md:text-3xl">{{ $team->name }}</h1>
                    <p class="text-sm text-slate-400">
                        DT: <span class="font-bold text-slate-200">{{ auth()->user()->name }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('dt.roster', $team) }}" class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400">
                    👥 Gestionar Plantilla ({{ $team->players->count() }})
                </a>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-400">
            {{ session('status') }}
        </div>
    @endif

    <!-- Reglas de Juego para el DT -->
    @if ($team->tournament->rules)
        @php $rules = $team->tournament->rules; @endphp
        <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-400">
                <span>⚠️</span> Reglas Clave del Torneo para este Equipo
            </div>
            <div class="mt-3 grid gap-4 text-xs sm:grid-cols-3 text-slate-300">
                <div>
                    <span class="font-bold text-white">Suspensión por Amarillas:</span>
                    <p class="text-slate-400">{{ $rules->yellow_card_limit_for_suspension }} amarillas acumulan 1 partido de sanción automática.</p>
                </div>
                <div>
                    <span class="font-bold text-white">Cierre de Alineación:</span>
                    <p class="text-slate-400">La nómina inicial debe enviarse digitalmente mínimo <span class="text-amber-300 font-bold">{{ $rules->lineup_lock_minutes_before_match }} minutos</span> antes del pitazo inicial.</p>
                </div>
                <div>
                    <span class="font-bold text-white">Sustituciones Permitidas:</span>
                    <p class="text-slate-400">Hasta {{ $rules->max_substitutions }} cambios oficiales por encuentro ({{ $rules->match_duration_minutes }} min).</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Accesos Rápidos y Estadísticas -->
    <div class="grid gap-6 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jugadores Inscritos</span>
                <span class="grid size-8 place-items-center rounded-lg bg-emerald-500/10 text-emerald-400 font-bold">#</span>
            </div>
            <div class="mt-4 text-3xl font-black text-white">{{ $team->players->count() }}</div>
            <a href="{{ route('dt.roster', $team) }}" class="mt-4 inline-block text-xs font-bold text-emerald-400 hover:underline">
                Ver plantilla o subir CSV →
            </a>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Próximos Encuentros</span>
                <span class="grid size-8 place-items-center rounded-lg bg-blue-500/10 text-blue-400 font-bold">⚽</span>
            </div>
            <div class="mt-4 text-3xl font-black text-white">{{ $upcomingMatches->count() }}</div>
            <p class="mt-4 text-xs text-slate-400">Partidos pendientes en el fixture</p>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Partidos Disputados</span>
                <span class="grid size-8 place-items-center rounded-lg bg-purple-500/10 text-purple-400 font-bold">🏆</span>
            </div>
            <div class="mt-4 text-3xl font-black text-white">{{ $recentMatches->count() }}</div>
            <a href="{{ route('standings.index', ['tournament' => $team->tournament_id]) }}" class="mt-4 inline-block text-xs font-bold text-emerald-400 hover:underline">
                Ver tabla de posiciones →
            </a>
        </div>
    </div>

    <!-- Próximo Partido & Alineación -->
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
        <h2 class="text-lg font-bold text-white">Próximo Partido en Calendario</h2>
        @if ($upcomingMatches->isEmpty())
            <p class="mt-2 text-sm text-slate-400">No hay partidos próximos programados en este momento.</p>
        @else
            @php $nextMatch = $upcomingMatches->first(); @endphp
            <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/60 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="rounded bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-400">
                            {{ $nextMatch->match_date->format('d/m/Y - H:i') }}
                        </span>
                        <div class="mt-2 text-xl font-bold text-white">
                            {{ $nextMatch->homeTeam->name }} <span class="text-slate-500">vs</span> {{ $nextMatch->awayTeam->name }}
                        </div>
                        @if ($nextMatch->venue)
                            <p class="mt-1 text-xs text-slate-400">
                                📍 Sede: <span class="text-slate-200">{{ $nextMatch->venue->name }}</span>
                                @if ($nextMatch->venue->navigation_url)
                                    • <a href="{{ $nextMatch->venue->navigation_url }}" target="_blank" class="text-emerald-400 hover:underline font-semibold">Ver Mapa GPS</a>
                                @endif
                            </p>
                        @endif
                    </div>

                    <div>
                        <a href="{{ route('matches.show', $nextMatch) }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 border border-slate-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700 transition">
                            Detalles del Encuentro
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
