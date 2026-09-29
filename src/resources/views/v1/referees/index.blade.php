@extends('v1.layouts.app')

@section('title', 'Referee Management')

@section('content')
<div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Referee Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage official match referees, certification credentials, and algorithmic evaluations.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('referees.evaluations.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                <svg class="size-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Anti-Conflict & Evaluations</span>
            </a>
            <button type="button" onclick="document.getElementById('modal-add-referee').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-lg bg-[#057a55] px-4 py-2.5 text-xs font-semibold text-white hover:bg-[#046c4b] transition shadow-2xs">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Referee</span>
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Referees -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Total Referees</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalReferees }}</div>
            <span class="inline-flex items-center text-[11px] text-emerald-700 font-medium mt-1">Official registry</span>
        </div>

        <!-- Card 2: Active Referees -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Active Referees</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $activeReferees }}</div>
            <span class="inline-flex items-center text-[11px] text-emerald-700 font-medium mt-1">Available for fixtures</span>
        </div>

        <!-- Card 3: Average Rating -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Average Rating</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($avgRating, 1) }} <span class="text-amber-500 text-xl">★</span></div>
            <span class="inline-flex items-center text-[11px] text-slate-500 font-medium mt-1">Evaluated by team coaches</span>
        </div>

        <!-- Card 4: Total Matches Officiated -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Matches Officiated</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalMatches }}</div>
            <span class="inline-flex items-center text-[11px] text-slate-500 font-medium mt-1">Season historical matches</span>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
        <!-- Filter Toolbar -->
        <div class="p-5 border-b border-slate-100">
            <form method="GET" action="{{ route('referees.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search referees by name or license ID..." 
                           class="w-full pl-10 pr-4 py-2 text-xs border border-slate-200 rounded-lg bg-slate-50/60 focus:bg-white focus:border-[#057a55] focus:outline-none placeholder:text-slate-400">
                </div>

                <div class="w-full sm:w-44">
                    <select name="status" class="w-full py-2 px-3 text-xs border border-slate-200 rounded-lg bg-white focus:border-[#057a55] focus:outline-none">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>

                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Referees Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-slate-600 font-semibold text-[11px] uppercase tracking-wider">
                        <th class="py-3 px-5">Referee</th>
                        <th class="py-3 px-5">License ID</th>
                        <th class="py-3 px-5 text-center">Matches</th>
                        <th class="py-3 px-5 text-center">Rating</th>
                        <th class="py-3 px-5 text-center">Status</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($referees as $referee)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0 border border-slate-200">
                                        {{ strtoupper(substr($referee->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $referee->name }}</p>
                                        <p class="text-[11px] text-slate-400">IFAB Certified Official</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 font-mono text-slate-600 font-medium">
                                {{ $referee->license_number }}
                            </td>
                            <td class="py-3.5 px-5 text-center font-bold text-slate-800">
                                {{ $referee->matches_count ?? $referee->total_matches_officiated }}
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                <span class="inline-flex items-center gap-1 font-bold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full text-[11px]">
                                    ★ {{ number_format($referee->rating_average, 1) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                @if ($referee->is_active)
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wide">
                                        ACTIVE
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 uppercase tracking-wide">
                                        INACTIVE
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('referees.evaluations.index') }}" class="p-1.5 text-slate-400 hover:text-emerald-700 transition" title="Evaluations & Conflict check">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('referees.destroy', $referee) }}" onsubmit="return confirm('¿Eliminar a este árbitro del padrón oficial?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Eliminar">
                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                No se encontraron árbitros registrados con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($referees->hasPages())
            <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Showing {{ $referees->firstItem() ?? 0 }} to {{ $referees->lastItem() ?? 0 }} of {{ $referees->total() }} referees</span>
                <div>
                    {{ $referees->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal: Add New Referee -->
<div id="modal-add-referee" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Add New Referee</h3>
            <button type="button" onclick="document.getElementById('modal-add-referee').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('referees.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" id="name" required placeholder="e.g. David O'Connor" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
            </div>

            <div>
                <label for="license_number" class="block text-xs font-semibold text-slate-700 mb-1">License ID / Credential *</label>
                <input type="text" name="license_number" id="license_number" required placeholder="e.g. REF-2024-009" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="size-4 rounded text-[#057a55] focus:ring-[#057a55] border-slate-300">
                <label for="is_active" class="text-xs font-semibold text-slate-700">Set as Active & Available for Matchdays</label>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-referee').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#057a55] hover:bg-[#046c4b] text-xs font-semibold text-white transition shadow-2xs">
                    Save Referee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
