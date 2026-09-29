@extends('v1.layouts.app')

@section('title', 'Auditing - Referee Submissions Review')

@section('content')
<div class="space-y-6">
    <!-- Header with Badges -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Auditing</h1>
                <span class="rounded-full bg-rose-600 text-white text-[11px] font-black px-2.5 py-0.5 shadow-xs">
                    {{ $stats['total_pending'] }} PENDING
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Referee Submissions Review, Disciplinary Audits & System Activity Trail.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-800">
                <span class="size-1.5 rounded-full bg-emerald-600"></span>
                Auditoría Activa v1.0
            </span>
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
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Pending Reviews</span>
            <div class="text-3xl font-extrabold text-rose-600 mt-2">{{ $stats['total_pending'] }}</div>
            <span class="inline-flex items-center text-[11px] text-rose-600 font-medium mt-1">Requires official intervention</span>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Disputed Scores</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $stats['disputed_count'] }}</div>
            <span class="inline-flex items-center text-[11px] text-amber-600 font-medium mt-1">Goal timing dispute</span>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Red Card Sanctions</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $stats['red_cards_pending'] }}</div>
            <span class="inline-flex items-center text-[11px] text-slate-500 font-medium mt-1">Mandatory suspension</span>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Resolved Today</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $stats['resolved_today'] }}</div>
            <span class="inline-flex items-center text-[11px] text-emerald-700 font-medium mt-1">Cleared by tournament director</span>
        </div>
    </div>

    <!-- Active Referee Submissions Review Section (From Screenshots) -->
    <div class="space-y-4">
        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <svg class="size-4 text-[#057a55]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Pending Referee Submissions Review
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($pendingAudits as $audit)
                <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3">
                            <div>
                                <span class="text-sm font-bold text-slate-900">Match #{{ $audit['match_id'] }}</span>
                                <p class="text-[11px] text-slate-400 font-medium">Submitted by <strong class="text-slate-600">{{ $audit['referee'] }}</strong></p>
                            </div>
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border
                                {{ $audit['badge_color'] === 'rose' ? 'border-rose-300 text-rose-700 bg-rose-50' : ($audit['badge_color'] === 'amber' ? 'border-amber-300 text-amber-700 bg-amber-50' : 'border-slate-300 text-slate-700 bg-slate-50') }}">
                                {{ $audit['badge'] }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed mt-3">
                            {{ $audit['summary'] }}
                        </p>
                    </div>

                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between gap-2">
                        <span class="text-[10px] font-mono text-slate-400">{{ $audit['time_ago'] }}</span>
                        <div class="flex items-center gap-2">
                            @if ($audit['action_secondary'])
                                <a href="{{ $audit['route_secondary'] ?? '#' }}" class="rounded-lg bg-sky-50 hover:bg-sky-100 border border-sky-200 px-3 py-1.5 text-xs font-semibold text-sky-800 transition">
                                    {{ $audit['action_secondary'] }}
                                </a>
                            @endif
                            <button type="button" onclick="alert('Acción completada: {{ $audit['action_primary'] }} en Partido #{{ $audit['match_id'] }}'); this.innerText = '✓ Resuelto'; this.disabled = true; this.className = 'rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 text-xs font-bold';" class="rounded-lg border border-[#057a55] text-[#057a55] hover:bg-[#057a55] hover:text-white px-3.5 py-1.5 text-xs font-semibold transition">
                                {{ $audit['action_primary'] }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- System Activity Trail Table -->
    <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">System Activity & Audit Trail</h3>
                <p class="text-xs text-slate-500">Immutable trace of changes, disciplinary pardons, and score modifications.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
                Live Audit Logs
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-600 font-semibold text-[11px] uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3 px-5">Timestamp</th>
                        <th class="py-3 px-5">Operator / Role</th>
                        <th class="py-3 px-5">Action</th>
                        <th class="py-3 px-5">Target Entity</th>
                        <th class="py-3 px-5">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-5 font-mono text-slate-500">{{ now()->subMinutes(12)->format('d/m/Y H:i:s') }}</td>
                        <td class="py-3 px-5 font-bold text-slate-900">Tournament Director</td>
                        <td class="py-3 px-5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">MATCH_SCHEDULED</span></td>
                        <td class="py-3 px-5 font-medium text-slate-800">MatchGame #109</td>
                        <td class="py-3 px-5 text-slate-500">Scheduled on Pitch 1 (Main Stadium) at 10:00</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-5 font-mono text-slate-500">{{ now()->subMinutes(34)->format('d/m/Y H:i:s') }}</td>
                        <td class="py-3 px-5 font-bold text-slate-900">Admin User</td>
                        <td class="py-3 px-5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">PLAYER_CREATED</span></td>
                        <td class="py-3 px-5 font-medium text-slate-800">Player PLY-042</td>
                        <td class="py-3 px-5 text-slate-500">New registration assigned to SF Lions</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-5 font-mono text-slate-500">{{ now()->subHours(2)->format('d/m/Y H:i:s') }}</td>
                        <td class="py-3 px-5 font-bold text-slate-900">Ref. Thomas</td>
                        <td class="py-3 px-5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">ACTA_CLOSED</span></td>
                        <td class="py-3 px-5 font-medium text-slate-800">MatchGame #842</td>
                        <td class="py-3 px-5 text-slate-500">Tripartite signatures stored. Final score 2-1</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-5 font-mono text-slate-500">{{ now()->subHours(4)->format('d/m/Y H:i:s') }}</td>
                        <td class="py-3 px-5 font-bold text-slate-900">Disciplinary Committee</td>
                        <td class="py-3 px-5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">SANCTION_ISSUED</span></td>
                        <td class="py-3 px-5 font-medium text-slate-800">Player #9 (Valley Heights)</td>
                        <td class="py-3 px-5 text-slate-500">1 Match suspension for direct red card</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
