@extends('v1.layouts.app')

@section('title', 'Tournament Statistics')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tournament Statistics</h1>
            <p class="text-xs text-slate-500 mt-0.5">Performance analytics, top goal scorers, and disciplinary indicators.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-800">
                <span class="size-1.5 rounded-full bg-emerald-600"></span>
                Official IFAB Feed
            </span>
        </div>
    </div>

    <!-- 4 KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Total Goals</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalGoals }}</div>
            <span class="inline-flex items-center text-[11px] text-emerald-700 font-medium mt-1">{{ $goalsPerMatch }} goals / match</span>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Matches Played</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalMatches }}</div>
            <span class="inline-flex items-center text-[11px] text-slate-500 font-medium mt-1">Official fixtures</span>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Yellow Cards</span>
            <div class="text-3xl font-extrabold text-amber-600 mt-2">{{ $totalYellowCards }}</div>
            <span class="inline-flex items-center text-[11px] text-slate-500 font-medium mt-1">Disciplinary warnings</span>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Red Cards</span>
            <div class="text-3xl font-extrabold text-rose-600 mt-2">{{ $totalRedCards }}</div>
            <span class="inline-flex items-center text-[11px] text-rose-600 font-medium mt-1">Expulsions & suspensions</span>
        </div>
    </div>

    <!-- Two Leaderboard Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Scorers -->
        <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-emerald-50 text-emerald-700 grid place-items-center text-sm font-bold border border-emerald-200">
                        ⚽
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Top Goalscorers (Goleadores)</h3>
                        <p class="text-[11px] text-slate-400">Official Golden Boot leaderboard</p>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($topScorers as $scorer)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50/60 transition">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-6 items-center justify-center rounded-full {{ $loop->iteration === 1 ? 'bg-amber-100 text-amber-800 font-black' : 'bg-slate-100 text-slate-600 font-bold' }} text-xs">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <p class="font-bold text-slate-900 text-xs">{{ $scorer->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $scorer->team?->name ?? 'Agente Libre' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono font-bold text-xs">
                                {{ $scorer->goals_count ?? 0 }} ⚽
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-xs text-slate-400">
                        No hay goles registrados todavía.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Disciplinary Cards Leaderboard -->
        <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-amber-50 text-amber-700 grid place-items-center text-sm font-bold border border-amber-200">
                        🟨
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Disciplinary Sanctions</h3>
                        <p class="text-[11px] text-slate-400">Most penalized players</p>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($topCarded as $player)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50/60 transition">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-6 items-center justify-center rounded-full bg-slate-100 text-slate-600 font-bold text-xs">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <p class="font-bold text-slate-900 text-xs">{{ $player->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $player->team?->name ?? 'Sin equipo' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (($player->yellow_cards ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">
                                    {{ $player->yellow_cards }} 🟨
                                </span>
                            @endif
                            @if (($player->red_cards ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-800 border border-rose-200 text-xs font-bold">
                                    {{ $player->red_cards }} 🟥
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-xs text-slate-400">
                        No hay tarjetas registradas en el torneo.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
