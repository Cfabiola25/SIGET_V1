@extends('v1.layouts.app')

@section('title', 'Profesionalización Arbitral & Asignación Algorítmica')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">
                    <span class="size-1.5 rounded-full bg-emerald-400"></span>
                    INTELIGENCIA ARBITRAL
                </span>
                <span class="text-xs text-slate-500">Evaluaciones DT • Anti-Conflictos</span>
            </div>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Panel de Arbitraje Profesional</h1>
            <p class="text-sm text-slate-400">Control de calificaciones post-partido, prevención de conflictos de interés y motor de asignación algorítmica.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-300">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-300">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Actions & Automation Section -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Motor de Asignación Masiva por Jornada -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-xl">
            <div class="flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-black">
                    ⚙
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Asignación Algorítmica por Jornada</h3>
                    <p class="text-xs text-slate-400">Designa árbitros evitando colisiones de agenda y conflictos con los clubes.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('tournaments.referees.bulk_assign') }}" class="mt-5 space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Torneo</label>
                        <select name="tournament_id" required class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none">
                            @foreach($tournaments as $tourn)
                                <option value="{{ $tourn->id }}">{{ $tourn->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Número de Jornada</label>
                        <input type="number" name="round_number" value="1" min="1" required class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 py-2.5 text-xs font-black text-slate-950 shadow-md shadow-emerald-500/20 hover:from-emerald-400 transition">
                    Ejecutar Motor Algorítmico de Asignación
                </button>
            </form>
        </div>

        <!-- Registro de Conflicto de Interés -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-xl">
            <div class="flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 font-black">
                    ⚠
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Declaración de Conflicto de Interés</h3>
                    <p class="text-xs text-slate-400">Bloquea formalmente la designación de un árbitro con un club determinado.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('referees.conflicts.store') }}" class="mt-5 space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Árbitro</label>
                        <select name="referee_id" required class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 focus:border-amber-500 focus:outline-none">
                            @foreach($referees as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ number_format($r->rating_average, 2) }} ★)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Club con Conflicto</label>
                        <select name="team_id" required class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 focus:border-amber-500 focus:outline-none">
                            @foreach($teams as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <input type="text" name="reason" placeholder="Motivo (ej: Vínculo familiar, incidente previo, club de origen)..." required class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 focus:border-amber-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full rounded-xl border border-amber-500/50 bg-amber-500/10 py-2.5 text-xs font-bold text-amber-400 hover:bg-amber-500/20 transition">
                    Registrar Bloqueo de Conflicto
                </button>
            </form>
        </div>
    </div>

    <!-- Tabla de Árbitros & Calificaciones -->
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Escalafón y Ratings de Colegiados</h3>
            <span class="text-xs text-slate-400">Total: {{ $referees->count() }} árbitros registrados</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase font-bold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Árbitro</th>
                        <th class="px-5 py-3.5">Licencia</th>
                        <th class="px-5 py-3.5 text-center">Calificación Promedio</th>
                        <th class="px-5 py-3.5 text-center">Partidos Oficiales</th>
                        <th class="px-5 py-3.5">Conflictos Registrados</th>
                        <th class="px-5 py-3.5 text-center">Evaluaciones DT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    @forelse($referees as $referee)
                        <tr class="hover:bg-slate-850/50 transition">
                            <td class="px-5 py-4 font-bold text-white flex items-center gap-3">
                                <div class="size-8 rounded-full bg-slate-800 border border-slate-700 grid place-items-center text-xs text-emerald-400 font-black">
                                    {{ mb_substr($referee->name, 0, 1) }}
                                </div>
                                <div>
                                    <span>{{ $referee->name }}</span>
                                    @if($referee->is_active)
                                        <span class="ml-2 inline-block size-2 rounded-full bg-emerald-400" title="Activo"></span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-400 font-mono">{{ $referee->license_number ?? 'LIBRE' }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1 rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-black text-amber-400 border border-amber-500/20">
                                    {{ number_format($referee->rating_average, 2) }} ★
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center font-bold text-slate-200">
                                {{ $referee->matches_count }}
                            </td>
                            <td class="px-5 py-4">
                                @if($referee->conflictRecords->isEmpty())
                                    <span class="text-slate-500 italic">Ninguno</span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($referee->conflictRecords as $con)
                                            <span class="rounded bg-rose-500/10 px-2 py-0.5 text-[10px] font-semibold text-rose-400 border border-rose-500/20" title="{{ $con->reason }}">
                                                ✖ {{ $con->team?->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full bg-slate-800 px-2.5 py-1 text-[11px] font-bold text-slate-300">
                                    {{ $referee->evaluations->count() }} recibidas
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">No hay árbitros dados de alta.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
