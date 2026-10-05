@extends('v1.layouts.public')

@section('title', 'Calendario & Fixture Oficial')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Principal -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 mb-2 border border-emerald-300">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Fixture & Jornadas Oficiales</span>
            </div>
            <h1 class="font-display text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                Calendario de Partidos
            </h1>
            <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                Consulta los encuentros programados, resultados oficiales en tiempo real y el fixture completo de cada torneo.
            </p>
        </div>

        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <div class="shrink-0">
                    <a href="{{ route('admin.matches.index') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 shadow-sm transition">
                        <svg class="size-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Panel Admin de Partidos</span>
                    </a>
                </div>
            @endif
        @endauth
    </div>

    <!-- Filtros de Navegación -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs">
        <form method="GET" action="{{ route('matches.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <!-- Torneo -->
            <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Torneo</label>
                <select name="tournament_id" class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#057a55] outline-none transition" onchange="this.form.submit()">
                    <option value="">Todos los Torneos</option>
                    @foreach($tournaments as $t)
                        <option value="{{ $t->id }}" @selected(($selectedTournamentId ?? '') == $t->id)>
                            {{ $t->name }} ({{ $t->sport_type }}){{ $t->is_featured ? ' ⭐ (Principal)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Estado -->
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Estado del Encuentro</label>
                <select name="status" class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#057a55] outline-none transition" onchange="this.form.submit()">
                    <option value="">Todos los Estados</option>
                    <option value="scheduled" @selected(($filters['status'] ?? '') === 'scheduled')>📅 Programados</option>
                    <option value="live" @selected(($filters['status'] ?? '') === 'live')>🔴 En Vivo</option>
                    <option value="played" @selected(($filters['status'] ?? '') === 'played')>✅ Finalizados</option>
                    <option value="suspended" @selected(($filters['status'] ?? '') === 'suspended')>⚠️ Suspendidos</option>
                </select>
            </div>

            <!-- Búsqueda -->
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Buscar por Equipo</label>
                <input type="text" 
                       name="search" 
                       value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Ej: Nacional, Millonarios..." 
                       class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#057a55] outline-none transition">
            </div>

            <!-- Botones -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" 
                        class="w-full rounded-xl bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs font-bold py-2.5 px-4 shadow-2xs transition text-center">
                    Filtrar
                </button>
                @if(!empty($filters['search']) || !empty($filters['status']) || !empty($selectedTournamentId))
                    <a href="{{ route('matches.index') }}" 
                       class="p-2.5 rounded-xl border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition" 
                       title="Limpiar filtros">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Píldoras de Acceso Rápido por Estado -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin text-xs">
        <a href="{{ route('matches.index') }}" 
           class="px-4 py-2 rounded-full font-bold whitespace-nowrap transition {{ empty($filters['status']) ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Todos ({{ $totalMatchesCount ?? $matches->total() }})
        </a>
        <a href="{{ route('matches.index', array_merge(request()->except('page'), ['status' => 'live'])) }}" 
           class="px-4 py-2 rounded-full font-bold whitespace-nowrap transition flex items-center gap-1.5 {{ ($filters['status'] ?? '') === 'live' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200' }}">
            <span class="size-2 rounded-full bg-rose-500 animate-pulse"></span>
            <span>En Vivo ({{ $liveMatchesCount ?? 0 }})</span>
        </a>
        <a href="{{ route('matches.index', array_merge(request()->except('page'), ['status' => 'scheduled'])) }}" 
           class="px-4 py-2 rounded-full font-bold whitespace-nowrap transition {{ ($filters['status'] ?? '') === 'scheduled' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-blue-700 hover:bg-blue-50 border border-blue-200' }}">
            Programados ({{ $scheduledMatchesCount ?? 0 }})
        </a>
        <a href="{{ route('matches.index', array_merge(request()->except('page'), ['status' => 'played'])) }}" 
           class="px-4 py-2 rounded-full font-bold whitespace-nowrap transition {{ ($filters['status'] ?? '') === 'played' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-emerald-800 hover:bg-emerald-50 border border-emerald-200' }}">
            Finalizados ({{ $playedMatchesCount ?? 0 }})
        </a>
    </div>

    <!-- Cuadrícula de Partidos Públicos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($matches as $match)
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between group">
                
                <!-- Encabezado de la Tarjeta: Torneo, Fecha y Estado -->
                <div class="p-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600 truncate max-w-[170px]" title="{{ $match->tournament?->name }}">
                        🏆 {{ $match->tournament?->name ?? 'Torneo' }}
                    </span>
                    
                    <!-- Badge de Estado -->
                    @if($match->status === 'live' || $match->is_timer_running)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                            <span class="size-1.5 rounded-full bg-rose-600"></span>
                            <span>EN VIVO {{ $match->formatted_clock ? '• ' . $match->formatted_clock : '' }}</span>
                        </span>
                    @elseif($match->status === 'played')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <span>Finalizado</span>
                        </span>
                    @elseif($match->status === 'suspended')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            <span>Suspendido</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-200/70 text-slate-700">
                            <span>Programado</span>
                        </span>
                    @endif
                </div>

                <!-- Cuerpo Central: Equipos & Marcador -->
                <div class="p-5 flex items-center justify-between gap-4">
                    
                    <!-- Equipo Local -->
                    <div class="flex-1 flex flex-col items-center text-center">
                        <div class="size-13 rounded-2xl bg-emerald-50 border border-emerald-200 text-[#057a55] font-black text-sm flex items-center justify-center shadow-2xs group-hover:scale-105 transition">
                            {{ strtoupper(substr($match->homeTeam?->name ?? 'LOC', 0, 2)) }}
                        </div>
                        <h3 class="mt-2 text-xs font-bold text-slate-900 line-clamp-1 max-w-[120px]" title="{{ $match->homeTeam?->name }}">
                            {{ $match->homeTeam?->name ?? 'Equipo Local' }}
                        </h3>
                        <span class="text-[10px] text-slate-400 font-medium">Local</span>
                    </div>

                    <!-- Marcador / VS Central -->
                    <div class="shrink-0 flex flex-col items-center">
                        @if($match->status === 'played' || $match->status === 'live' || $match->is_timer_running)
                            <div class="flex items-center gap-2 bg-slate-900 text-white font-mono text-2xl font-black px-3.5 py-1.5 rounded-xl shadow-xs">
                                <span>{{ $match->home_score ?? 0 }}</span>
                                <span class="text-slate-500 font-sans text-sm">-</span>
                                <span>{{ $match->away_score ?? 0 }}</span>
                            </div>
                        @else
                            <div class="text-lg font-black text-slate-300 font-display">VS</div>
                        @endif
                        <span class="mt-1 text-[11px] text-slate-500 font-mono font-medium">
                            {{ $match->match_date ? $match->match_date->format('d/m • H:i') : 'Por definir' }}
                        </span>
                    </div>

                    <!-- Equipo Visitante -->
                    <div class="flex-1 flex flex-col items-center text-center">
                        <div class="size-13 rounded-2xl bg-blue-50 border border-blue-200 text-blue-700 font-black text-sm flex items-center justify-center shadow-2xs group-hover:scale-105 transition">
                            {{ strtoupper(substr($match->awayTeam?->name ?? 'VIS', 0, 2)) }}
                        </div>
                        <h3 class="mt-2 text-xs font-bold text-slate-900 line-clamp-1 max-w-[120px]" title="{{ $match->awayTeam?->name }}">
                            {{ $match->awayTeam?->name ?? 'Equipo Visita' }}
                        </h3>
                        <span class="text-[10px] text-slate-400 font-medium">Visitante</span>
                    </div>
                </div>

                <!-- Detalles de Sede, Árbitro y Enlaces -->
                <div class="px-5 pb-5 pt-3 border-t border-slate-100 flex flex-col gap-3">
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span class="flex items-center gap-1 truncate max-w-[180px]">
                            <svg class="size-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $match->venue?->name ?? 'Sede Principal' }}</span>
                        </span>
                        @if($match->referee)
                            <span class="truncate text-slate-400" title="{{ $match->referee->name }}">
                                👤 {{ $match->referee->name }}
                            </span>
                        @endif
                    </div>

                    <!-- Botón a la Ficha del Partido -->
                    <a href="{{ route('matches.show', $match) }}" 
                       class="w-full rounded-xl bg-slate-50 hover:bg-[#057a55] text-slate-700 hover:text-white px-4 py-2.5 text-xs font-bold transition flex items-center justify-center gap-1.5 border border-slate-200 hover:border-[#057a55] shadow-2xs">
                        <span>Ver Ficha de Partido & Estadísticas</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
                <span class="text-4xl block mb-2">⚽</span>
                <h3 class="text-base font-bold text-slate-800">No se encontraron partidos</h3>
                <p class="text-xs text-slate-500 mt-1">Intenta ajustando los filtros de torneo, estado o búsqueda.</p>
                <div class="mt-4">
                    <a href="{{ route('matches.index') }}" class="inline-block text-xs font-bold text-[#057a55] hover:underline">
                        Limpiar todos los filtros
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if($matches->hasPages())
        <div class="pt-4 flex justify-center">
            {{ $matches->links() }}
        </div>
    @endif

</div>
@endsection
