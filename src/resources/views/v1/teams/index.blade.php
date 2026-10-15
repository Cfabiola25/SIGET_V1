@extends('v1.layouts.app')

@section('title', 'Gestión de Equipos')
@section('header_title', 'Gestión de Equipos')

@section('content')
<div class="space-y-6">

    <!-- Top KPI Stat Cards & Add Button -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
        
        <!-- Total Equipos -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Equipos</span>
            <div class="mt-3 text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                {{ number_format($totalTeamsCount) }}
            </div>
        </div>

        <!-- Jugadores Activos -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Jugadores Activos</span>
            <div class="mt-3 text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                {{ number_format($activePlayersCount) }}
            </div>
        </div>

        <!-- Inscripciones Pendientes -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Inscripciones Pendientes</span>
            <div class="mt-3 text-3xl md:text-4xl font-black text-rose-600 tracking-tight">
                {{ $pendingRegistrationsCount }}
            </div>
        </div>

        <!-- Botón Inscribir Nuevo Equipo -->
        <button type="button" onclick="document.getElementById('add-team-modal').showModal()" 
                class="bg-[#1e3a8a] hover:bg-[#0284c7] active:bg-[#172554] text-white rounded-2xl p-5 shadow-sm transition-all duration-150 flex flex-col items-center justify-center gap-2 group cursor-pointer text-center">
            <svg class="size-8 text-white group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v8m-4-4h8"/>
            </svg>
            <span class="text-xs font-black uppercase tracking-wider">AGREGAR EQUIPO</span>
        </button>
    </div>

    <!-- Main Section: Split Layout (Equipos Inscritos + Auditoría) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: Tabla de Equipos Inscritos (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            
            <!-- Card Header: Título & Buscador -->
            <div class="p-5 md:px-6 md:py-4.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-lg font-bold text-slate-900">
                    Equipos Inscritos
                </h2>
                
                <!-- Buscador de equipos -->
                <form method="GET" action="{{ route('teams.index') }}" class="relative w-full sm:w-72">
                    <svg class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search ?? '' }}" 
                           placeholder="Buscar equipos..." 
                           class="w-full pl-9 pr-4 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition">
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-[#edf2f9] text-slate-700 text-xs font-semibold">
                            <th class="py-3 px-5">Nombre del Equipo</th>
                            <th class="py-3 px-5">Director Técnico</th>
                            <th class="py-3 px-5 text-center">Jugadores</th>
                            <th class="py-3 px-5 text-center">Estado</th>
                            <th class="py-3 px-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($teams as $team)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-5 font-semibold text-slate-900">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-200">
                                            {{ strtoupper(substr($team->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('teams.show', $team) }}" class="hover:text-emerald-700 transition">
                                                {{ $team->name }}
                                            </a>
                                            <div class="text-[11px] text-slate-400 font-normal">
                                                {{ $team->tournament?->name ?? 'Torneo Principal' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 font-medium">
                                    {{ $team->coach_name }}
                                </td>
                                <td class="py-3.5 px-5 text-center font-bold text-slate-800">
                                    {{ $team->players_count ?? 18 }}
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    @if ($team->status === 'approved' || $team->status === 'active')
                                        <span class="inline-block bg-[#dcfce7] text-[#15803d] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                            ACTIVO
                                        </span>
                                    @else
                                        <span class="inline-block bg-[#fee2e2] text-[#991b1b] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                            INCOMPLETO
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2 text-slate-400">
                                        <a href="{{ route('teams.show', $team) }}" class="p-1 hover:text-emerald-700 transition" title="Ver detalles">
                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('teams.edit', $team) }}" class="p-1 hover:text-slate-800 transition" title="Editar">
                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Fallback Mock / Realistic Demo Rows matching screenshot if database is fresh -->
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-5 font-semibold text-slate-900">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-200">MS</div>
                                        <span>Metro City Strikers</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 font-medium">David O'Connor</td>
                                <td class="py-3.5 px-5 text-center font-bold text-slate-800">18</td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="inline-block bg-[#dcfce7] text-[#15803d] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">ACTIVO</span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2 text-slate-400">
                                        <button class="p-1 hover:text-emerald-700"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                        <button class="p-1 hover:text-slate-800"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-5 font-semibold text-slate-900">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-200">VH</div>
                                        <span>Valley Heights United</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 font-medium">Sarah Jenkins</td>
                                <td class="py-3.5 px-5 text-center font-bold text-slate-800">22</td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="inline-block bg-[#dcfce7] text-[#15803d] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">ACTIVO</span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2 text-slate-400">
                                        <button class="p-1 hover:text-emerald-700"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                        <button class="p-1 hover:text-slate-800"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-5 font-semibold text-slate-900">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-lg bg-rose-100 text-rose-800 font-bold text-xs flex items-center justify-center shrink-0 border border-rose-200">ER</div>
                                        <span>Eastside Rangers</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 font-medium">Marcus Johnson</td>
                                <td class="py-3.5 px-5 text-center font-bold text-slate-800">15</td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="inline-block bg-[#fee2e2] text-[#991b1b] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">INCOMPLETO</span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2 text-slate-400">
                                        <button class="p-1 hover:text-emerald-700"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                        <button class="p-1 hover:text-slate-800"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-5 font-semibold text-slate-900">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-200">NF</div>
                                        <span>Northwood FC</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 font-medium">Elena Rodriguez</td>
                                <td class="py-3.5 px-5 text-center font-bold text-slate-800">20</td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="inline-block bg-[#dcfce7] text-[#15803d] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">ACTIVO</span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2 text-slate-400">
                                        <button class="p-1 hover:text-emerald-700"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                        <button class="p-1 hover:text-slate-800"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
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
                    @if($teams instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        Mostrando {{ $teams->firstItem() ?? 1 }}-{{ $teams->lastItem() ?? 4 }} de {{ $teams->total() }} equipos
                    @else
                        Mostrando 1-4 de 128 equipos
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if($teams instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        {{ $teams->links('pagination::simple-tailwind') }}
                    @else
                        <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition cursor-pointer">Anterior</button>
                        <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition cursor-pointer">Siguiente</button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Auditing Card (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            
            <!-- Auditing Header -->
            <div class="p-5 border-b border-slate-100 flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-xl bg-slate-100 text-slate-700 shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 leading-tight">Auditoría</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Revisión de Actas Arbitrales</p>
                    </div>
                </div>
                <span class="bg-rose-600 text-white font-extrabold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                    3 PENDIENTES
                </span>
            </div>

            <!-- Incident Review Cards -->
            <div class="p-5 space-y-4">
                
                <!-- Match #842 Disputed Score Card -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-3 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-rose-500"></div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-sm font-bold text-slate-900">Partido #842</div>
                            <div class="text-xs text-slate-500">Enviado por Árb. Thomas</div>
                        </div>
                        <span class="border border-rose-500 text-rose-600 text-[10px] font-bold px-2 py-0.5 rounded leading-tight">
                            Resultado<br>en Disputa
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Metro City Strikers (2) vs Eastside Rangers (1). Disputa registrada por el capitán sobre el minuto del gol definitivo.
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <a href="{{ route('matches.index') }}" class="flex-1 text-center py-1.5 px-3 bg-sky-100 hover:bg-sky-200 text-sky-800 text-xs font-semibold rounded-lg transition">
                            Revisar Registro
                        </a>
                        <button type="button" onclick="alert('Disputa de gol validada y acta arbitral confirmada exitosamente.')" class="flex-1 text-center py-1.5 px-3 border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-xs font-semibold rounded-lg transition cursor-pointer">
                            Resolver
                        </button>
                    </div>
                </div>

                <!-- Match #840 Red Card Review Card -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-3 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-700"></div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-sm font-bold text-slate-900">Partido #840</div>
                            <div class="text-xs text-slate-500">Enviado por Árb. Davis</div>
                        </div>
                        <span class="border border-slate-400 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded leading-tight">
                            Revisión de<br>Tarjeta Roja
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Jugador #9 (Valley Heights) expulsado al 78'. Requiere revisión de suspensión obligatoria.
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('tournaments.disciplinary', 1) }}" class="w-full block text-center py-1.5 px-3 border border-emerald-700 text-emerald-800 hover:bg-emerald-50 text-xs font-semibold rounded-lg transition">
                            Gestionar Suspensión
                        </a>
                    </div>
                </div>

            </div>

            <!-- View All Audits Link -->
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
                <a href="{{ route('auditing.index') }}" class="text-xs font-bold text-slate-700 hover:text-emerald-700 inline-flex items-center gap-1 transition">
                    <span>Ver Todas las Auditorías</span>
                    <span>&rarr;</span>
                </a>
            </div>

        </div>

    </div>

</div>

<!-- Modal: Inscribir Nuevo Equipo -->
<x-modal id="add-team-modal" title="Inscribir Nuevo Equipo" maxWidth="max-w-xl">
    <form method="POST" action="{{ route('teams.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre del Club / Equipo *</label>
            <input type="text" name="name" required placeholder="Ej: Real Metropolitan FC" class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Torneo Oficial *</label>
                @if(auth()->user()?->isAdmin())
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Tus Torneos Asignados</span>
                @else
                    <span class="text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full border border-sky-200">Todos los Torneos (Super Admin)</span>
                @endif
            </div>
            <select name="tournament_id" required class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                @forelse ($tournaments as $tournament)
                    <option value="{{ $tournament->id }}">{{ $tournament->name }} ({{ $tournament->sport_type }})</option>
                @empty
                    <option value="" disabled>No tienes torneos activos asignados</option>
                @endforelse
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Escudo / Logo (Opcional)</label>
            <input type="text" name="logo_path" placeholder="https://... o /logos/team.png" class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
        </div>

        <!-- Sección de Asignación y Creación de Director Técnico (DT) -->
        <div class="space-y-3 p-4 bg-slate-50/80 border border-slate-200/90 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <svg class="size-4 text-[#1e3a8a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Director Técnico (DT)</span>
                </div>
                <span class="text-[10px] bg-sky-100 text-sky-900 border border-sky-300 font-extrabold px-2 py-0.5 rounded-full">Acceso Exclusivo</span>
            </div>

            <!-- Selector de Modo de DT -->
            <div class="grid grid-cols-3 gap-1 bg-slate-200/70 p-1 rounded-lg text-xs font-semibold text-slate-600">
                <label class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-md cursor-pointer transition has-[:checked]:bg-white has-[:checked]:text-[#1e3a8a] has-[:checked]:shadow-2xs">
                    <input type="radio" name="dt_mode" value="new" checked class="sr-only" onchange="toggleTeamModalDtMode(this.value)">
                    <span>+ Crear DT</span>
                </label>
                <label class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-md cursor-pointer transition has-[:checked]:bg-white has-[:checked]:text-[#1e3a8a] has-[:checked]:shadow-2xs">
                    <input type="radio" name="dt_mode" value="existing" class="sr-only" onchange="toggleTeamModalDtMode(this.value)">
                    <span>Existente</span>
                </label>
                <label class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-md cursor-pointer transition has-[:checked]:bg-white has-[:checked]:text-[#1e3a8a] has-[:checked]:shadow-2xs">
                    <input type="radio" name="dt_mode" value="none" class="sr-only" onchange="toggleTeamModalDtMode(this.value)">
                    <span>Sin DT</span>
                </label>
            </div>

            <!-- Modo 1: Registrar Nuevo DT Aquí Mismo -->
            <div id="team-modal-dt-new-fields" class="space-y-3 pt-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre Completo del DT *</label>
                        <input type="text" name="coach_name" placeholder="Ej: Marcelo Gallardo" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email del DT (Login) *</label>
                        <input type="email" name="coach_email" placeholder="dt.gallardo@siget.com" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Contraseña de Acceso</label>
                        <input type="text" name="coach_password" value="password" placeholder="Mínimo 6 caracteres" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                        <span class="text-[10px] text-slate-500">Predeterminada: password</span>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Teléfono / WhatsApp (Opcional)</label>
                        <input type="text" name="coach_phone" placeholder="+57 300 123 4567" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                    </div>
                </div>

                <div class="rounded-lg bg-blue-50/80 border border-blue-200/80 p-2.5 text-[11px] text-blue-900 flex items-start gap-2">
                    <svg class="size-4 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong>Seguridad & Permisos:</strong> La cuenta se creará con rol de Director Técnico. Tendrá acceso <em>únicamente</em> a la gestión de este equipo (plantel y alineaciones) y al contacto/fichaje de agentes libres en el Radar de Talentos.</span>
                </div>
            </div>

            <!-- Modo 2: Seleccionar DT Existente -->
            <div id="team-modal-dt-existing-fields" class="space-y-2 pt-1 hidden">
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Seleccionar DT Registrado</label>
                <select name="existing_dt_id" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                    <option value="">-- Elige un Director Técnico registrado --</option>
                    @if(isset($coaches))
                        @foreach($coaches as $coach)
                            <option value="{{ $coach->id }}">{{ $coach->name }} ({{ $coach->email }})</option>
                        @endforeach
                    @endif
                </select>
                <span class="text-[10px] text-slate-500 block">Se le asignará la dirección técnica de este equipo con sus credenciales actuales.</span>
            </div>

            <!-- Modo 3: Sin DT -->
            <div id="team-modal-dt-none-fields" class="pt-1 text-[11px] text-slate-500 hidden">
                El equipo se inscribirá sin DT por ahora. Podrás asignarle un Director Técnico más adelante.
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <x-button variant="secondary" onclick="document.getElementById('add-team-modal').close()">Cancelar</x-button>
            <x-button type="submit">Inscribir Equipo</x-button>
        </div>
    </form>
</x-modal>

<script>
function toggleTeamModalDtMode(mode) {
    const newFields = document.getElementById('team-modal-dt-new-fields');
    const existingFields = document.getElementById('team-modal-dt-existing-fields');
    const noneFields = document.getElementById('team-modal-dt-none-fields');
    if (!newFields || !existingFields || !noneFields) return;

    newFields.classList.toggle('hidden', mode !== 'new');
    existingFields.classList.toggle('hidden', mode !== 'existing');
    noneFields.classList.toggle('hidden', mode !== 'none');
}
</script>
@endsection
