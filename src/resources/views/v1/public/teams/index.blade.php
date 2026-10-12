@extends('v1.layouts.public')

@section('title', 'Clubes & Equipos Participantes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Principal -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 mb-2 border border-emerald-300">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                <span>Directorio Oficial de Clubes</span>
            </div>
            <h1 class="font-display text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                Equipos en Competencia
            </h1>
            <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                Conoce a todos los clubes registrados en la plataforma, sus planteles oficiales, directores técnicos e historial de rendimiento.
            </p>
        </div>

        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <div class="shrink-0">
                    <a href="{{ route('admin.teams.index') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 shadow-sm transition">
                        <svg class="size-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Panel Admin de Equipos</span>
                    </a>
                </div>
            @endif
        @endauth
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs">
        <form method="GET" action="{{ route('teams.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-end">
            <!-- Torneo -->
            <div class="sm:col-span-5">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Filtrar por Torneo</label>
                <select name="tournament_id" class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#2b4e72] outline-none transition" onchange="this.form.submit()">
                    <option value="">Todos los Torneos</option>
                    @foreach($tournaments as $t)
                        <option value="{{ $t->id }}" @selected(($tournamentId ?? '') == $t->id)>
                            {{ $t->name }}{{ $t->is_featured ? ' (Principal)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Búsqueda -->
            <div class="sm:col-span-5">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Buscar por Nombre o Director Técnico</label>
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Ej: América, Ancelotti, Millonarios..." 
                       class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#2b4e72] outline-none transition">
            </div>

            <!-- Botones -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" 
                        class="w-full rounded-xl bg-[#2b4e72] hover:bg-[#2790b0] active:bg-[#1e3852] text-white text-xs font-bold py-2.5 px-4 shadow-2xs transition text-center">
                    Buscar
                </button>
                @if(!empty($search) || !empty($tournamentId))
                    <a href="{{ route('teams.index') }}" 
                       class="p-2.5 rounded-xl border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition" 
                       title="Limpiar búsqueda">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Cuadrícula de Equipos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($teams as $team)
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between group">
                
                <!-- Encabezado de la Tarjeta con Torneo -->
                <div class="p-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-500 truncate max-w-[150px] inline-flex items-center gap-1.5" title="{{ $team->tournament?->name }}">
                        <x-reicon name="trophy" class="size-3.5 text-emerald-600 shrink-0" />
                        <span class="truncate">{{ $team->tournament?->name ?? 'Torneo' }}</span>
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                        {{ $team->status === 'active' || $team->status === 'approved' ? 'ACTIVO' : ($team->status === 'completed' ? 'FINALIZADO' : 'PENDIENTE') }}
                    </span>
                </div>

                <!-- Escudo y Nombre Central -->
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="size-20 rounded-2xl bg-emerald-50 border border-emerald-200 text-[#2b4e72] font-black text-2xl flex items-center justify-center shadow-2xs group-hover:scale-105 transition">
                        <x-reicon name="shield-check" class="size-9 text-[#2b4e72]" />
                    </div>
                    <h3 class="mt-3.5 font-display text-lg font-bold text-slate-900 group-hover:text-[#2b4e72] transition leading-snug">
                        {{ $team->name }}
                    </h3>
                    
                    <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                        <span class="font-semibold text-slate-700">DT:</span>
                        <span>{{ $team->captain?->name ?? 'Por confirmar' }}</span>
                    </div>

                    <!-- Métricas Rápidas -->
                    <div class="mt-5 w-full pt-4 border-t border-slate-100 grid grid-cols-2 gap-2 text-center text-xs">
                        <div class="p-1.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-mono font-black text-sm text-slate-900">{{ $team->players_count ?? $team->players->count() }}</span>
                            <span class="block text-[10px] text-slate-400 font-semibold uppercase">Jugadores</span>
                        </div>
                        <div class="p-1.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-mono font-black text-sm text-emerald-700">{{ $team->standing?->points ?? 0 }}</span>
                            <span class="block text-[10px] text-slate-400 font-semibold uppercase">Puntos</span>
                        </div>
                    </div>
                </div>

                <!-- Botón de Acción -->
                <div class="p-4 pt-0">
                    <a href="{{ route('teams.show', $team) }}" 
                       class="w-full rounded-xl bg-slate-50 hover:bg-[#2b4e72] text-slate-700 hover:text-white px-4 py-2.5 text-xs font-bold transition flex items-center justify-center gap-1.5 border border-slate-200 hover:border-[#2b4e72] shadow-2xs">
                        <span>Ver Plantel & Perfil</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
                <x-reicon name="shield-check" class="size-10 text-slate-300 mx-auto block mb-2" />
                <h3 class="text-base font-bold text-slate-800">No se encontraron equipos</h3>
                <p class="text-xs text-slate-500 mt-1">Ajusta los criterios de búsqueda o revisa los torneos activos.</p>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if($teams->hasPages())
        <div class="pt-4 flex justify-center">
            {{ $teams->links() }}
        </div>
    @endif

</div>
@endsection
