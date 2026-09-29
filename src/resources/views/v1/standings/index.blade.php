@extends('v1.layouts.app')

@section('title', 'League Standings')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">League Standings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Official tournament points table, goal differential, and qualification brackets.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-800">
                <span class="size-1.5 rounded-full bg-emerald-600"></span>
                Calculated In Real-Time
            </span>
        </div>
    </div>

    <!-- Table Container -->
    <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Official Table</h3>
                <p class="text-[11px] text-slate-400">PTS: Win (3), Draw (1), Loss (0)</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <div class="flex items-center gap-1.5 text-emerald-700">
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                    <span class="font-medium text-[11px]">Playoffs Zone (Top 4)</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-600 font-semibold text-[11px] uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3 px-5 text-center w-12">#</th>
                        <th class="py-3 px-5">Team / Club</th>
                        <th class="py-3 px-4 text-center">MP</th>
                        <th class="py-3 px-4 text-center">W</th>
                        <th class="py-3 px-4 text-center">D</th>
                        <th class="py-3 px-4 text-center">L</th>
                        <th class="py-3 px-4 text-center">GF</th>
                        <th class="py-3 px-4 text-center">GA</th>
                        <th class="py-3 px-4 text-center">GD</th>
                        <th class="py-3 px-5 text-center font-bold">PTS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($standings as $standing)
                        @php
                            $gd = $standing->goals_for - $standing->goals_against;
                            $isPlayoff = $loop->iteration <= 4;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition {{ $isPlayoff ? 'bg-emerald-50/20' : '' }}">
                            <td class="py-3.5 px-5 text-center font-bold">
                                @if ($loop->iteration === 1)
                                    <span class="inline-flex size-6 items-center justify-center rounded-full bg-amber-100 text-amber-800 text-xs font-black">
                                        1
                                    </span>
                                @elseif ($isPlayoff)
                                    <span class="inline-flex size-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                        {{ $loop->iteration }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-mono text-xs">
                                        {{ $loop->iteration }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-slate-100 border border-slate-200 grid place-items-center text-xs font-bold text-slate-700">
                                        ⚽
                                    </div>
                                    <div>
                                        <a href="{{ route('teams.show', $standing->team) }}" class="font-bold text-slate-900 hover:text-[#057a55] transition hover:underline">
                                            {{ $standing->team->name }}
                                        </a>
                                        <p class="text-[10px] text-slate-400">{{ $standing->tournament->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-medium text-slate-700">{{ $standing->matches_played }}</td>
                            <td class="py-3.5 px-4 text-center font-medium text-slate-700">{{ $standing->wins }}</td>
                            <td class="py-3.5 px-4 text-center font-medium text-slate-700">{{ $standing->draws }}</td>
                            <td class="py-3.5 px-4 text-center font-medium text-slate-700">{{ $standing->losses }}</td>
                            <td class="py-3.5 px-4 text-center text-slate-500 font-mono">{{ $standing->goals_for }}</td>
                            <td class="py-3.5 px-4 text-center text-slate-500 font-mono">{{ $standing->goals_against }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-semibold {{ $gd > 0 ? 'text-emerald-700' : ($gd < 0 ? 'text-rose-600' : 'text-slate-500') }}">
                                {{ $gd > 0 ? '+' . $gd : $gd }}
                            </td>
                            <td class="py-3.5 px-5 text-center font-mono text-sm font-extrabold text-slate-900">
                                {{ $standing->points }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                Todavía no hay posiciones calculadas para esta temporada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
