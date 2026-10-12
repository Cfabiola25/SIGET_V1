@extends('v1.layouts.app')

@section('title', 'Gestión de Jugadores')
@section('header_title', 'Gestión de Jugadores')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
                Gestión de Jugadores
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">
                Administra todos los jugadores inscritos en los equipos activos.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Exportar CSV -->
            <a href="{{ route('players.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs md:text-sm font-semibold hover:bg-slate-50 shadow-2xs transition">
                <svg class="size-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Exportar CSV</span>
            </a>

            <!-- Botón Agregar Nuevo Jugador -->
            <button type="button" onclick="document.getElementById('add-player-modal').showModal()" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#2b4e72] hover:bg-[#2790b0] active:bg-[#1e3852] text-white text-xs md:text-sm font-bold shadow-xs transition cursor-pointer">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Agregar Jugador</span>
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5">
        <form method="GET" action="{{ route('players.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            
            <!-- Search Input -->
            <div class="lg:col-span-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Buscar Jugadores</label>
                <div class="relative">
                    <svg class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por nombre o ID..." 
                           class="w-full pl-9 pr-3 py-2 text-xs md:text-sm bg-white border border-slate-300 rounded-lg focus:border-emerald-600 transition">
                </div>
            </div>

            <!-- Filtrar por Equipo -->
            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Filtrar por Equipo</label>
                <select name="team_id" class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="">Todos los Equipos</option>
                    @foreach ($teams as $t)
                        <option value="{{ $t->id }}" @selected(($teamId ?? '') == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtrar por Posición -->
            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Filtrar por Posición</label>
                <select name="position" class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="">Todas las Posiciones</option>
                    <option value="forward" @selected(in_array(($position ?? ''), ['forward', 'FW']))>Delantero (DEL)</option>
                    <option value="midfielder" @selected(in_array(($position ?? ''), ['midfielder', 'MF']))>Mediocampista (MED)</option>
                    <option value="defender" @selected(in_array(($position ?? ''), ['defender', 'DF']))>Defensa (DEF)</option>
                    <option value="goalkeeper" @selected(in_array(($position ?? ''), ['goalkeeper', 'GK']))>Portero (POR)</option>
                </select>
            </div>

            <!-- Apply Button -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-3 bg-sky-100 hover:bg-sky-200 text-sky-900 text-xs md:text-sm font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-2xs">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Aplicar Filtros</span>
                </button>
                @if ($search || $teamId || $position)
                    <a href="{{ route('players.index') }}" class="p-2 text-slate-400 hover:text-slate-700" title="Limpiar">
                        &times;
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Players Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-[#edf2f9] text-slate-700 text-xs font-semibold">
                        <th class="py-3.5 px-6">JUGADOR</th>
                        <th class="py-3.5 px-6">EQUIPO</th>
                        <th class="py-3.5 px-6 text-center">DORSAL</th>
                        <th class="py-3.5 px-6 text-center">POSICIÓN</th>
                        <th class="py-3.5 px-6 text-center">ESTADO</th>
                        <th class="py-3.5 px-6 text-right">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($players as $player)
                        @php
                            $playerIdCode = 'PLY-' . str_pad($player->id, 3, '0', STR_PAD_LEFT);
                            $pos = $player->profile?->position ?? 'forward';
                            $posLabel = match(strtolower($pos)) {
                                'forward', 'fw', 'delantero', 'pivot' => 'DEL',
                                'midfielder', 'mf', 'mediocampista', 'volante' => 'MED',
                                'defender', 'df', 'defensa', 'cierre' => 'DEF',
                                'goalkeeper', 'gk', 'portero', 'arquero' => 'POR',
                                'ala', 'lateral' => 'ALA',
                                default => strtoupper(substr($pos, 0, 3)),
                            };
                            $isSuspended = $player->sanctions->isNotEmpty();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            
                            <!-- Player Name & Avatar -->
                            <td class="py-3.5 px-6 font-semibold text-slate-900">
                                <div class="flex items-center gap-3.5">
                                    <div class="size-9 rounded-full bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-300">
                                        {{ strtoupper(substr($player->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">{{ $player->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-normal">ID: {{ $playerIdCode }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Team -->
                            <td class="py-3.5 px-6 text-slate-600 font-medium">
                                {{ $player->team?->name ?? 'Agente Libre' }}
                            </td>

                            <!-- Number -->
                            <td class="py-3.5 px-6 text-center font-bold text-slate-800">
                                {{ $player->jersey_number ?? 10 }}
                            </td>

                            <!-- Position -->
                            <td class="py-3.5 px-6 text-center">
                                <span class="inline-block bg-slate-100 text-slate-700 text-xs font-bold px-2.5 py-0.5 rounded">
                                    {{ $posLabel }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-6 text-center">
                                @if ($isSuspended)
                                    <span class="inline-block bg-[#fee2e2] text-[#991b1b] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
                                        SUSPENDIDO
                                    </span>
                                @else
                                    <span class="inline-block bg-[#eaf3de] text-[#42651b] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
                                        ACTIVO
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-3 text-slate-400">
                                    <a href="{{ route('players.cromo', $player) }}" class="p-1 hover:text-emerald-700 transition" title="Ficha deportiva">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('players.destroy', $player) }}" onsubmit="return confirm('¿Eliminar jugador de la lista?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 hover:text-rose-600 transition cursor-pointer" title="Eliminar">
                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="size-10 stroke-1 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-600">No se encontraron jugadores</p>
                                    <p class="text-xs text-slate-400 mt-1">Prueba ajustando los filtros de búsqueda o registra un nuevo jugador con el botón superior.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <div>
                @if($players instanceof \Illuminate\Pagination\LengthAwarePaginator && $players->total() > 0)
                    Mostrando {{ $players->firstItem() }} a {{ $players->lastItem() }} de {{ $players->total() }} jugadores
                @elseif($players instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    0 jugadores encontrados
                @else
                    Total: {{ count($players) }} jugadores
                @endif
            </div>

            <div>
                @if($players instanceof \Illuminate\Pagination\LengthAwarePaginator && $players->hasPages())
                    {{ $players->links('pagination::tailwind') }}
                @endif
            </div>
        </div>

    </div>

</div>

<!-- Modal: Registrar Nuevo Jugador -->
<x-modal id="add-player-modal" title="Registrar Nuevo Jugador">
    <form method="POST" action="{{ route('players.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre Completo *</label>
            <input type="text" name="name" required placeholder="Ej: Alex Mercer" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Equipo *</label>
                <select name="team_id" required class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="">Seleccionar Equipo...</option>
                    @foreach ($teams as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Número de Dorsal</label>
                <input type="number" name="jersey_number" min="1" max="99" value="10" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Posición de Juego</label>
                <select name="position" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="forward">Delantero (DEL)</option>
                    <option value="midfielder">Mediocampista (MED)</option>
                    <option value="defender">Defensa (DEF)</option>
                    <option value="goalkeeper">Portero (POR)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Documento de Identidad (DNI)</label>
                <input type="text" name="identification_document" placeholder="1020304050" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <x-button variant="secondary" onclick="document.getElementById('add-player-modal').close()">Cancelar</x-button>
            <x-button type="submit">Guardar Jugador</x-button>
        </div>
    </form>
</x-modal>
@endsection
