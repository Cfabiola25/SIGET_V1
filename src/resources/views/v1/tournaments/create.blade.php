@extends('v1.layouts.app')

@section('title', 'Create New Tournament')
@section('header_title', 'Create New Tournament')

@section('content')
<form method="POST" action="{{ route('tournaments.store') }}" id="create-tournament-form">
    @csrf

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
                Create New Tournament
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">
                Configure tournament details, phases, and points systems.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('tournaments.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs md:text-sm font-semibold hover:bg-slate-50 shadow-2xs transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs md:text-sm font-bold shadow-xs transition cursor-pointer">
                Save Tournament
            </button>
        </div>
    </div>

    <!-- Main Two-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Card 1: Core Details -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 md:p-6 space-y-5">
                <div class="flex items-center gap-2 text-emerald-800">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-base font-bold text-slate-900">Core Details</h3>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tournament Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Summer Cup 2024" 
                           class="w-full text-sm px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg focus:border-emerald-600 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Season</label>
                    <select name="season" class="w-full text-sm px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg focus:border-emerald-600 transition">
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026" selected>2026</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Sport / Discipline *</label>
                    <select name="sport_type" required class="w-full text-sm px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg focus:border-emerald-600 transition">
                        <option value="Futbol">Soccer (11v11)</option>
                        <option value="Fútbol 8">Fútbol 8</option>
                        <option value="Futsal">Futsal</option>
                        <option value="Baloncesto">Baloncesto</option>
                        <option value="Voleibol">Voleibol</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Start Date</label>
                        <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">End Date</label>
                        <input type="date" name="end_date" value="{{ now()->addMonths(3)->format('Y-m-d') }}" class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg">
                    </div>
                </div>
            </div>

            <!-- Card 2: Points System -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 md:p-6 space-y-4">
                <div class="flex items-center gap-2 text-emerald-800">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <h3 class="text-base font-bold text-slate-900">Points System</h3>
                </div>

                <!-- Win Row -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="size-7 rounded-full bg-[#057a55] text-white font-black text-xs flex items-center justify-center">
                            W
                        </div>
                        <span class="text-sm font-semibold text-slate-800">Win</span>
                    </div>
                    <input type="number" name="points_win" value="3" min="0" max="10" 
                           class="w-16 text-center font-bold text-sm py-1.5 bg-white border border-slate-300 rounded-lg">
                </div>

                <!-- Draw Row -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="size-7 rounded-full bg-sky-600 text-white font-black text-xs flex items-center justify-center">
                            D
                        </div>
                        <span class="text-sm font-semibold text-slate-800">Draw</span>
                    </div>
                    <input type="number" name="points_draw" value="1" min="0" max="10" 
                           class="w-16 text-center font-bold text-sm py-1.5 bg-white border border-slate-300 rounded-lg">
                </div>

                <!-- Loss Row -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="size-7 rounded-full bg-rose-600 text-white font-black text-xs flex items-center justify-center">
                            L
                        </div>
                        <span class="text-sm font-semibold text-slate-800">Loss</span>
                    </div>
                    <input type="number" name="points_loss" value="0" min="0" max="10" 
                           class="w-16 text-center font-bold text-sm py-1.5 bg-white border border-slate-300 rounded-lg">
                </div>

                <!-- Bonus Point Checkbox -->
                <div class="pt-2">
                    <label class="flex items-start gap-2.5 text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="bonus_goals" value="1" class="size-4 mt-0.5 rounded border-slate-300 accent-[#057a55]">
                        <span>Award bonus point for goals scored (e.g., >3 goals)</span>
                    </label>
                </div>
            </div>

        </div>

        <!-- Right Column: Tournament Phases (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 md:p-6 space-y-6">
            
            <!-- Phases Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2 text-emerald-800">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <h3 class="text-base font-bold text-slate-900">Tournament Phases</h3>
                </div>

                <button type="button" onclick="alert('Nueva fase agregada al flujo del torneo.')" 
                        class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-700 flex items-center gap-1.5 transition cursor-pointer">
                    <span>+</span>
                    <span>Add Phase</span>
                </button>
            </div>

            <!-- Phase 1: Group Stage -->
            <div class="border border-slate-200 rounded-xl p-4 md:p-5 bg-slate-50/50 space-y-4">
                
                <!-- Phase Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="size-6 rounded-md bg-[#057a55] text-white font-bold text-xs flex items-center justify-center">
                            1
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">Group Stage</h4>
                        <span class="bg-sky-100 text-sky-800 font-bold text-[10px] px-2.5 py-0.5 rounded-full">
                            Round Robin
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-slate-400">
                        <button type="button" class="p-1 hover:text-slate-700"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                        <button type="button" class="p-1 hover:text-rose-600"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </div>
                </div>

                <!-- Phase 1 Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Number of Groups</label>
                        <input type="number" name="group_count" value="4" min="1" max="16" 
                               class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Teams per Group</label>
                        <input type="number" name="teams_per_group" value="4" min="2" max="16" 
                               class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Advancing Teams (per group)</label>
                    <select name="advancing_teams" class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg">
                        <option value="2">Top 2</option>
                        <option value="1">Top 1</option>
                        <option value="3">Top 3</option>
                    </select>
                </div>
            </div>

            <!-- Down Arrow Connector -->
            <div class="flex justify-center -my-2">
                <div class="size-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shadow-2xs">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                </div>
            </div>

            <!-- Phase 2: Playoffs -->
            <div class="border border-slate-200 rounded-xl p-4 md:p-5 bg-slate-50/50 space-y-4">
                
                <!-- Phase Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="size-6 rounded-md bg-slate-700 text-white font-bold text-xs flex items-center justify-center">
                            2
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">Playoffs</h4>
                        <span class="bg-sky-100 text-sky-800 font-bold text-[10px] px-2.5 py-0.5 rounded-full">
                            Knockout
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-slate-400">
                        <button type="button" class="p-1 hover:text-slate-700"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                        <button type="button" class="p-1 hover:text-rose-600"><svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </div>
                </div>

                <!-- Phase 2 Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Starting Round</label>
                        <select name="starting_round" class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg">
                            <option value="quarter_finals">Quarter-Finals (8 teams)</option>
                            <option value="semi_finals">Semi-Finals (4 teams)</option>
                            <option value="round_of_16">Round of 16 (16 teams)</option>
                        </select>
                        <div class="text-[11px] text-slate-400 mt-1">Auto-calculated from Phase 1</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Legs per Match</label>
                        <select name="elimination_type" class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg">
                            <option value="single">Single Elimination</option>
                            <option value="two_legged">Two-Legged (Ida y Vuelta)</option>
                        </select>
                    </div>
                </div>

                <!-- Third place checkbox -->
                <div>
                    <label class="flex items-center gap-2.5 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="include_third_place" value="1" checked class="size-4 rounded border-slate-300 accent-[#057a55]">
                        <span>Include Third Place Match</span>
                    </label>
                </div>
            </div>

            <!-- Bracket Visualization Placeholder (Dashed Box) -->
            <div class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center flex flex-col items-center justify-center">
                <svg class="size-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h4v4H4V6zm12 0h4v4h-4V6zm-6 7h4v4h-4v-4zM8 8h4v8m0-4h4"/>
                </svg>
                <p class="text-xs text-slate-400 font-medium max-w-sm">
                    Bracket visualization will be available once teams are assigned.
                </p>
            </div>

        </div>

    </div>

</form>
@endsection
