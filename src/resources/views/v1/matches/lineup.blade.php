@extends('v1.layouts.app')

@section('title', 'Alineación Digital: ' . $team->name)

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('matches.show', $match) }}" class="text-xs font-semibold uppercase tracking-wider text-[#057a55] hover:underline flex items-center gap-1">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al Partido
            </a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 md:text-3xl">Alineación Oficial: {{ $team->name }}</h1>
            <p class="text-xs md:text-sm text-slate-500">
                {{ $match->homeTeam?->name ?? 'Equipo Local' }} vs {{ $match->awayTeam?->name ?? 'Equipo Visitante' }} • {{ $match->match_date?->format('d/m/Y H:i') ?? 'Fecha por definir' }}
            </p>
        </div>

        <div>
            @if ($isLocked)
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700 shadow-2xs">
                    <x-reicon name="lock" class="size-4 text-rose-600" /> Envío Bloqueado por Tiempo Reglamentario
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-800 shadow-2xs">
                    <x-reicon name="clock" class="size-3.5 text-emerald-600" /> Cierre: {{ $lockMinutes }} min antes del partido
                </span>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-2xs">
            <div class="font-bold">No se pudo guardar la alineación:</div>
            <ul class="mt-1.5 list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($isLocked)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center text-slate-700 shadow-2xs">
            <x-reicon name="alert-triangle" class="size-8 text-amber-600 mx-auto" />
            <h2 class="mt-2 text-base font-bold text-slate-900">Plazo de Envío Finalizado</h2>
            <p class="text-xs text-slate-600 mt-1 max-w-xl mx-auto">
                El reglamento del torneo estipula que las alineaciones deben confirmarse al menos {{ $lockMinutes }} minutos antes del inicio.
                Para modificaciones excepcionales o contingencias, acude directamente con la mesa de control o el árbitro oficial.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('matches.lineup.store', [$match, $team]) }}">
        @csrf

        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Convocatoria del Plantel (Filtro Tripartito)</h2>
                    <p class="text-xs text-slate-500">Selecciona hasta 11 titulares y los suplentes reglamentarios.</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-500">Total en Plantel: <strong class="text-slate-900">{{ $players->count() }}</strong></span>
                </div>
            </div>

            @if ($players->isEmpty())
                <div class="p-8 text-center text-xs text-slate-500">
                    No tienes jugadores registrados en el plantel de {{ $team->name }}.
                    <a href="{{ route('dt.roster', $team) }}" class="text-[#057a55] font-bold hover:underline block mt-2">Ir a registrar jugadores &rarr;</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">Dorsal</th>
                                <th class="px-4 py-3">Jugador</th>
                                <th class="px-4 py-3">Filtro de Habilitación</th>
                                <th class="px-4 py-3 text-center">Titular</th>
                                <th class="px-4 py-3 text-center">Suplente</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($players as $player)
                                @php
                                    $check = $eligibilityData[$player->id] ?? ['eligible' => true, 'reasons' => []];
                                    $isEligible = $check['eligible'];
                                    $isStarter = in_array($player->id, old('starters', $currentStarters), true);
                                    $isSub = in_array($player->id, old('substitutes', $currentSubs), true);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors {{ ! $isEligible ? 'bg-rose-50/40 text-slate-400' : '' }}">
                                    <td class="px-4 py-3 font-mono font-black text-[#057a55]">
                                        #{{ $player->jersey_number }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-900">{{ $player->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $player->identification_document }} • {{ $player->profile?->position_label ?? 'Jugador' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($isEligible)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                                                <x-reicon name="check" class="size-3 text-emerald-600" /> Habilitado
                                            </span>
                                        @else
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-[11px] font-bold text-rose-800 border border-rose-200">
                                                    <x-reicon name="alert-triangle" class="size-3 text-rose-600" /> Inhabilitado
                                                </span>
                                                <ul class="text-[10px] text-rose-600 list-disc list-inside">
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
                                               class="size-4 rounded border-slate-300 text-[#057a55] focus:ring-[#057a55]">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" name="substitutes[]" value="{{ $player->id }}"
                                               {{ $isSub ? 'checked' : '' }}
                                               {{ ! $isEligible || $isLocked ? 'disabled' : '' }}
                                               class="size-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
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
                <a href="{{ route('matches.show', $match) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#057a55] px-6 py-2.5 text-xs font-bold text-white shadow-2xs hover:bg-[#046c4b] transition cursor-pointer">
                    Confirmar y Enviar Alineación Oficial
                </button>
            </div>
        @endif
    </form>
</div>
@endsection
