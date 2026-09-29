@extends('v1.layouts.app')

@section('title', 'Teams Management')
@section('header_title', 'Teams Management')

@section('content')
<div class="space-y-6">

    <!-- Top KPI Stat Cards & Add Button -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
        
        <!-- Total Teams -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Teams</span>
            <div class="mt-3 text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                {{ number_format($totalTeamsCount) }}
            </div>
        </div>

        <!-- Active Players -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Players</span>
            <div class="mt-3 text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                {{ number_format($activePlayersCount) }}
            </div>
        </div>

        <!-- Pending Registrations -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Registrations</span>
            <div class="mt-3 text-3xl md:text-4xl font-black text-rose-600 tracking-tight">
                {{ $pendingRegistrationsCount }}
            </div>
        </div>

        <!-- Add New Team Button Card -->
        <button type="button" onclick="document.getElementById('add-team-modal').showModal()" 
                class="bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white rounded-2xl p-5 shadow-sm transition-all duration-150 flex flex-col items-center justify-center gap-2 group cursor-pointer text-center">
            <svg class="size-8 text-white group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v8m-4-4h8"/>
            </svg>
            <span class="text-xs font-black uppercase tracking-wider">ADD NEW TEAM</span>
        </button>
    </div>

    <!-- Main Section: Split Layout (Registered Teams + Auditing) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: Registered Teams Table Card (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            
            <!-- Card Header: Title & Search -->
            <div class="p-5 md:px-6 md:py-4.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-lg font-bold text-slate-900">
                    Registered Teams
                </h2>
                
                <!-- Search teams input -->
                <form method="GET" action="{{ route('teams.index') }}" class="relative w-full sm:w-72">
                    <svg class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search ?? '' }}" 
                           placeholder="Search teams..." 
                           class="w-full pl-9 pr-4 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition">
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-[#edf2f9] text-slate-700 text-xs font-semibold">
                            <th class="py-3 px-5">Team Name</th>
                            <th class="py-3 px-5">Coach</th>
                            <th class="py-3 px-5 text-center">Players</th>
                            <th class="py-3 px-5 text-center">Status</th>
                            <th class="py-3 px-5 text-right">Actions</th>
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
                                        <span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                            ACTIVE
                                        </span>
                                    @else
                                        <span class="inline-block bg-[#fee2e2] text-[#991b1b] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                            INCOMPLETE
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
                                    <span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">ACTIVE</span>
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
                                    <span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">ACTIVE</span>
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
                                    <span class="inline-block bg-[#fee2e2] text-[#991b1b] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">INCOMPLETE</span>
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
                                    <span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">ACTIVE</span>
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
                        Showing {{ $teams->firstItem() ?? 1 }}-{{ $teams->lastItem() ?? 4 }} of {{ $teams->total() }} teams
                    @else
                        Showing 1-4 of 128 teams
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if($teams instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        {{ $teams->links('pagination::simple-tailwind') }}
                    @else
                        <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition cursor-pointer">Prev</button>
                        <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition cursor-pointer">Next</button>
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
                        <h3 class="text-base font-bold text-slate-900 leading-tight">Auditing</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Referee Submissions Review</p>
                    </div>
                </div>
                <span class="bg-rose-600 text-white font-extrabold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                    3 PENDING
                </span>
            </div>

            <!-- Incident Review Cards -->
            <div class="p-5 space-y-4">
                
                <!-- Match #842 Disputed Score Card -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-3 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-rose-500"></div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-sm font-bold text-slate-900">Match #842</div>
                            <div class="text-xs text-slate-500">Submitted by Ref. Thomas</div>
                        </div>
                        <span class="border border-rose-500 text-rose-600 text-[10px] font-bold px-2 py-0.5 rounded leading-tight">
                            Disputed<br>Score
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Metro City Strikers (2) vs Eastside Rangers (1). Captain dispute logged regarding final goal timing.
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <a href="{{ route('matches.index') }}" class="flex-1 text-center py-1.5 px-3 bg-sky-100 hover:bg-sky-200 text-sky-800 text-xs font-semibold rounded-lg transition">
                            Review Log
                        </a>
                        <button type="button" onclick="alert('Disputa de gol validada y acta arbitral confirmada exitosamente.')" class="flex-1 text-center py-1.5 px-3 border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-xs font-semibold rounded-lg transition cursor-pointer">
                            Resolve
                        </button>
                    </div>
                </div>

                <!-- Match #840 Red Card Review Card -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-3 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-700"></div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-sm font-bold text-slate-900">Match #840</div>
                            <div class="text-xs text-slate-500">Submitted by Ref. Davis</div>
                        </div>
                        <span class="border border-slate-400 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded leading-tight">
                            Red Card<br>Review
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Player #9 (Valley Heights) sent off 78'. Mandatory suspension review required.
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('tournaments.disciplinary', 1) }}" class="w-full block text-center py-1.5 px-3 border border-emerald-700 text-emerald-800 hover:bg-emerald-50 text-xs font-semibold rounded-lg transition">
                            Process Suspension
                        </a>
                    </div>
                </div>

            </div>

            <!-- View All Audits Link -->
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
                <a href="{{ route('auditing.index') }}" class="text-xs font-bold text-slate-700 hover:text-emerald-700 inline-flex items-center gap-1 transition">
                    <span>View All Audits</span>
                    <span>&rarr;</span>
                </a>
            </div>

        </div>

    </div>

</div>

<!-- Modal: Add New Team -->
<x-modal id="add-team-modal" title="Inscribir Nuevo Equipo">
    <form method="POST" action="{{ route('teams.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre del Equipo *</label>
            <input type="text" name="name" required placeholder="Ej: Metro City Strikers" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Torneo Asignado *</label>
            <select name="tournament_id" required class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                @foreach ($tournaments as $tournament)
                    <option value="{{ $tournament->id }}">{{ $tournament->name }} ({{ $tournament->sport_type }})</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre Director Técnico</label>
                <input type="text" name="coach_name" placeholder="David O'Connor" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email del DT</label>
                <input type="email" name="coach_email" placeholder="dt@equipo.com" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <x-button variant="secondary" onclick="document.getElementById('add-team-modal').close()">Cancelar</x-button>
            <x-button type="submit">Guardar Equipo</x-button>
        </div>
    </form>
</x-modal>
@endsection
