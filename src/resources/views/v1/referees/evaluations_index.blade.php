@extends('v1.layouts.app')

@section('title', 'Profesionalización Arbitral & Asignación Algorítmica')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-200">
                    <span class="size-1.5 rounded-full bg-emerald-600"></span>
                    INTELIGENCIA ARBITRAL
                </span>
                <span class="text-xs text-slate-400 font-medium">Evaluaciones DT • Anti-Conflictos</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Panel de Arbitraje Profesional & Designaciones</h1>
            <p class="text-xs text-slate-500">Control de calificaciones post-partido, prevención de conflictos de interés y motor de asignación algorítmica.</p>
        </div>
        <a href="{{ route('referees.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
            <span>← Padrón de Árbitros</span>
        </a>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Actions & Automation Section -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Motor de Asignación Masiva por Jornada -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">
                    ⚙
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Asignación Algorítmica por Jornada</h3>
                    <p class="text-xs text-slate-500">Designa árbitros evitando colisiones de agenda y conflictos con los clubes.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('tournaments.referees.bulk_assign') }}" class="space-y-4 pt-2">
                @csrf
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Torneo</label>
                        <select name="tournament_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                            @foreach($tournaments as $tourn)
                                <option value="{{ $tourn->id }}">{{ $tourn->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Número de Jornada</label>
                        <input type="number" name="round_number" value="1" min="1" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full rounded-lg bg-[#057a55] py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-[#046c4b] transition">
                    Ejecutar Motor Algorítmico de Asignación
                </button>
            </form>
        </div>

        <!-- Registro de Conflicto de Interés -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl bg-amber-50 text-amber-700 border border-amber-200 font-bold">
                    ⚠
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Declaración de Conflicto de Interés</h3>
                    <p class="text-xs text-slate-500">Bloquea formalmente la designación de un árbitro con un club determinado.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('referees.conflicts.store') }}" class="space-y-4 pt-2">
                @csrf
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Árbitro</label>
                        <select name="referee_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                            @foreach($referees as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ number_format($r->rating_average, 2) }} ★)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Club con Conflicto</label>
                        <select name="team_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                            @foreach($teams as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <input type="text" name="reason" placeholder="Motivo (ej: Vínculo familiar, incidente previo, club de origen)..." required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                </div>
                <button type="submit" class="w-full rounded-lg border border-amber-300 bg-amber-50 py-2.5 text-xs font-semibold text-amber-900 hover:bg-amber-100 transition">
                    Registrar Bloqueo de Conflicto
                </button>
            </form>
        </div>
    </div>

    <!-- Tabla de Árbitros & Calificaciones -->
    <div class="rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-2xs">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Escalafón y Ratings de Colegiados</h3>
            <span class="text-xs text-slate-500 font-medium">Total: {{ $referees->count() }} árbitros registrados</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-600 font-semibold text-[11px] uppercase tracking-wider border-b border-slate-200/80">
                        <th class="px-5 py-3.5">Árbitro</th>
                        <th class="px-5 py-3.5">Licencia</th>
                        <th class="px-5 py-3.5 text-center">Calificación Promedio</th>
                        <th class="px-5 py-3.5 text-center">Partidos Oficiales</th>
                        <th class="px-5 py-3.5">Conflictos Registrados</th>
                        <th class="px-5 py-3.5 text-center">Evaluaciones DT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($referees as $referee)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900 flex items-center gap-3">
                                <div class="size-8 rounded-full bg-slate-100 border border-slate-200 grid place-items-center text-xs text-emerald-700 font-bold">
                                    {{ mb_substr($referee->name, 0, 1) }}
                                </div>
                                <div>
                                    <span>{{ $referee->name }}</span>
                                    @if($referee->is_active)
                                        <span class="ml-1.5 inline-block size-1.5 rounded-full bg-emerald-600" title="Activo"></span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 font-mono">{{ $referee->license_number ?? 'LIBRE' }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 border border-amber-200">
                                    {{ number_format($referee->rating_average, 2) }} ★
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center font-bold text-slate-800">
                                {{ $referee->matches_count }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($referee->conflictRecords->isEmpty())
                                    <span class="text-slate-400 italic">Ninguno</span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($referee->conflictRecords as $con)
                                            <span class="rounded bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700 border border-rose-200" title="{{ $con->reason }}">
                                                ✖ {{ $con->team?->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-700">
                                    {{ $referee->evaluations->count() }} recibidas
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">No hay árbitros dados de alta.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
