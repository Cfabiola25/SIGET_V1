@extends('v1.layouts.app')

@section('title', $tournament->name)
@section('header_title', $tournament->name)

@section('header_badge')
<span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
    {{ $tournament->status }}
</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Tournament Overview Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-800">
                    <a href="{{ route('tournaments.index') }}" class="hover:underline">&larr; Tournaments</a>
                    <span>•</span>
                    <span>{{ $tournament->sport_type }}</span>
                </div>
                <h2 class="mt-1.5 text-2xl md:text-3xl font-black text-slate-900 tracking-tight">{{ $tournament->name }}</h2>
                <p class="mt-1.5 text-xs md:text-sm text-slate-500">
                    Dates: {{ $tournament->start_date?->format('d M Y') }} — {{ $tournament->end_date?->format('d M Y') }}
                    @if ($tournament->admin) • Tournament Director: <strong class="text-slate-700 font-semibold">{{ $tournament->admin->name }}</strong> @endif
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('matches.schedule') }}" class="px-4 py-2 rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Schedule Matches</span>
                </a>
                <a href="{{ route('tournaments.brackets', $tournament) }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                    <span>🏆 Brackets</span>
                </a>
                <a href="{{ route('tournaments.disciplinary', $tournament) }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                    <span>⚖️ Disciplinary</span>
                </a>
                <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                    <span>⚙️ Rules</span>
                </a>
                <a href="{{ route('standings.index', ['tournament' => $tournament->id]) }}" class="px-4 py-2 rounded-lg bg-sky-100 text-sky-900 text-xs font-bold hover:bg-sky-200 transition">
                    Leaderboard
                </a>
            </div>
        </div>
    </div>

    <!-- Active Tournament Rules Widget -->
    @if ($tournament->rules)
        @php $r = $tournament->rules; @endphp
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-lg">⚖️</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Tournament Technical Regulations</h3>
                </div>
                <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 transition">
                    Edit Rules &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Yellow Card Limit</span>
                    <div class="mt-1 text-lg font-bold text-amber-700">{{ $r->yellow_card_limit_for_suspension }} cards</div>
                    <span class="text-[11px] text-slate-400">= 1 match suspension</span>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Points System</span>
                    <div class="mt-1 text-lg font-bold text-emerald-800">{{ $r->points_for_win }}W / {{ $r->points_for_draw }}D / {{ $r->points_for_loss }}L</div>
                    <span class="text-[11px] text-slate-400">Standard classification</span>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Match Duration</span>
                    <div class="mt-1 text-lg font-bold text-slate-900">{{ $r->match_duration_minutes }} minutes</div>
                    <span class="text-[11px] text-slate-400">Regular regulation time</span>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Max Substitutions</span>
                    <div class="mt-1 text-lg font-bold text-sky-800">{{ $r->max_substitutions }} subs</div>
                    <span class="text-[11px] text-slate-400">Per squad per game</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Split Grid: Registered Teams & Fixtures -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Teams in Tournament -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Registered Teams ({{ $tournament->teams->count() }})</h3>
                <a href="{{ route('teams.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">
                    Manage Teams &rarr;
                </a>
            </div>

            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($tournament->teams as $team)
                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center border border-emerald-200">
                                {{ strtoupper(substr($team->name, 0, 2)) }}
                            </div>
                            <div>
                                <a href="{{ route('teams.show', $team) }}" class="font-bold text-sm text-slate-900 hover:text-emerald-700 transition">
                                    {{ $team->name }}
                                </a>
                                <p class="text-xs text-slate-500">
                                    Coach: {{ $team->coach_name }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('teams.show', $team) }}" class="px-3 py-1 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            View Team
                        </a>
                    </div>
                @empty
                    <p class="py-6 text-xs text-slate-500 text-center">No teams registered yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Matches & Fixtures -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Matches & Schedule ({{ $tournament->matches->count() }})</h3>
                <a href="{{ route('matches.schedule') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">
                    Calendar View &rarr;
                </a>
            </div>

            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($tournament->matches->take(6) as $match)
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-900">
                                {{ $match->homeTeam->name }}
                                @if ($match->status === 'played')
                                    <span class="text-emerald-700 font-black">({{ $match->home_score }} - {{ $match->away_score }})</span>
                                @else
                                    <span class="text-slate-400 font-normal">vs</span>
                                @endif
                                {{ $match->awayTeam->name }}
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $match->match_date->format('d M Y · H:i') }}
                                @if ($match->venue) • 📍 {{ $match->venue->name }} @endif
                            </p>
                        </div>
                        <a href="{{ route('matches.show', $match) }}" class="px-3 py-1 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Details
                        </a>
                    </div>
                @empty
                    <p class="py-6 text-xs text-slate-500 text-center">No matches scheduled yet.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
