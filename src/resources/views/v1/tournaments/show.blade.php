@extends('v1.layouts.app')

@section('title', $tournament->name)

@section('content')
<div class="space-y-8">
    <div class="rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900/90 to-emerald-950/40 p-6 md:p-8 backdrop-blur-xl">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-400">
                    <a href="{{ route('tournaments.index') }}" class="hover:underline">← Torneos</a>
                    <span>•</span>
                    <span>{{ ucfirst($tournament->status) }}</span>
                </div>
                <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">{{ $tournament->name }}</h1>
                <p class="mt-1 text-sm text-slate-400">
                    Fútbol • Del {{ $tournament->start_date?->format('d/m/Y') }} al {{ $tournament->end_date?->format('d/m/Y') }}
                    @if ($tournament->admin) • Admin: <span class="text-slate-200">{{ $tournament->admin->name }}</span> @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if (auth()->check() && (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $tournament->admin_id === auth()->id())))
                    <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-2 text-sm font-bold text-amber-400 hover:bg-amber-500/20 transition">
                        ⚙️ Reglas de Competición
                    </a>
                    <a href="{{ route('tournaments.edit', $tournament) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-bold text-slate-200 hover:bg-slate-700 transition">
                        Editar Torneo
                    </a>
                    <a href="{{ route('tournaments.fixtures.generate', $tournament) }}" class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-2 text-sm font-bold text-emerald-400 hover:bg-emerald-500/20 transition flex items-center gap-1.5">
                        🗓️ Generar Fixture
                    </a>
                @endif
                <a href="{{ route('tournaments.brackets', $tournament) }}" class="rounded-xl border border-cyan-500/40 bg-cyan-500/10 px-4 py-2 text-sm font-bold text-cyan-300 hover:bg-cyan-500/20 transition flex items-center gap-1.5">
                    🏆 Brackets
                </a>
                <a href="{{ route('tournaments.disciplinary', $tournament) }}" class="rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-2 text-sm font-bold text-rose-300 hover:bg-rose-500/20 transition flex items-center gap-1.5">
                    ⚖️ Tribunal Disciplinario
                </a>
                <a href="{{ route('standings.index', ['tournament' => $tournament->id]) }}" class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-bold text-slate-950 hover:bg-emerald-400 transition shadow-md">
                    📊 Ver Tabla
                </a>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-400">
            {{ session('status') }}
        </div>
    @endif

    <!-- Resumen de Reglas de Juego Activas -->
    @if ($tournament->rules)
        @php $r = $tournament->rules; @endphp
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">⚖️</span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-white">Reglamento Técnico del Torneo</h2>
                </div>
                @if (auth()->check() && (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $tournament->admin_id === auth()->id())))
                    <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="text-xs font-semibold text-emerald-400 hover:underline">
                        Modificar Reglas →
                    </a>
                @endif
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-xs">
                <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-3.5">
                    <span class="text-slate-400">Suspensión Amarillas</span>
                    <div class="mt-1 text-base font-bold text-amber-400">{{ $r->yellow_card_limit_for_suspension }} tarjetas</div>
                    <span class="text-[11px] text-slate-500">= 1 partido de inhabilitación</span>
                </div>

                <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-3.5">
                    <span class="text-slate-400">Criterio de Desempate</span>
                    <div class="mt-1 text-base font-bold text-emerald-400">{{ ucwords(str_replace('_', ' ', $r->tiebreaker_rule)) }}</div>
                    <span class="text-[11px] text-slate-500">Jerarquía oficial en tabla</span>
                </div>

                <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-3.5">
                    <span class="text-slate-400">Tiempo de Juego</span>
                    <div class="mt-1 text-base font-bold text-white">{{ $r->match_duration_minutes }} minutos</div>
                    <span class="text-[11px] text-slate-500">Duración reglamentaria</span>
                </div>

                <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-3.5">
                    <span class="text-slate-400">Sustituciones Máximas</span>
                    <div class="mt-1 text-base font-bold text-blue-400">{{ $r->max_substitutions }} cambios</div>
                    <span class="text-[11px] text-slate-500">Por equipo en cada partido</span>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-2">
        <!-- Equipos Inscritos -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white">Equipos Participantes ({{ $tournament->teams->count() }})</h2>
                @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
                    <a href="{{ route('teams.create') }}" class="text-xs font-semibold text-emerald-400 hover:underline">
                        + Registrar Equipo
                    </a>
                @endif
            </div>

            @if ($tournament->teams->isEmpty())
                <p class="mt-4 text-sm text-slate-400">No hay equipos registrados en este torneo aún.</p>
            @else
                <div class="mt-4 divide-y divide-slate-800">
                    @foreach ($tournament->teams as $team)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <a href="{{ route('teams.show', $team) }}" class="font-bold text-white hover:text-emerald-400 transition">
                                    {{ $team->name }}
                                </a>
                                <p class="text-xs text-slate-400">
                                    DT / Capitán: {{ $team->captain?->name ?? 'Pendiente de asignar (Magic Link)' }}
                                </p>
                            </div>
                            <a href="{{ route('teams.show', $team) }}" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-200 hover:bg-slate-700">
                                Ver Equipo
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Partidos del Torneo -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white">Fixture y Calendario ({{ $tournament->matches->count() }})</h2>
                <a href="{{ route('matches.index', ['search' => $tournament->name]) }}" class="text-xs font-semibold text-emerald-400 hover:underline">
                    Ver todos los partidos →
                </a>
            </div>

            @if ($tournament->matches->isEmpty())
                <p class="mt-4 text-sm text-slate-400">Aún no se han programado partidos para este torneo.</p>
            @else
                <div class="mt-4 divide-y divide-slate-800">
                    @foreach ($tournament->matches->take(6) as $match)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <div class="font-bold text-white">
                                    {{ $match->homeTeam->name }}
                                    @if ($match->status === 'played')
                                        <span class="text-emerald-400">({{ $match->home_score }} - {{ $match->away_score }})</span>
                                    @else
                                        <span class="text-slate-500">vs</span>
                                    @endif
                                    {{ $match->awayTeam->name }}
                                </div>
                                <p class="text-xs text-slate-400">
                                    {{ $match->match_date->format('d/m/Y H:i') }}
                                    @if ($match->venue) • 📍 {{ $match->venue->name }} @endif
                                </p>
                            </div>
                            <a href="{{ route('matches.show', $match) }}" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-200 hover:bg-slate-700">
                                Ver
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
