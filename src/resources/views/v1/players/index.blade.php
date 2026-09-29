@extends('v1.layouts.app')

@section('title', 'Player Management')
@section('header_title', 'Player Management')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
                Player Management
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">
                Manage all registered players across active teams.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Export CSV -->
            <a href="{{ route('players.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs md:text-sm font-semibold hover:bg-slate-50 shadow-2xs transition">
                <svg class="size-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </a>

            <!-- Add New Player Button -->
            <button type="button" onclick="document.getElementById('add-player-modal').showModal()" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs md:text-sm font-bold shadow-xs transition cursor-pointer">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Add New Player</span>
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5">
        <form method="GET" action="{{ route('players.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            
            <!-- Search Input -->
            <div class="lg:col-span-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Search Players</label>
                <div class="relative">
                    <svg class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by name or ID..." 
                           class="w-full pl-9 pr-3 py-2 text-xs md:text-sm bg-white border border-slate-300 rounded-lg focus:border-emerald-600 transition">
                </div>
            </div>

            <!-- Filter by Team -->
            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Filter by Team</label>
                <select name="team_id" class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="">All Teams</option>
                    @foreach ($teams as $t)
                        <option value="{{ $t->id }}" @selected(($teamId ?? '') == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Position -->
            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Filter by Position</label>
                <select name="position" class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="">All Positions</option>
                    <option value="FW" @selected(($position ?? '') === 'FW')>Forward (FW)</option>
                    <option value="MF" @selected(($position ?? '') === 'MF')>Midfielder (MF)</option>
                    <option value="DF" @selected(($position ?? '') === 'DF')>Defender (DF)</option>
                    <option value="GK" @selected(($position ?? '') === 'GK')>Goalkeeper (GK)</option>
                </select>
            </div>

            <!-- Apply Button -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-3 bg-sky-100 hover:bg-sky-200 text-sky-900 text-xs md:text-sm font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-2xs">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Apply Filters</span>
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
                        <th class="py-3.5 px-6">PLAYER NAME</th>
                        <th class="py-3.5 px-6">TEAM</th>
                        <th class="py-3.5 px-6 text-center">NUMBER</th>
                        <th class="py-3.5 px-6 text-center">POSITION</th>
                        <th class="py-3.5 px-6 text-center">STATUS</th>
                        <th class="py-3.5 px-6 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($players as $player)
                        @php
                            $playerIdCode = 'PLY-' . str_pad($player->id, 3, '0', STR_PAD_LEFT);
                            $pos = $player->profile?->position ?? 'FW';
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
                                {{ $player->team?->name ?? 'Free Agent' }}
                            </td>

                            <!-- Number -->
                            <td class="py-3.5 px-6 text-center font-bold text-slate-800">
                                {{ $player->jersey_number ?? 10 }}
                            </td>

                            <!-- Position -->
                            <td class="py-3.5 px-6 text-center">
                                <span class="inline-block bg-slate-100 text-slate-700 text-xs font-bold px-2.5 py-0.5 rounded">
                                    {{ $pos }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-6 text-center">
                                @if ($isSuspended)
                                    <span class="inline-block bg-[#fee2e2] text-[#991b1b] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
                                        SUSPENDED
                                    </span>
                                @else
                                    <span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
                                        ACTIVE
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
                        <!-- Demo Mock Rows matching Screenshot 5 -->
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-900">
                                <div class="flex items-center gap-3.5">
                                    <div class="size-9 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center shrink-0">AM</div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">Alex Mercer</div>
                                        <div class="text-[11px] text-slate-400 font-normal">ID: PLY-001</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 font-medium">SF Lions</td>
                            <td class="py-3.5 px-6 text-center font-bold text-slate-800">10</td>
                            <td class="py-3.5 px-6 text-center"><span class="inline-block bg-slate-100 text-slate-700 text-xs font-bold px-2.5 py-0.5 rounded">FW</span></td>
                            <td class="py-3.5 px-6 text-center"><span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">ACTIVE</span></td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-3 text-slate-400">
                                    <button class="p-1 hover:text-slate-800"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button class="p-1 hover:text-rose-600"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-900">
                                <div class="flex items-center gap-3.5">
                                    <div class="size-9 rounded-full bg-sky-100 text-sky-800 font-bold text-xs flex items-center justify-center shrink-0">SJ</div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">Sarah Jenkins</div>
                                        <div class="text-[11px] text-slate-400 font-normal">ID: PLY-042</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 font-medium">Bay Area FC</td>
                            <td class="py-3.5 px-6 text-center font-bold text-slate-800">4</td>
                            <td class="py-3.5 px-6 text-center"><span class="inline-block bg-slate-100 text-slate-700 text-xs font-bold px-2.5 py-0.5 rounded">DF</span></td>
                            <td class="py-3.5 px-6 text-center"><span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">ACTIVE</span></td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-3 text-slate-400">
                                    <button class="p-1 hover:text-slate-800"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button class="p-1 hover:text-rose-600"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-900">
                                <div class="flex items-center gap-3.5">
                                    <div class="size-9 rounded-full bg-slate-700 text-white font-bold text-xs flex items-center justify-center shrink-0">MT</div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">Marcus Thorne</div>
                                        <div class="text-[11px] text-slate-400 font-normal">ID: PLY-018</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 font-medium">Golden Gate United</td>
                            <td class="py-3.5 px-6 text-center font-bold text-slate-800">1</td>
                            <td class="py-3.5 px-6 text-center"><span class="inline-block bg-slate-100 text-slate-700 text-xs font-bold px-2.5 py-0.5 rounded">GK</span></td>
                            <td class="py-3.5 px-6 text-center"><span class="inline-block bg-[#fee2e2] text-[#991b1b] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">SUSPENDED</span></td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-3 text-slate-400">
                                    <button class="p-1 hover:text-slate-800"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button class="p-1 hover:text-rose-600"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-900">
                                <div class="flex items-center gap-3.5">
                                    <div class="size-9 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0">EL</div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">Elena Rodriguez</div>
                                        <div class="text-[11px] text-slate-400 font-normal">ID: PLY-088</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 font-medium">SF Lions</td>
                            <td class="py-3.5 px-6 text-center font-bold text-slate-800">8</td>
                            <td class="py-3.5 px-6 text-center"><span class="inline-block bg-slate-100 text-slate-700 text-xs font-bold px-2.5 py-0.5 rounded">MF</span></td>
                            <td class="py-3.5 px-6 text-center"><span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">ACTIVE</span></td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-3 text-slate-400">
                                    <button class="p-1 hover:text-slate-800"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button class="p-1 hover:text-rose-600"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
                @if($players instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    Showing {{ $players->firstItem() ?? 1 }} to {{ $players->lastItem() ?? 4 }} of {{ $players->total() }} players
                @else
                    Showing 1 to 4 of 128 players
                @endif
            </div>

            <div>
                @if($players instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $players->links('pagination::tailwind') }}
                @else
                    <div class="flex items-center gap-1">
                        <button class="size-8 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 flex items-center justify-center cursor-pointer">&lt;</button>
                        <button class="size-8 rounded-lg bg-[#057a55] text-white font-bold flex items-center justify-center">1</button>
                        <button class="size-8 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center cursor-pointer">2</button>
                        <button class="size-8 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center cursor-pointer">3</button>
                        <span class="px-1 text-slate-400">...</span>
                        <button class="size-8 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 flex items-center justify-center cursor-pointer">&gt;</button>
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>

<!-- Modal: Add New Player -->
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
                    <option value="FW">Delantero (FW)</option>
                    <option value="MF">Mediocampista (MF)</option>
                    <option value="DF">Defensa (DF)</option>
                    <option value="GK">Portero (GK)</option>
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
