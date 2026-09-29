@extends('v1.layouts.app')

@section('title', 'Match Scheduler')
@section('header_title', 'Match Scheduler')

@section('header_search')
<div class="relative w-64 md:w-80 hidden sm:block">
    <svg class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    <input type="text" placeholder="Search matches, teams..." 
           class="w-full pl-9 pr-4 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition">
</div>
@endsection

@section('content')
@php
    $carbonDate = \Carbon\Carbon::parse($date);
    $prevDate = $carbonDate->copy()->subDay()->format('Y-m-d');
    $nextDate = $carbonDate->copy()->addDay()->format('Y-m-d');
    $displayDate = $carbonDate->format('F d, Y');
@endphp

<div class="space-y-6">

    <!-- Date Navigation & View Modes Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-3.5 px-6 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Date Switcher -->
        <div class="flex items-center gap-4">
            <a href="{{ route('matches.schedule', ['date' => $prevDate]) }}" class="p-1 text-slate-500 hover:text-slate-800 transition">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-base md:text-lg font-bold text-slate-900 min-w-44 text-center">
                {{ $displayDate }}
            </h2>
            <a href="{{ route('matches.schedule', ['date' => $nextDate]) }}" class="p-1 text-slate-500 hover:text-slate-800 transition">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <!-- Mode Buttons: Today, Day, Week -->
        <div class="flex items-center rounded-xl bg-slate-100 p-1 text-xs font-semibold text-slate-600">
            <a href="{{ route('matches.schedule', ['date' => now()->format('Y-m-d'), 'view' => 'today']) }}" 
               class="px-4 py-1.5 rounded-lg transition {{ $viewMode === 'today' || $date === now()->format('Y-m-d') ? 'bg-sky-100 text-sky-800 font-bold shadow-2xs' : 'hover:text-slate-900' }}">
                Today
            </a>
            <a href="{{ route('matches.schedule', ['date' => $date, 'view' => 'day']) }}" 
               class="px-4 py-1.5 rounded-lg transition {{ $viewMode === 'day' && $date !== now()->format('Y-m-d') ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'hover:text-slate-900' }}">
                Day
            </a>
            <a href="{{ route('matches.schedule', ['date' => $date, 'view' => 'week']) }}" 
               class="px-4 py-1.5 rounded-lg transition {{ $viewMode === 'week' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'hover:text-slate-900' }}">
                Week
            </a>
        </div>
    </div>

    <!-- Main Scheduler Split Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: Pitches / Timeline Grid (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            
            <!-- Table Timeline Header -->
            <div class="grid grid-cols-12 bg-[#edf2f9] border-b border-slate-200 text-xs font-semibold text-slate-700 py-3.5 px-4">
                <div class="col-span-2">Time</div>
                <div class="col-span-5">Pitch 1 (Main Stadium)</div>
                <div class="col-span-5">Pitch 2 (Cancha Turf)</div>
            </div>

            <!-- Time Slots -->
            <div class="divide-y divide-slate-100/90 text-sm">
                
                @php
                    $timeSlots = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
                @endphp

                @foreach ($timeSlots as $slot)
                    @php
                        // Filter matches for Pitch 1 and Pitch 2
                        $matchP1 = $matches->first(function($m) use ($slot) {
                            return ($m->field_number == 1 || $m->field_number == null) && $m->match_date->format('H:i') === $slot;
                        });
                        $matchP2 = $matches->first(function($m) use ($slot) {
                            return $m->field_number == 2 && $m->match_date->format('H:i') === $slot;
                        });
                    @endphp

                    <div class="grid grid-cols-12 min-h-22 items-stretch border-b border-slate-100/70 hover:bg-slate-50/40 transition">
                        
                        <!-- Time Label -->
                        <div class="col-span-2 p-3 text-xs font-semibold text-slate-400 border-r border-slate-100 flex items-start pt-3">
                            {{ $slot }}
                        </div>

                        <!-- Pitch 1 Column -->
                        <div class="col-span-5 p-2.5 border-r border-slate-100 flex flex-col justify-center">
                            @if ($matchP1)
                                <div class="bg-[#057a55] text-white p-3 rounded-xl shadow-xs transition hover:shadow-md cursor-pointer">
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="font-bold text-xs truncate">
                                            {{ $matchP1->homeTeam->name }} vs {{ $matchP1->awayTeam->name }}
                                        </div>
                                        <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0">
                                            U14
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-1.5 text-[11px] text-emerald-100 font-medium">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                        <span>Ref: {{ $matchP1->referee?->name ?? 'J. Smith' }}</span>
                                    </div>
                                </div>
                            @elseif ($slot === '09:00' && $matches->isEmpty())
                                <!-- Demo match from screenshot -->
                                <div class="bg-[#057a55] text-white p-3 rounded-xl shadow-xs transition hover:shadow-md cursor-pointer">
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="font-bold text-xs truncate">Eagles vs Falcons</div>
                                        <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0">U14</span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-1.5 text-[11px] text-emerald-100 font-medium">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                        <span>Ref: J. Smith</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Pitch 2 Column -->
                        <div class="col-span-5 p-2.5 flex flex-col justify-center">
                            @if ($matchP2)
                                <div class="bg-sky-100 text-slate-800 p-3 rounded-xl border border-sky-200 shadow-2xs transition hover:shadow-md cursor-pointer">
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="font-bold text-xs text-slate-900 truncate">
                                            {{ $matchP2->homeTeam->name }} vs {{ $matchP2->awayTeam->name }}
                                        </div>
                                        <span class="bg-white text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 border border-slate-200">
                                            U16
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-1.5 text-[11px] text-slate-600 font-medium">
                                        @if ($matchP2->referee)
                                            <span class="text-emerald-700">Ref: {{ $matchP2->referee->name }}</span>
                                        @else
                                            <span class="text-amber-700 flex items-center gap-1 font-semibold">
                                                <span>⚠</span> No Ref Assigned
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @elseif ($slot === '10:00' && $matches->isEmpty())
                                <!-- Demo match from screenshot -->
                                <div class="bg-sky-100 text-slate-800 p-3 rounded-xl border border-sky-200 shadow-2xs transition hover:shadow-md cursor-pointer">
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="font-bold text-xs text-slate-900 truncate">Tigers vs Lions</div>
                                        <span class="bg-white text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 border border-slate-200">U16</span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-1.5 text-[11px] text-slate-600 font-medium">
                                        <span class="text-amber-700 flex items-center gap-1 font-semibold">
                                            <span>⚠</span> No Ref Assigned
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                @endforeach

            </div>
        </div>

        <!-- Right: Schedule New Match Form Card (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden p-6">
            
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <div class="size-8 rounded-full border-2 border-emerald-600 text-emerald-600 flex items-center justify-center font-bold text-lg">
                    +
                </div>
                <h3 class="text-lg font-bold text-slate-900 leading-tight">
                    Schedule New<br>Match
                </h3>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('matches.schedule.store') }}" class="space-y-4">
                @csrf

                <!-- Teams Row -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Home Team</label>
                        <select name="home_team_id" required class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                            <option value="">Select Team...</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Away Team</label>
                        <select name="away_team_id" required class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                            <option value="">Select Team...</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Date & Time Row -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Date</label>
                        <input type="date" name="date" value="{{ $date }}" required 
                               class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Time</label>
                        <input type="time" name="time" value="09:00" required 
                               class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    </div>
                </div>

                <!-- Venue / Pitch -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Venue / Pitch</label>
                    <select name="field_number" class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                        <option value="1">Pitch 1 (Main Stadium)</option>
                        <option value="2">Pitch 2 (Cancha Sintética Turf)</option>
                        <option value="3">Pitch 3 (Cancha Alterna)</option>
                    </select>
                </div>

                <!-- Referee -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Referee</label>
                    <select name="referee_id" class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                        <option value="auto">Auto-Assign</option>
                        @foreach ($referees as $referee)
                            <option value="{{ $referee->id }}">{{ $referee->name }} (Nivel {{ $referee->certification_level }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions: Cancel & Schedule -->
                <div class="grid grid-cols-2 gap-3 pt-3">
                    <button type="reset" class="w-full py-2.5 px-4 bg-sky-100 hover:bg-sky-200 text-sky-900 text-sm font-semibold rounded-lg transition text-center cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="w-full py-2.5 px-4 bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-sm font-semibold rounded-lg shadow-xs transition text-center cursor-pointer">
                        Schedule
                    </button>
                </div>
            </form>

        </div>

    </div>

</div>
@endsection
