@extends('v1.layouts.app')

@section('title', 'Tribunal de Penas y Disciplina')
@section('header_title', 'Tribunal de Penas y Disciplina')

@section('content')
<div class="space-y-6">

    <!-- Header bar -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-800">
                <a href="{{ route('tournaments.show', $tournament) }}" class="hover:underline">&larr; Volver a {{ $tournament->name }}</a>
            </div>
            <h2 class="mt-1 text-2xl font-black text-slate-900 tracking-tight">Tribunal Disciplinario & Fair Play</h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Automatización reglamentaria IFAB de suspensiones e inhabilitaciones en competencia.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold">
                <span>🟨</span> Límite Amarillas: {{ $tournament->rules?->yellow_card_limit_for_suspension ?? 2 }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-900 text-xs font-bold">
                <span>🟥</span> Suspensión Roja: {{ $tournament->rules?->direct_red_suspension_matches ?? 1 }} fecha(s)
            </span>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-5">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Inhabilitados Vigentes</span>
            <div class="mt-2 text-3xl font-black text-slate-900">{{ $activeSanctions->count() }}</div>
            <span class="text-xs text-slate-400 mt-1 block">jugadores suspendidos para la fecha</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Total Tarjetas Amarillas</span>
            <div class="mt-2 text-3xl font-black text-slate-900">{{ $yellowCardLeaders->sum('yellow_cards_count') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">amonestaciones acumuladas</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-bold uppercase tracking-wider text-purple-600">Total Tarjetas Rojas</span>
            <div class="mt-2 text-3xl font-black text-slate-900">{{ $redCardLeaders->sum('red_cards_count') }}</div>
            <span class="text-xs text-slate-400 mt-1 block">expulsiones directas o dobles</span>
        </div>
    </div>

    <!-- Active Sanctions Table -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900">Sanciones Activas (Inhabilitaciones para Próxima Fecha)</h3>
            <span class="text-xs font-semibold text-slate-500">Filtro Tripartito oficial</span>
        </div>

        @if ($activeSanctions->isEmpty())
            <div class="p-8 text-center text-slate-500 italic">
                <span class="text-3xl block mb-2">🛡️</span>
                No hay jugadores con sanciones disciplinarias activas en este momento. Fair play impecable.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-[#edf2f9] text-slate-700 text-xs font-semibold">
                            <th class="py-3 px-5">Jugador</th>
                            <th class="py-3 px-5">Equipo</th>
                            <th class="py-3 px-5">Causa de Sanción</th>
                            <th class="py-3 px-5 text-center">Fechas</th>
                            <th class="py-3 px-5 text-center">Restantes</th>
                            <th class="py-3 px-5">Detalle / Origen</th>
                            <th class="py-3 px-5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($activeSanctions as $sanction)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-5 font-semibold text-slate-900">
                                    {{ $sanction->player->name }}
                                </td>
                                <td class="py-3.5 px-5 text-slate-600 font-medium">
                                    {{ $sanction->player->team?->name }}
                                </td>
                                <td class="py-3.5 px-5">
                                    @if ($sanction->sanction_type === 'direct_red')
                                        <span class="inline-block bg-[#fee2e2] text-[#991b1b] text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase">
                                            🟥 Roja Directa
                                        </span>
                                    @elseif ($sanction->sanction_type === 'double_yellow')
                                        <span class="inline-block bg-amber-100 text-amber-900 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase">
                                            🟨🟨 Doble Amarilla
                                        </span>
                                    @else
                                        <span class="inline-block bg-yellow-100 text-yellow-900 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase">
                                            🟨 Acumulación Amarillas
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-center font-bold text-slate-800">
                                    {{ $sanction->matches_suspended }}
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="px-2 py-0.5 rounded font-black text-xs bg-rose-100 text-rose-800 border border-rose-200">
                                        {{ $sanction->remainingMatches() }} fecha(s)
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-xs text-slate-500 max-w-xs truncate">
                                    {{ $sanction->notes }}
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <form action="{{ route('tournaments.disciplinary.pardon', [$tournament, $sanction]) }}" method="POST" onsubmit="return confirm('¿Confirma indultar administrativamente esta sanción?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold transition cursor-pointer">
                                            Indultar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Cards Leaderboards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Yellow Cards Ranking -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span>🟨</span> Tarjetas Amarillas Acumuladas
                </h3>
                <span class="text-xs text-slate-400">Límite: {{ $tournament->rules?->yellow_card_limit_for_suspension ?? 2 }}</span>
            </div>

            @if ($yellowCardLeaders->isEmpty())
                <p class="text-xs text-slate-400 py-6 italic text-center">Sin amonestaciones registradas.</p>
            @else
                <div class="divide-y divide-slate-100 text-xs">
                    @foreach ($yellowCardLeaders as $player)
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="size-6 rounded bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center">#{{ $player->jersey_number }}</span>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $player->name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $player->team?->name }}</p>
                                </div>
                            </div>
                            <span class="size-7 rounded-lg bg-yellow-100 border border-yellow-200 font-bold text-yellow-900 flex items-center justify-center">
                                {{ $player->yellow_cards_count }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Red Cards Ranking -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span>🟥</span> Tarjetas Rojas en el Torneo
                </h3>
                <span class="text-xs text-slate-400">Expulsiones</span>
            </div>

            @if ($redCardLeaders->isEmpty())
                <p class="text-xs text-slate-400 py-6 italic text-center">Sin tarjetas rojas registradas.</p>
            @else
                <div class="divide-y divide-slate-100 text-xs">
                    @foreach ($redCardLeaders as $player)
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="size-6 rounded bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center">#{{ $player->jersey_number }}</span>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $player->name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $player->team?->name }}</p>
                                </div>
                            </div>
                            <span class="size-7 rounded-lg bg-rose-100 border border-rose-200 font-bold text-rose-900 flex items-center justify-center">
                                {{ $player->red_cards_count }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
