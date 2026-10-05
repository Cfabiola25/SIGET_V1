@extends('v1.layouts.app')

@section('title', $tournament->name)
@section('header_title', $tournament->name)

@section('header_badge')
<span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
    {{ $tournament->status === 'active' ? 'Activo' : ($tournament->status === 'completed' ? 'Finalizado' : 'Pendiente') }}
</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Tarjeta de Resumen del Torneo -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-800">
                    <a href="{{ route('tournaments.index') }}" class="hover:underline">&larr; Torneos</a>
                    <span>•</span>
                    <span>{{ $tournament->sport_type }}</span>
                </div>
                <h2 class="mt-1.5 text-2xl md:text-3xl font-black text-slate-900 tracking-tight">{{ $tournament->name }}</h2>
                <p class="mt-1.5 text-xs md:text-sm text-slate-500">
                    Fechas: {{ $tournament->start_date?->format('d M Y') }} — {{ $tournament->end_date?->format('d M Y') }}
                    @if ($tournament->admin) • Director de Torneo: <strong class="text-slate-700 font-semibold">{{ $tournament->admin->name }}</strong> @endif
                </p>
            </div>

            <!-- Botones de Acción Rápida -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('matches.schedule') }}" class="px-4 py-2 rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Programar Partidos</span>
                </a>
                <a href="{{ route('tournaments.brackets', $tournament) }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5"><x-reicon name="trophy" class="size-3.5 text-slate-500" /><span>Llaves</span></span>
                </a>
                <a href="{{ route('tournaments.disciplinary', $tournament) }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5"><x-reicon name="shield" class="size-3.5 text-slate-500" /><span>Disciplinario</span></span>
                </a>
                <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5"><x-reicon name="setting" class="size-3.5 text-slate-500" /><span>Reglamento</span></span>
                </a>
                <a href="{{ route('standings.index', ['tournament' => $tournament->id]) }}" class="px-4 py-2 rounded-lg bg-sky-100 text-sky-900 text-xs font-bold hover:bg-sky-200 transition">
                    Posiciones
                </a>
            </div>
        </div>
    </div>

    <!-- Widget de Reglamento Activo del Torneo -->
    @if ($tournament->rules)
        @php $r = $tournament->rules; @endphp
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <x-reicon name="shield" class="size-4 text-slate-500" />
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Reglamento Técnico del Torneo</h3>
                </div>
                <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 transition">
                    Editar Reglamento &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Límite de Tarjetas Amarillas</span>
                    <div class="mt-1 text-lg font-bold text-amber-700">{{ $r->yellow_card_limit_for_suspension }} tarjetas</div>
                    <span class="text-[11px] text-slate-400">= 1 partido de suspensión</span>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Sistema de Puntuación</span>
                    <div class="mt-1 text-lg font-bold text-emerald-800">{{ $r->points_for_win }}PG / {{ $r->points_for_draw }}PE / {{ $r->points_for_loss }}PP</div>
                    <span class="text-[11px] text-slate-400">Clasificación estándar</span>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Duración del Partido</span>
                    <div class="mt-1 text-lg font-bold text-slate-900">{{ $r->match_duration_minutes }} minutos</div>
                    <span class="text-[11px] text-slate-400">Tiempo reglamentario regular</span>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Cambios Permitidos</span>
                    <div class="mt-1 text-lg font-bold text-sky-800">{{ $r->max_substitutions }} cambios</div>
                    <span class="text-[11px] text-slate-400">Por equipo por partido</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Cuadrícula: Equipos Inscritos y Partidos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Equipos en el Torneo -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Equipos Inscritos ({{ $tournament->teams->count() }})</h3>
                <a href="{{ route('teams.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">
                    Gestionar Equipos &rarr;
                </a>
            </div>

            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($tournament->teams as $team)
                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center border border-emerald-200">
                                {{ strtoupper(substr($team->name, 0, 2)) }}
                            </div>
                            <div>
                                <a href="{{ route('teams.show', $team) }}" class="font-bold text-sm text-slate-900 hover:text-emerald-700 transition">
                                    {{ $team->name }}
                                </a>
                                <p class="text-xs text-slate-500">
                                    Director Técnico: {{ $team->coach_name }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('teams.show', $team) }}" class="px-3 py-1 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Ver Equipo
                        </a>
                    </div>
                @empty
                    <p class="py-6 text-xs text-slate-500 text-center">No hay equipos inscritos todavía.</p>
                @endforelse
            </div>
        </div>

        <!-- Matches & Fixtures -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Partidos y Calendario ({{ $tournament->matches->count() }})</h3>
                <a href="{{ route('matches.schedule') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">
                    Ver Calendario &rarr;
                </a>
            </div>

            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($tournament->matches->take(6) as $match)
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-900">
                                {{ $match->homeTeam->name }}
                                @if ($match->status === 'played')
                                    <span class="text-emerald-700 font-black">({{ $match->home_score }} - {{ $match->away_score }})</span>
                                @else
                                    <span class="text-slate-400 font-normal">vs</span>
                                @endif
                                {{ $match->awayTeam->name }}
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $match->match_date->format('d M Y · H:i') }}
                                @if ($match->venue) • <x-reicon name="pin" class="size-3 text-slate-400 inline-block" /> {{ $match->venue->name }} @endif
                            </p>
                        </div>
                        <a href="{{ route('matches.show', $match) }}" class="px-3 py-1 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Detalles
                        </a>
                    </div>
                @empty
                    <p class="py-6 text-xs text-slate-500 text-center">No hay partidos programados todavía.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
