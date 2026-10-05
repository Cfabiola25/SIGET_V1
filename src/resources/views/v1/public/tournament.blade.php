@extends('v1.layouts.public')

@section('title', $tournament->name . ' | Torneo Oficial')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Hero / Tournament Header -->
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-[#182232] to-[#0f172a] text-white p-6 sm:p-10 shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <x-reicon name="trophy" class="size-72 text-white" />
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <span class="size-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>{{ $tournament->sport_type }}</span>
                    </span>
                    <span class="text-xs text-slate-400 font-mono">{{ $tournament->teams->count() }} Equipos Inscritos</span>
                </div>
                <h1 class="font-display text-3xl sm:text-5xl font-black tracking-tight text-white">
                    {{ $tournament->name }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Sigue el calendario completo, resultados en vivo y la tabla oficial de posiciones en tiempo real.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <span class="rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider {{ $tournament->status === 'active' ? 'bg-[#057a55] text-white' : ($tournament->status === 'completed' ? 'bg-slate-700 text-slate-200' : 'bg-amber-600 text-white') }}">
                    {{ $tournament->status === 'active' ? 'Torneo Activo' : ($tournament->status === 'completed' ? 'Finalizado' : 'Próximamente') }}
                </span>
                <a href="{{ route('standings.index', ['tournament_id' => $tournament->id]) }}" class="inline-flex items-center gap-2 rounded-xl bg-white text-slate-900 px-4 py-2 text-xs font-bold shadow-sm hover:bg-slate-100 transition">
                    <span>Ver Posiciones</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Fixture (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Calendario & Partidos</h2>
                    <p class="text-xs text-slate-500">Encuentros oficiales programados y resultados finales.</p>
                </div>
                <a href="{{ route('matches.index', ['tournament_id' => $tournament->id]) }}" class="text-xs font-bold text-[#057a55] hover:underline">
                    Ver todos &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($tournament->matches->sortBy('match_date') as $match)
                    <div class="rounded-2xl border border-slate-200/90 bg-white p-4 sm:p-5 shadow-2xs hover:border-slate-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <span class="font-mono font-semibold text-slate-600">
                                    {{ $match->match_date?->format('d/m/Y · H:i') ?? 'Por definir' }}
                                </span>
                                @if($match->venue)
                                    <span>•</span>
                                    <span class="truncate">{{ $match->venue->name }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                                <span class="truncate">{{ $match->homeTeam?->name ?? 'Por definir' }}</span>
                                <span class="text-xs font-normal text-slate-400 px-1">vs</span>
                                <span class="truncate">{{ $match->awayTeam?->name ?? 'Por definir' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-end sm:self-center">
                            @if(in_array($match->status, ['played', 'in_progress'], true))
                                <div class="font-mono font-black text-base sm:text-lg px-3 py-1 rounded-lg bg-slate-100 text-slate-900 border border-slate-200">
                                    {{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}
                                </div>
                            @else
                                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                                    Programado
                                </span>
                            @endif
                            <a href="{{ route('matches.show', $match) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                Ver
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center text-xs text-slate-400">
                        El fixture aún no ha sido publicado para este torneo.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Standings (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Tabla de Clasificación</h2>
                    <p class="text-xs text-slate-500">Puntajes al día del torneo.</p>
                </div>
                <a href="{{ route('standings.index', ['tournament_id' => $tournament->id]) }}" class="text-xs font-bold text-[#057a55] hover:underline">
                    Tabla completa &rarr;
                </a>
            </div>

            <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3 text-center w-8">#</th>
                            <th class="py-3 px-3">Equipo</th>
                            <th class="py-3 px-2 text-center" title="Partidos Jugados">PJ</th>
                            <th class="py-3 px-2 text-center" title="Diferencia de Goles">DG</th>
                            <th class="py-3 px-3 text-center font-black text-slate-900">PTS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tournament->standings->sortByDesc('points') as $standing)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                <td class="py-3 px-3 font-semibold text-slate-900 truncate max-w-[140px]">
                                    {{ $standing->team?->name ?? 'Equipo' }}
                                </td>
                                <td class="py-3 px-2 text-center text-slate-600 font-mono">{{ $standing->matches_played }}</td>
                                <td class="py-3 px-2 text-center font-mono font-medium {{ ($standing->goals_for - $standing->goals_against) > 0 ? 'text-emerald-700' : '' }}">
                                    {{ $standing->goals_for - $standing->goals_against }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-extrabold text-[#057a55]">
                                    {{ $standing->points }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                    La tabla se actualizará al disputarse los partidos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Teams in Tournament -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Clubes Participantes ({{ $tournament->teams->count() }})</h3>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($tournament->teams as $team)
                        <a href="{{ route('teams.show', $team) }}" class="p-2.5 rounded-xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/30 transition text-xs font-bold text-slate-800 flex items-center gap-2 truncate">
                            <span class="size-6 rounded-lg bg-emerald-50 text-[#057a55] font-black text-[10px] flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($team->name, 0, 2)) }}
                            </span>
                            <span class="truncate">{{ $team->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
