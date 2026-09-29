@extends('v1.layouts.app')

@section('title', 'Panel de DT - ' . $team->name)

@section('content')
<div class="space-y-6">
    <!-- Header del Director Técnico -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 md:p-8 shadow-2xs">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <span class="grid size-16 place-items-center rounded-2xl bg-emerald-50 text-3xl font-black text-[#057a55] border border-emerald-100 shadow-2xs">
                    📋
                </span>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#057a55]">
                        <span>Panel de Director Técnico (DT)</span>
                        <span class="text-slate-300">•</span>
                        <span>{{ $team->tournament->name }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 md:text-3xl mt-1">{{ $team->name }}</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        DT: <span class="font-bold text-slate-800">{{ auth()->user()->name }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('dt.roster', $team) }}" class="rounded-lg bg-[#057a55] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#046c4b] flex items-center gap-2">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Gestionar Plantilla ({{ $team->players->count() }})</span>
                </a>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <!-- Reglas de Juego para el DT -->
    @if ($team->tournament->rules)
        @php $rules = $team->tournament->rules; @endphp
        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 shadow-2xs">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-900">
                <span>⚠️</span> Reglas Clave del Torneo para este Equipo
            </div>
            <div class="mt-3 grid gap-4 text-xs sm:grid-cols-3 text-amber-950">
                <div>
                    <span class="font-bold">Suspensión por Amarillas:</span>
                    <p class="text-amber-800 mt-0.5">{{ $rules->yellow_card_limit_for_suspension }} amarillas acumulan 1 partido de sanción.</p>
                </div>
                <div>
                    <span class="font-bold">Cierre de Alineación:</span>
                    <p class="text-amber-800 mt-0.5">La nómina debe enviarse <span class="font-bold underline">{{ $rules->lineup_lock_minutes_before_match }} minutos</span> antes del pitazo inicial.</p>
                </div>
                <div>
                    <span class="font-bold">Sustituciones Permitidas:</span>
                    <p class="text-amber-800 mt-0.5">Hasta {{ $rules->max_substitutions }} cambios oficiales por encuentro ({{ $rules->match_duration_minutes }} min).</p>
                </div>
            </div>
        </div>
    @endif

    <!-- 3 KPI Cards -->
    <div class="grid gap-5 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Jugadores Inscritos</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $team->players->count() }}</div>
            <a href="{{ route('dt.roster', $team) }}" class="mt-4 inline-block text-xs font-semibold text-[#057a55] hover:underline">
                Ver plantilla o subir CSV →
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Próximos Encuentros</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $upcomingMatches->count() }}</div>
            <p class="mt-4 text-xs text-slate-400">Partidos pendientes en el fixture</p>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Partidos Disputados</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $recentMatches->count() }}</div>
            <a href="{{ route('standings.index') }}" class="mt-4 inline-block text-xs font-semibold text-[#057a55] hover:underline">
                Ver tabla de posiciones →
            </a>
        </div>
    </div>

    <!-- Próximo Partido & Alineación -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
        <h2 class="text-sm font-bold text-slate-900">Próximo Partido en Calendario</h2>
        @if ($upcomingMatches->isEmpty())
            <p class="mt-3 text-xs text-slate-400">No hay partidos próximos programados en este momento.</p>
        @else
            @php $nextMatch = $upcomingMatches->first(); @endphp
            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/60 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                            {{ $nextMatch->match_date->format('d/m/Y - H:i') }} hrs
                        </span>
                        <div class="mt-2 text-lg font-bold text-slate-900">
                            {{ $nextMatch->homeTeam->name }} <span class="text-slate-400 font-normal">vs</span> {{ $nextMatch->awayTeam->name }}
                        </div>
                        @if ($nextMatch->venue)
                            <p class="mt-1 text-xs text-slate-500">
                                📍 Sede: <span class="text-slate-800 font-semibold">{{ $nextMatch->venue->name }}</span>
                                @if ($nextMatch->venue->navigation_url)
                                    • <a href="{{ $nextMatch->venue->navigation_url }}" target="_blank" class="text-[#057a55] hover:underline font-semibold">Ver Mapa GPS</a>
                                @endif
                            </p>
                        @endif
                    </div>

                    <div>
                        <a href="{{ route('matches.show', $nextMatch) }}" class="inline-flex items-center gap-2 rounded-lg bg-white border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                            Detalles del Encuentro
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
