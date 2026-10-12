@extends('v1.layouts.public')

@section('title', 'Radar de Talentos & Scouting')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Principal -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-cyan-100 text-cyan-900 text-xs font-bold px-3 py-1 mb-2 border border-cyan-300">
                <span class="size-2 rounded-full bg-cyan-600"></span>
                <span>Bolsa de Talentos & Agentes Libres</span>
            </div>
            <h1 class="font-display text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                Radar de Talentos
            </h1>
            <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                Explora el mercado de jugadores libres con calificación algorítmica, métricas de rendimiento y fichas cromográficas.
            </p>
        </div>

        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isCoach())
                <div class="shrink-0">
                    <a href="{{ route('admin.scouting.index') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 shadow-sm transition">
                        <svg class="size-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Panel de Fichajes (Admin/DT)</span>
                    </a>
                </div>
            @endif
        @endauth
    </div>

    <!-- Barra de Filtros de Scouting -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs">
        <form method="GET" action="{{ route('scouting.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <!-- Posición -->
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Posición</label>
                <select name="position" class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#1e3a8a] outline-none transition" onchange="this.form.submit()">
                    <option value="">Todas las Posiciones</option>
                    <option value="goalkeeper" @selected(($filters['position'] ?? '') === 'goalkeeper')>Portero</option>
                    <option value="defender" @selected(($filters['position'] ?? '') === 'defender')>Defensa</option>
                    <option value="midfielder" @selected(($filters['position'] ?? '') === 'midfielder')>Mediocampista</option>
                    <option value="forward" @selected(($filters['position'] ?? '') === 'forward')>Delantero</option>
                </select>
            </div>

            <!-- Pierna Hábil -->
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pierna Hábil</label>
                <select name="preferred_foot" class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#1e3a8a] outline-none transition" onchange="this.form.submit()">
                    <option value="">Cualquier perfil</option>
                    <option value="right" @selected(($filters['preferred_foot'] ?? '') === 'right')>Diestro</option>
                    <option value="left" @selected(($filters['preferred_foot'] ?? '') === 'left')>Zurdo</option>
                    <option value="ambidextrous" @selected(($filters['preferred_foot'] ?? '') === 'ambidextrous')>Ambidiestro</option>
                </select>
            </div>

            <!-- Calificación Mínima -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Rating Mínimo</label>
                <select name="min_rating" class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#1e3a8a] outline-none transition" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="8.0" @selected(($filters['min_rating'] ?? '') == '8.0')>8.0 o más</option>
                    <option value="8.5" @selected(($filters['min_rating'] ?? '') == '8.5')>8.5 o más</option>
                    <option value="9.0" @selected(($filters['min_rating'] ?? '') == '9.0')>9.0 o más</option>
                </select>
            </div>

            <!-- Búsqueda -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nombre</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar talento..." 
                       class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#1e3a8a] outline-none transition">
            </div>

            <!-- Botones -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" 
                        class="w-full rounded-xl bg-[#1e3a8a] hover:bg-[#0284c7] active:bg-[#172554] text-white text-xs font-bold py-2.5 px-4 shadow-2xs transition text-center">
                    Explorar
                </button>
                @if(!empty($filters))
                    <a href="{{ route('scouting.index') }}" 
                       class="p-2.5 rounded-xl border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition" 
                       title="Limpiar filtros">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Cuadrícula de Jugadores -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($freeAgents as $agent)
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between group">
                
                <!-- Encabezado con Posición y Rating -->
                <div class="p-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-600">
                        <x-reicon name="football" class="size-3.5 text-slate-500" />
                        <span>{{ $agent->profile?->position_label ?? ucfirst($agent->position) }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300">
                        <x-reicon name="star" variant="filled" class="size-3 text-amber-500" />
                        <span>{{ number_format($agent->profile?->performance_rating ?? $agent->profile?->scouting_rating ?? 8.0, 1) }}</span>
                    </span>
                </div>

                <!-- Foto / Avatar y Nombre -->
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="size-20 rounded-2xl bg-slate-100 border border-slate-200 text-slate-700 font-black text-2xl flex items-center justify-center shadow-2xs group-hover:scale-105 transition">
                        {{ strtoupper(substr($agent->name, 0, 2)) }}
                    </div>

                    <h3 class="mt-3.5 font-display text-base font-bold text-slate-900 group-hover:text-[#1e3a8a] transition line-clamp-1">
                        {{ $agent->name }}
                    </h3>

                    <div class="mt-1 text-xs text-slate-500">
                        {{ $agent->profile?->preferred_foot_label ?? 'Diestro' }} • {{ $agent->profile?->age ?? '24' }} años
                    </div>

                    @if($agent->profile?->bio)
                        <p class="mt-3 text-xs text-slate-400 italic line-clamp-2">
                            "{{ $agent->profile->bio }}"
                        </p>
                    @endif

                    <!-- Estado Agente Libre -->
                    <div class="mt-4 pt-3 border-t border-slate-100 w-full flex items-center justify-center gap-1.5 text-xs text-emerald-700 font-bold">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        <span>Agente Libre Disponible</span>
                    </div>
                </div>

                <!-- Botón de Ficha de Scouting -->
                <div class="p-4 pt-0">
                    <a href="{{ route('scouting.show', $agent) }}" 
                       class="w-full rounded-xl bg-slate-50 hover:bg-[#1e3a8a] text-slate-700 hover:text-white px-4 py-2.5 text-xs font-bold transition flex items-center justify-center gap-1.5 border border-slate-200 hover:border-[#1e3a8a] shadow-2xs">
                        <span>Ver Radar & Ficha Técnica</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
                <x-reicon name="search" class="size-10 text-slate-300 mx-auto block mb-2" />
                <h3 class="text-base font-bold text-slate-800">No se encontraron futbolistas libres</h3>
                <p class="text-xs text-slate-500 mt-1">Modifica los filtros de posición o calificación.</p>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if($freeAgents->hasPages())
        <div class="pt-4 flex justify-center">
            {{ $freeAgents->links() }}
        </div>
    @endif

</div>
@endsection
