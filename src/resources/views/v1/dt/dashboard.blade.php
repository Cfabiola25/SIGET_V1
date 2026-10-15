@extends('v1.layouts.app')

@section('title', 'Panel de DT - ' . $team->name)
@section('header_title', 'Panel de Director Técnico')

@section('header_badge')
<div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
    <span>{{ $team->tournament->name }}</span>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header del Director Técnico -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 md:p-8 shadow-2xs">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <span class="grid size-16 place-items-center rounded-2xl bg-emerald-50 text-[#1e3a8a] border border-emerald-100 shadow-2xs">
                    <x-reicon name="clipboard-list" class="size-8 text-[#1e3a8a]" />
                </span>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#1e3a8a]">
                        <span>Director Técnico Oficial</span>
                        <span class="text-slate-300">•</span>
                        <span>{{ $team->tournament->name }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 md:text-3xl mt-1">{{ $team->name }}</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        DT Asignado: <span class="font-bold text-slate-800">{{ auth()->user()->name }}</span> • Categoría Oficial Federativa
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('dt.roster', $team) }}" class="rounded-xl bg-[#1e3a8a] px-4 py-2.5 text-xs font-bold text-white shadow-2xs transition hover:bg-[#0284c7] flex items-center gap-2">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Mi Plantilla & Perfiles ({{ $team->players->count() }})</span>
                </a>
                <a href="{{ route('standings.index') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    Tabla de Posiciones
                </a>
                <a href="{{ route('scouting.index') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 shadow-2xs transition flex items-center gap-1.5">
                    <x-reicon name="search" class="size-4 text-emerald-600" />
                    <span>Radar de Talentos Libres</span>
                </a>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 flex items-center gap-2">
            <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <!-- Reglas Clave del Torneo para el DT -->
    @if ($team->tournament->rules)
        @php $rules = $team->tournament->rules; @endphp
        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 shadow-2xs">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-900">
                <x-reicon name="alert-triangle" class="size-4 text-amber-500 inline-block mr-1" /> Reglas Oficiales de Juego para este Equipo
            </div>
            <div class="mt-3 grid gap-4 text-xs sm:grid-cols-3 text-amber-950">
                <div>
                    <span class="font-bold">Límite de Sanción por Amarillas:</span>
                    <p class="text-amber-800 mt-0.5">{{ $rules->yellow_card_limit_for_suspension }} tarjetas amarillas acumulan 1 partido oficial de suspensión.</p>
                </div>
                <div>
                    <span class="font-bold">Cierre de Nómina Oficial:</span>
                    <p class="text-amber-800 mt-0.5">La nómina de titulares y suplentes se bloquea <span class="font-bold underline">{{ $rules->lineup_lock_minutes_before_match }} minutos</span> antes del inicio del encuentro.</p>
                </div>
                <div>
                    <span class="font-bold">Sustituciones en Partido:</span>
                    <p class="text-amber-800 mt-0.5">Hasta {{ $rules->max_substitutions }} cambios reglamentarios por partido (duración: {{ $rules->match_duration_minutes }} min).</p>
                </div>
            </div>
        </div>
    @endif

    <!-- 3 KPI Cards -->
    <div class="grid gap-5 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jugadores en Nómina</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $team->players->count() }}</div>
            <a href="{{ route('dt.roster', $team) }}" class="mt-4 inline-block text-xs font-bold text-[#1e3a8a] hover:underline">
                Gestionar plantilla, perfiles y fichas médicas &rarr;
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Partidos Programados</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $upcomingMatches->count() }}</div>
            <p class="mt-4 text-xs text-slate-400">Encuentros oficiales asignados en el calendario</p>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Partidos Disputados</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $recentMatches->count() }}</div>
            <a href="{{ route('standings.index') }}" class="mt-4 inline-block text-xs font-bold text-[#1e3a8a] hover:underline">
                Ver tabla de posiciones oficial &rarr;
            </a>
        </div>
    </div>

    <!-- Sección Exclusiva: Partidos Asignados a este Equipo -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <svg class="size-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Partidos Asignados a {{ $team->name }}</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Consulta cuándo, a qué hora, en qué cancha juegas, con qué árbitro, contra quién y nominas a tu alineación oficial.
                </p>
            </div>
            <span class="text-xs font-bold px-3 py-1 bg-slate-100 text-slate-700 rounded-full self-start sm:self-auto">
                {{ $upcomingMatches->count() }} próximos encuentros
            </span>
        </div>

        @if ($upcomingMatches->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-slate-400">
                <x-reicon name="calendar" class="size-10 text-slate-300 mx-auto block mb-2" />
                <p class="text-sm font-semibold text-slate-600">No hay partidos próximos programados en este momento.</p>
                <p class="text-xs text-slate-400 mt-1">El director del torneo publicará las fechas y horarios en el calendario oficial.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($upcomingMatches as $match)
                    @php
                        $isHome = $match->home_team_id === $team->id;
                        $opponent = $isHome ? $match->awayTeam : $match->homeTeam;
                        $myLineupCount = $match->lineups->where('team_id', $team->id)->count();
                        $hasLineup = $myLineupCount > 0;
                        $isSubmissionLocked = $match->isLineupSubmissionLocked();
                    @endphp

                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 hover:bg-slate-50/90 transition">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                            
                            <!-- Columna 1: Horario, Fecha y Condición -->
                            <div class="space-y-1.5 min-w-[200px]">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $isHome ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-purple-100 text-purple-800 border border-purple-200' }}">
                                        {{ $isHome ? 'LOCAL' : 'VISITANTE' }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-800">
                                        {{ $match->match_date?->format('d/m/Y') ?? 'Fecha por definir' }}
                                    </span>
                                </div>
                                <div class="text-2xl font-black text-slate-900 tracking-tight">
                                    {{ $match->match_date?->format('H:i') ?? '--:--' }} <span class="text-xs font-bold text-slate-500 uppercase">hrs</span>
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    Jornada #{{ $match->round_number ?? 1 }} • {{ $match->tournament?->name }}
                                </div>
                            </div>

                            <!-- Columna 2: Enfrentamiento (Rival) -->
                            <div class="flex-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rival Asignado</span>
                                <div class="mt-1 flex items-center gap-3">
                                    <div class="size-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-black text-slate-800 text-sm shadow-2xs">
                                        {{ strtoupper(substr($opponent?->name ?? 'R', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-base font-bold text-slate-900 leading-tight">
                                            {{ $isHome ? $team->name : $opponent?->name }} 
                                            <span class="text-slate-400 font-normal mx-1">vs</span> 
                                            {{ $isHome ? $opponent?->name : $team->name }}
                                        </div>
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            Contra: <span class="font-bold text-slate-800">{{ $opponent?->name ?? 'Equipo Rival' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna 3: Sede, Cancha y Árbitro Oficial -->
                            <div class="space-y-1.5 min-w-[220px]">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Lugar / Cancha</span>
                                    <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5 mt-0.5">
                                        <x-reicon name="pin" class="size-3.5 text-slate-500" />
                                        <span>{{ $match->venue?->name ?? 'Sede por asignar' }}</span>
                                        <span class="text-emerald-700 font-extrabold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 text-[10px]">
                                            Cancha #{{ $match->field_number ?? 1 }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Árbitro Oficial Asignado</span>
                                    <div class="text-xs font-semibold text-slate-700 flex items-center gap-1.5 mt-0.5">
                                        <x-reicon name="shield-check" class="size-3.5 text-[#1e3a8a]" />
                                        <span class="{{ $match->referee ? 'text-slate-900 font-bold' : 'text-slate-400 italic' }}">
                                            {{ $match->referee?->name ?? 'Árbitro Oficial por designar' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna 4: Botón Nómina / Alineación Oficial -->
                            <div class="flex flex-col sm:items-end justify-center gap-2 min-w-[180px]">
                                @if ($hasLineup)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        ✓ Nómina Registrada ({{ $myLineupCount }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        ⚠️ Nómina Pendiente
                                    </span>
                                @endif

                                @if ($isSubmissionLocked)
                                    <div class="text-[11px] text-rose-600 font-bold">
                                        Plazo cerrado para nómina
                                    </div>
                                @else
                                    <a href="{{ route('matches.lineup.edit', ['match' => $match->id, 'team' => $team->id]) }}" 
                                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#1e3a8a] px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-[#0284c7] active:bg-[#172554] transition transform active:scale-95 text-center">
                                        <x-reicon name="clipboard-list" class="size-4" />
                                        <span>{{ $hasLineup ? 'Modificar Nómina' : 'Nominar Jugadores' }}</span>
                                    </a>
                                @endif

                                <a href="{{ route('matches.show', $match) }}" class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 hover:underline">
                                    Ver ficha del partido &rarr;
                                </a>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Historial de Partidos Jugados -->
    @if ($recentMatches->isNotEmpty())
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-3">
            <h3 class="text-base font-bold text-slate-900">Historial Reciente de Encuentros</h3>
            <div class="divide-y divide-slate-100">
                @foreach ($recentMatches as $rm)
                    @php
                        $isHome = $rm->home_team_id === $team->id;
                        $opponent = $isHome ? $rm->awayTeam : $rm->homeTeam;
                    @endphp
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 font-mono">{{ $rm->match_date?->format('d/m/Y') }}</span>
                            <span class="font-bold text-slate-800">
                                {{ $isHome ? $team->name : $opponent?->name }} 
                                <span class="px-2 py-0.5 rounded bg-slate-100 font-black text-slate-900">
                                    {{ $rm->home_score ?? 0 }} - {{ $rm->away_score ?? 0 }}
                                </span> 
                                {{ $isHome ? $opponent?->name : $team->name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-slate-500">Árbitro: {{ $rm->referee?->name ?? 'Oficial' }}</span>
                            <a href="{{ route('matches.show', $rm) }}" class="font-bold text-[#1e3a8a] hover:underline">
                                Ver Acta Oficial &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
