@extends('v1.layouts.app')

@section('title', 'Radar de Talentos & Transfer Market')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-50 px-2.5 py-0.5 text-xs font-bold text-cyan-800 border border-cyan-200">
                    <span class="size-1.5 rounded-full bg-cyan-600"></span>
                    MERCADO DE PASES LOCAL
                </span>
                <span class="text-xs text-slate-400 font-medium">Agencia Libre & Reclutamiento</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Radar de Talentos</h1>
            <p class="text-xs text-slate-500">Descubre jugadores libres con calificación algorítmica para reforzar el plantel de tu club.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->has('scouting'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first('scouting') }}
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
        <form method="GET" action="{{ route('scouting.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Posición</label>
                <select name="position" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 focus:border-[#057a55] focus:outline-none">
                    <option value="">Todas las posiciones</option>
                    <option value="goalkeeper" {{ ($filters['position'] ?? '') === 'goalkeeper' ? 'selected' : '' }}>Portero</option>
                    <option value="defender" {{ ($filters['position'] ?? '') === 'defender' ? 'selected' : '' }}>Defensa</option>
                    <option value="midfielder" {{ ($filters['position'] ?? '') === 'midfielder' ? 'selected' : '' }}>Mediocampista</option>
                    <option value="forward" {{ ($filters['position'] ?? '') === 'forward' ? 'selected' : '' }}>Delantero</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pie Hábil</label>
                <select name="preferred_foot" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 focus:border-[#057a55] focus:outline-none">
                    <option value="">Cualquier pie</option>
                    <option value="right" {{ ($filters['preferred_foot'] ?? '') === 'right' ? 'selected' : '' }}>Diestro</option>
                    <option value="left" {{ ($filters['preferred_foot'] ?? '') === 'left' ? 'selected' : '' }}>Zurdo</option>
                    <option value="ambidextrous" {{ ($filters['preferred_foot'] ?? '') === 'ambidextrous' ? 'selected' : '' }}>Ambidiestro</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Rating Mínimo</label>
                <select name="min_rating" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 focus:border-[#057a55] focus:outline-none">
                    <option value="">Cualquier calificación</option>
                    <option value="6.0" {{ ($filters['min_rating'] ?? '') === '6.0' ? 'selected' : '' }}>6.0 o superior</option>
                    <option value="7.0" {{ ($filters['min_rating'] ?? '') === '7.0' ? 'selected' : '' }}>7.0 o superior ★</option>
                    <option value="8.0" {{ ($filters['min_rating'] ?? '') === '8.0' ? 'selected' : '' }}>8.0 o superior ★★</option>
                    <option value="9.0" {{ ($filters['min_rating'] ?? '') === '9.0' ? 'selected' : '' }}>9.0 Elite ★★★</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Buscar</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre o documento..." class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none placeholder:text-slate-400">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-lg bg-[#057a55] py-2 text-xs font-semibold text-white transition hover:bg-[#046c4b] shadow-2xs">
                    Filtrar Radar
                </button>
                <a href="{{ route('scouting.index') }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Free Agents Grid -->
    @if($freeAgents->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center shadow-2xs">
            <div class="mx-auto size-12 rounded-xl bg-cyan-50 text-cyan-700 grid place-items-center mb-3">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">No se encontraron agentes libres</h3>
            <p class="mt-1 text-xs text-slate-500">Prueba ajustando los filtros del radar o vuelve más tarde.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($freeAgents as $player)
                @php
                    $profile = $player->profile;
                    $rating = $profile?->performance_rating ?? 7.0;
                @endphp
                <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs hover:shadow-xs transition">
                    <div>
                        <!-- Top Badges -->
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-cyan-50 px-2.5 py-0.5 text-[10px] font-bold text-cyan-800 border border-cyan-200">
                                Agente Libre
                            </span>
                            <div class="flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 border border-amber-200">
                                <span>{{ number_format($rating, 1) }}</span>
                                <span class="text-[10px]">★</span>
                            </div>
                        </div>

                        <!-- Player Info -->
                        <div class="mt-4 flex items-center gap-3">
                            <div class="grid size-11 place-items-center rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700">
                                {{ mb_substr($player->name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 hover:text-[#057a55] transition">
                                    <a href="{{ route('scouting.show', $player) }}">{{ $player->name }}</a>
                                </h3>
                                <p class="text-[11px] text-slate-500">{{ $profile?->position_label ?? 'Mediocampista' }} • {{ $profile?->preferred_foot_label ?? 'Diestro' }}</p>
                            </div>
                        </div>

                        <!-- Stats Highlights -->
                        <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-2.5 text-center border border-slate-100">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">{{ $player->goalsCount() }}</span>
                                <span class="text-[10px] text-slate-400">Goles</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">{{ $player->matchesCount() }}</span>
                                <span class="text-[10px] text-slate-400">PJ</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">{{ $profile?->calculated_market_value ? '$' . number_format($profile->calculated_market_value) : '$1.2M' }}</span>
                                <span class="text-[10px] text-slate-400">Valor</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <a href="{{ route('scouting.show', $player) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                            Ver Perfil
                        </a>
                        <form method="POST" action="{{ route('scouting.recruit', $player) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-[#057a55] px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-[#046c4b] transition shadow-2xs">
                                Reclutar
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $freeAgents->links() }}
        </div>
    @endif
</div>
@endsection
