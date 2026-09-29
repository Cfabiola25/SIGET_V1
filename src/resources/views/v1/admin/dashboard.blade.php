@extends('v1.layouts.app')

@section('title', 'Overview')
@section('header_title', 'Overview')

@section('header_badge')
<div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
    <span>Season 2024</span>
    <svg class="size-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- 4 KPI Stat Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
        
        <!-- Total Teams -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">TOTAL TEAMS</span>
                <div class="size-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                    {{ number_format($totalTeamsCount) }}
                </div>
                <div class="mt-2 text-xs font-semibold text-emerald-700 flex items-center gap-1">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>+12 from last season</span>
                </div>
            </div>
        </div>

        <!-- Active Players -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">ACTIVE PLAYERS</span>
                <div class="size-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                    {{ number_format($activePlayersCount) }}
                </div>
                <div class="mt-2 text-xs font-semibold text-emerald-700 flex items-center gap-1">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>+5% engagement</span>
                </div>
            </div>
        </div>

        <!-- Upcoming Matches -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">UPCOMING MATCHES</span>
                <div class="size-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                    {{ $upcomingMatchesCount }}
                </div>
                <div class="mt-2 text-xs font-semibold text-slate-500">
                    Next 48 hours
                </div>
            </div>
        </div>

        <!-- Pending Results -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">PENDING RESULTS</span>
                <div class="size-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                    {{ $pendingResultsCount }}
                </div>
                <div class="mt-2 text-xs font-semibold text-rose-600 flex items-center gap-1">
                    <span>⚠</span>
                    <span>Requires validation</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Main Content Split (Recent Activity + Right Widgets) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: Recent Activity Card (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            
            <!-- Header -->
            <div class="p-5 md:px-6 md:py-4.5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">
                    Recent Activity
                </h2>
                <a href="{{ route('matches.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 transition">
                    View All
                </a>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-[#edf2f9] text-slate-700 text-xs font-semibold">
                            <th class="py-3 px-5">Match</th>
                            <th class="py-3 px-5 text-center">Result</th>
                            <th class="py-3 px-5">Referee</th>
                            <th class="py-3 px-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if ($recentMatches->isNotEmpty())
                            @foreach ($recentMatches as $m)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $m->homeTeam->name }}</span>
                                            <span class="text-slate-400 font-normal">vs</span>
                                            <span>{{ $m->awayTeam->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 text-center font-bold text-slate-900">
                                        {{ $m->status === 'played' ? ($m->home_score ?? 0).' - '.($m->away_score ?? 0) : ($m->status === 'in_progress' ? '0 - 0' : '--') }}
                                    </td>
                                    <td class="py-4 px-5 text-slate-600 font-medium">
                                        {{ $m->referee?->name ?? 'J. Smith' }}
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        @if ($m->status === 'played')
                                            <span class="inline-block bg-[#057a55] text-white text-[11px] font-bold px-3 py-0.5 rounded-full">
                                                Final
                                            </span>
                                        @elseif ($m->status === 'in_progress')
                                            <span class="inline-block bg-sky-100 text-sky-800 text-[11px] font-bold px-3 py-0.5 rounded-full">
                                                In Progress
                                            </span>
                                        @else
                                            <span class="inline-block bg-slate-100 text-slate-700 text-[11px] font-bold px-3 py-0.5 rounded-full">
                                                Upcoming
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <!-- Demo Rows matching screenshot -->
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-5 font-semibold text-slate-900">
                                    Lions vs Tigers
                                </td>
                                <td class="py-4 px-5 text-center font-bold text-slate-900">
                                    2 - 1
                                </td>
                                <td class="py-4 px-5 text-slate-600 font-medium">
                                    J. Smith
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <span class="inline-block bg-[#057a55] text-white text-[11px] font-bold px-3 py-0.5 rounded-full">
                                        Final
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-5 font-semibold text-slate-900">
                                    Eagles vs Hawks
                                </td>
                                <td class="py-4 px-5 text-center font-bold text-slate-900">
                                    0 - 0
                                </td>
                                <td class="py-4 px-5 text-slate-600 font-medium">
                                    A. Davis
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <span class="inline-block bg-sky-100 text-sky-800 text-[11px] font-bold px-3 py-0.5 rounded-full">
                                        In Progress
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-5 font-semibold text-slate-900">
                                    Sharks vs Dolphins
                                </td>
                                <td class="py-4 px-5 text-center font-bold text-slate-500">
                                    --
                                </td>
                                <td class="py-4 px-5 text-slate-600 font-medium">
                                    M. Johnson
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <span class="inline-block bg-slate-100 text-slate-700 text-[11px] font-bold px-3 py-0.5 rounded-full">
                                        Upcoming
                                    </span>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Right: Urgent Tasks & Tournament Progress (1 Col) -->
        <div class="space-y-6">
            
            <!-- Urgent Tasks Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5">
                <div class="flex items-center gap-2 text-rose-600 mb-4">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    <h3 class="text-base font-bold text-slate-900">Urgent Tasks</h3>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 space-y-3 bg-slate-50/50">
                    <h4 class="text-sm font-bold text-slate-900">Matchday Closure</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Review {{ $pendingResultsCount }} pending results from Weekend League.
                    </p>
                    <a href="{{ route('matches.index') }}" class="block w-full py-2.5 px-4 bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs font-bold rounded-lg transition text-center shadow-xs">
                        Review Results
                    </a>
                </div>
            </div>

            <!-- Tournament Progress Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 text-center flex flex-col items-center">
                <h3 class="text-base font-bold text-slate-900 w-full text-left mb-6">Tournament Progress</h3>
                
                <!-- Circular Radial Gauge -->
                <div class="relative size-36 flex items-center justify-center">
                    <svg class="size-full -rotate-90" viewBox="0 0 36 36">
                        <!-- Background Circle -->
                        <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <!-- Progress Arc -->
                        <path class="text-[#057a55]" stroke-width="3.5" stroke-dasharray="{{ $progressPercentage }}, 100" stroke-linecap="round" stroke="currentColor" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <div class="absolute text-3xl font-black text-slate-900">
                        {{ $progressPercentage }}%
                    </div>
                </div>

                <p class="mt-4 text-xs text-slate-500 font-medium">
                    Group Stage nearing completion.
                </p>
            </div>

        </div>

    </div>

</div>
@endsection
