@extends('v1.layouts.app')

@section('title', 'Radar de Talentos & Transfer Market')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-bold text-cyan-400 border border-cyan-500/20">
                    <span class="size-1.5 rounded-full bg-cyan-400"></span>
                    MERCADO DE PASES LOCAL
                </span>
                <span class="text-xs text-slate-500">Agencia Libre & Reclutamiento</span>
            </div>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Radar de Talentos</h1>
            <p class="text-sm text-slate-400">Descubre jugadores libres con calificación algorítmica de rendimiento para reforzar el plantel de tu club.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-300">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->has('scouting'))
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-300">
            {{ $errors->first('scouting') }}
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 backdrop-blur-xl">
        <form method="GET" action="{{ route('scouting.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Posición</label>
                <select name="position" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2 text-xs font-semibold text-slate-200 focus:border-cyan-500 focus:outline-none">
                    <option value="">Todas las posiciones</option>
                    <option value="goalkeeper" {{ ($filters['position'] ?? '') === 'goalkeeper' ? 'selected' : '' }}>Portero</option>
                    <option value="defender" {{ ($filters['position'] ?? '') === 'defender' ? 'selected' : '' }}>Defensa</option>
                    <option value="midfielder" {{ ($filters['position'] ?? '') === 'midfielder' ? 'selected' : '' }}>Mediocampista</option>
                    <option value="forward" {{ ($filters['position'] ?? '') === 'forward' ? 'selected' : '' }}>Delantero</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pie Hábil</label>
                <select name="preferred_foot" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2 text-xs font-semibold text-slate-200 focus:border-cyan-500 focus:outline-none">
                    <option value="">Cualquier pie</option>
                    <option value="right" {{ ($filters['preferred_foot'] ?? '') === 'right' ? 'selected' : '' }}>Diestro</option>
                    <option value="left" {{ ($filters['preferred_foot'] ?? '') === 'left' ? 'selected' : '' }}>Zurdo</option>
                    <option value="ambidextrous" {{ ($filters['preferred_foot'] ?? '') === 'ambidextrous' ? 'selected' : '' }}>Ambidiestro</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Rating Mínimo</label>
                <select name="min_rating" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2 text-xs font-semibold text-slate-200 focus:border-cyan-500 focus:outline-none">
                    <option value="">Cualquier calificación</option>
                    <option value="6.0" {{ ($filters['min_rating'] ?? '') === '6.0' ? 'selected' : '' }}>6.0 o superior</option>
                    <option value="7.0" {{ ($filters['min_rating'] ?? '') === '7.0' ? 'selected' : '' }}>7.0 o superior ★</option>
                    <option value="8.0" {{ ($filters['min_rating'] ?? '') === '8.0' ? 'selected' : '' }}>8.0 o superior ★★</option>
                    <option value="9.0" {{ ($filters['min_rating'] ?? '') === '9.0' ? 'selected' : '' }}>9.0 Elite ★★★</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Buscar</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre o documento..." class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2 text-xs font-semibold text-slate-200 focus:border-cyan-500 focus:outline-none">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-cyan-500 py-2 text-xs font-bold text-slate-950 transition hover:bg-cyan-400 shadow-md shadow-cyan-500/10">
                    Filtrar Radar
                </button>
                <a href="{{ route('scouting.index') }}" class="rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs font-bold text-slate-400 hover:text-white transition">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Free Agents Grid -->
    @if($freeAgents->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/30 p-12 text-center">
            <div class="mx-auto size-14 rounded-2xl bg-cyan-500/10 text-cyan-400 grid place-items-center mb-3">
                <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h3 class="text-base font-bold text-white">No se encontraron agentes libres</h3>
            <p class="mt-1 text-sm text-slate-500">Prueba ajustando los filtros del radar o vuelve más tarde.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($freeAgents as $player)
                @php
                    $profile = $player->profile;
                    $rating = $profile?->performance_rating ?? 7.0;
                    $ratingColor = $rating >= 8.5 ? 'from-amber-500 to-yellow-400 text-slate-950' : ($rating >= 7.0 ? 'from-emerald-500 to-teal-400 text-slate-950' : 'from-slate-700 to-slate-600 text-white');
                @endphp
                <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg backdrop-blur-md transition-all duration-300 hover:border-cyan-500/40 hover:bg-slate-900/90 hover:shadow-cyan-500/5">
                    <div>
                        <!-- Top Badges -->
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-cyan-500/10 px-2.5 py-0.5 text-[11px] font-bold text-cyan-400 border border-cyan-500/20">
                                Agente Libre
                            </span>
                            <div class="flex items-center gap-1.5 rounded-lg bg-gradient-to-r {{ $ratingColor }} px-2.5 py-0.5 text-xs font-black shadow-sm">
                                <span>{{ number_format($rating, 1) }}</span>
                                <span class="text-[10px]">★</span>
                            </div>
                        </div>

                        <!-- Player Info -->
                        <div class="mt-4 flex items-center gap-3">
                            <div class="grid size-12 place-items-center rounded-xl bg-slate-800 border border-slate-700 text-base font-black text-white">
                                {{ mb_substr($player->name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white group-hover:text-cyan-300 transition">
                                    <a href="{{ route('scouting.show', $player) }}">{{ $player->name }}</a>
                                </h3>
                                <p class="text-xs text-slate-400">{{ $profile?->position_label ?? 'Mediocampista' }} • {{ $profile?->preferred_foot_label ?? 'Diestro' }}</p>
                            </div>
                        </div>

                        <!-- Stats Highlights -->
                        <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-slate-950/70 p-2.5 text-center border border-slate-850">
                            <div>
                                <span class="block text-xs font-bold text-slate-200">{{ $player->goalsCount() }}</span>
                                <span class="text-[10px] text-slate-500 uppercase">Goles</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-amber-400">{{ $profile?->mvp_awards_count ?? 0 }}</span>
                                <span class="text-[10px] text-slate-500 uppercase">MVPs</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-200">{{ $player->yellowCardsCount() }}</span>
                                <span class="text-[10px] text-slate-500 uppercase">Amarillas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-5 space-y-2 pt-3 border-t border-slate-800">
                        <a href="{{ route('scouting.show', $player) }}" class="block w-full text-center rounded-xl border border-slate-700 bg-slate-800/80 py-2 text-xs font-bold text-slate-300 transition hover:bg-slate-700">
                            Ver Radar Completo
                        </a>

                        @if($availableTeams->isNotEmpty())
                            <form method="POST" action="{{ route('scouting.recruit', $player) }}" class="space-y-1.5">
                                @csrf
                                <div class="flex items-center gap-1.5">
                                    <select name="team_id" required class="flex-1 rounded-xl border border-slate-800 bg-slate-950 px-2.5 py-1.5 text-xs text-slate-300 focus:border-cyan-500 focus:outline-none">
                                        @foreach($availableTeams as $t)
                                            <option value="{{ $t->id }}">Fichar en {{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 px-3 py-1.5 text-xs font-black text-slate-950 hover:from-emerald-400 transition" title="Fichar Jugador">
                                        Fichar
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $freeAgents->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
