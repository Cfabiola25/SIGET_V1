@extends('v1.layouts.app')

@section('title', 'Generador de Fixtures: ' . $tournament->name)
@section('header_title', 'Fixture Generator')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-800">
                <a href="{{ route('tournaments.show', $tournament) }}" class="hover:underline">&larr; Volver al Torneo</a>
            </div>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight mt-1">Generador Automático de Calendario (Berger)</h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Algoritmo matemático Round-Robin con resolución de descansos y disponibilidad de sedes.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 border border-emerald-200 text-emerald-800">
                <span>⚽</span> {{ $tournament->teams->count() }} Equipos
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800">
            <p class="font-bold mb-1">Por favor verifica los datos:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($tournament->teams->count() < 2)
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-8 text-center">
            <div class="text-4xl mb-3">⚠️</div>
            <h3 class="text-base font-bold text-slate-900">Insuficientes Equipos</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Se requieren al menos 2 equipos confirmados para generar un calendario de competición.</p>
            <a href="{{ route('tournaments.show', $tournament) }}" class="mt-4 inline-block px-4 py-2 rounded-lg bg-[#057a55] text-white font-bold text-xs hover:bg-[#046c4b] transition">
                Ir a Gestión de Equipos
            </a>
        </div>
    @else
        <!-- Algorithm Explanation Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 space-y-3">
            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-800 text-xl font-bold">
                    📐
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Motor Matemático Berger (Sistema de Rotación de Polígono)</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        El generador calcula de forma determinista todas las jornadas garantizando que cada equipo enfrente a todos los demás, alternando equitativamente localías y respetando descansos mínimos entre encuentros.
                        @if ($tournament->teams->count() % 2 !== 0)
                            <span class="block mt-1 text-amber-700 font-semibold">
                                ℹ️ Cantidad impar de equipos ({{ $tournament->teams->count() }}): se aplicará descanso automático (BYE) rotativo por jornada.
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Configuration Form -->
        <form action="{{ route('tournaments.fixtures.generate.submit', $tournament) }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 space-y-5">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <span>⚙️</span> Parámetros del Calendario
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1">Fecha de Inicio de la Jornada 1</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->next(\Carbon\Carbon::SATURDAY)->format('Y-m-d')) }}" required class="w-full rounded-lg bg-white border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                        <span class="text-[11px] text-slate-400 mt-1 block">Primera fecha en la que se disputarán los partidos</span>
                    </div>

                    <!-- Days Between Rounds -->
                    <div>
                        <label for="days_between_rounds" class="block text-xs font-semibold text-slate-700 mb-1">Días de Descanso entre Jornadas</label>
                        <input type="number" name="days_between_rounds" id="days_between_rounds" min="1" max="30" value="{{ old('days_between_rounds', 7) }}" required class="w-full rounded-lg bg-white border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                        <span class="text-[11px] text-slate-400 mt-1 block">7 días para ligas de fin de semana</span>
                    </div>

                    <!-- Rounds Count -->
                    <div>
                        <label for="rounds_count" class="block text-xs font-semibold text-slate-700 mb-1">Formato de Vueltas</label>
                        <select name="rounds_count" id="rounds_count" class="w-full rounded-lg bg-white border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                            <option value="1" {{ old('rounds_count') == '1' ? 'selected' : '' }}>1 Vuelta (Solo Ida - Todos contra Todos)</option>
                            <option value="2" {{ old('rounds_count') == '2' ? 'selected' : '' }}>2 Vueltas (Ida y Vuelta - Recíproco con localía invertida)</option>
                        </select>
                    </div>

                    <!-- Time Slots -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Franjas Horarias Disponibles</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-700">
                                <input type="checkbox" name="time_slots[]" value="09:00" checked class="rounded border-slate-300 accent-[#057a55]"> 09:00 AM
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-700">
                                <input type="checkbox" name="time_slots[]" value="11:00" checked class="rounded border-slate-300 accent-[#057a55]"> 11:00 AM
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-700">
                                <input type="checkbox" name="time_slots[]" value="14:00" checked class="rounded border-slate-300 accent-[#057a55]"> 02:00 PM
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-700">
                                <input type="checkbox" name="time_slots[]" value="16:00" checked class="rounded border-slate-300 accent-[#057a55]"> 04:00 PM
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Venues Selection -->
                <div class="pt-4 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-700 mb-2">Sedes Disponibles para Asignación de Partidos</label>
                    @if ($venues->isEmpty())
                        <p class="text-xs text-slate-400 italic">No hay sedes registradas en el sistema.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($venues as $venue)
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800">
                                    <input type="checkbox" name="venue_ids[]" value="{{ $venue->id }}" checked class="rounded border-slate-300 accent-[#057a55]">
                                    <div>
                                        <strong>{{ $venue->name }}</strong>
                                        <span class="block text-[10px] text-slate-500">{{ $venue->city }} &bull; {{ $venue->field_count }} canchas</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Teams in fixture preview -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 space-y-3">
                <h4 class="text-sm font-bold text-slate-900 flex items-center justify-between">
                    <span>Equipos a Programar ({{ $tournament->teams->count() }})</span>
                    <span class="text-xs text-slate-500 font-normal">Se emparejarán automáticamente</span>
                </h4>
                <div class="flex flex-wrap gap-2">
                    @foreach ($tournament->teams as $team)
                        <span class="px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                            {{ $team->name }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('tournaments.show', $tournament) }}" class="px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white font-bold text-xs transition shadow-xs flex items-center gap-2 cursor-pointer">
                    <span>🗓️</span> Generar Fixture Oficial
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
