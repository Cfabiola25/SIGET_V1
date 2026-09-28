@extends('v1.layouts.app')

@section('title', 'Alineación Digital: ' . $team->name)

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('matches.show', $match) }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
                ← Volver al Partido
            </a>
            <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">Alineación Oficial: {{ $team->name }}</h1>
            <p class="text-sm text-slate-400">
                {{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }} • {{ $match->match_date->format('d/m/Y H:i') }}
            </p>
        </div>

        <div>
            @if ($isLocked)
                <span class="rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-2 text-xs font-black text-rose-400">
                    🔒 Envío Bloqueado por Tiempo Reglamentario
                </span>
            @else
                <span class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-2 text-xs font-black text-emerald-400">
                    ⏱️ Cierre: {{ $lockMinutes }} min antes del pitazo
                </span>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-500/40 bg-rose-500/10 p-5 text-sm text-rose-400">
            <div class="font-bold">No se pudo guardar la alineación:</div>
            <ul class="mt-2 list-disc list-inside space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($isLocked)
        <div class="rounded-2xl border border-rose-500/30 bg-rose-950/30 p-6 text-center text-slate-300">
            <span class="text-3xl">⛔</span>
            <h2 class="mt-2 text-base font-bold text-white">Plazo de Envío Finalizado</h2>
            <p class="text-xs text-slate-400 mt-1">
                El reglamento del torneo estipula que las alineaciones deben confirmarse al menos {{ $lockMinutes }} minutos antes del inicio.
                Para modificaciones excepcionales, acude directamente con la terna arbitral.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('matches.lineup.store', [$match, $team]) }}">
        @csrf

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-base font-bold text-white">Convocatoria del Plantel (Filtro Tripartito)</h2>
                    <p class="text-xs text-slate-400">Selecciona hasta 11 titulares y los suplentes autorizados.</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400">Total en Plantel: <strong class="text-white">{{ $players->count() }}</strong></span>
                </div>
            </div>

            @if ($players->isEmpty())
                <div class="p-8 text-center text-sm text-slate-400">
                    No tienes jugadores registrados en el plantel de {{ $team->name }}.
                    <a href="{{ route('dt.roster', $team) }}" class="text-emerald-400 font-bold hover:underline block mt-2">Ir a cargar jugadores →</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="border-b border-slate-800 text-xs uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Dorsal</th>
                                <th class="px-4 py-3">Jugador</th>
                                <th class="px-4 py-3">Filtro de Habilitación</th>
                                <th class="px-4 py-3 text-center">Titular</th>
                                <th class="px-4 py-3 text-center">Suplente</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach ($players as $player)
                                @php
                                    $check = $eligibilityData[$player->id] ?? ['eligible' => true, 'reasons' => []];
                                    $isEligible = $check['eligible'];
                                    $isStarter = in_array($player->id, old('starters', $currentStarters), true);
                                    $isSub = in_array($player->id, old('substitutes', $currentSubs), true);
                                @endphp
                                <tr class="hover:bg-slate-800/20 {{ ! $isEligible ? 'opacity-60 bg-rose-950/10' : '' }}">
                                    <td class="px-4 py-3 font-mono font-black text-emerald-400">
                                        #{{ $player->jersey_number }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-white">{{ $player->name }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $player->identification_document }} • {{ $player->profile?->position_label ?? 'Jugador' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($isEligible)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-bold text-emerald-400 border border-emerald-500/30">
                                                ✅ Habilitado
                                            </span>
                                        @else
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-500/20 px-2.5 py-0.5 text-xs font-bold text-rose-400 border border-rose-500/30">
                                                    ⛔ Inhabilitado
                                                </span>
                                                <ul class="text-[11px] text-rose-400 list-disc list-inside">
                                                    @foreach ($check['reasons'] as $reason)
                                                        <li>{{ $reason }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" name="starters[]" value="{{ $player->id }}"
                                               {{ $isStarter ? 'checked' : '' }}
                                               {{ ! $isEligible || $isLocked ? 'disabled' : '' }}
                                               class="size-5 rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500/20">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" name="substitutes[]" value="{{ $player->id }}"
                                               {{ $isSub ? 'checked' : '' }}
                                               {{ ! $isEligible || $isLocked ? 'disabled' : '' }}
                                               class="size-5 rounded border-slate-700 bg-slate-950 text-blue-500 focus:ring-blue-500/20">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if (! $isLocked && ! $players->isEmpty())
            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('matches.show', $match) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-700 transition">
                    Cancelar
                </a>
                <button type="submit" class="rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/20 hover:bg-emerald-400 transition">
                    Confirmar y Enviar Alineación Oficial
                </button>
            </div>
        @endif
    </form>
</div>
@endsection
