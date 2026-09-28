@extends('v1.layouts.app')

@section('title', 'Generador Automático de Fixtures: ' . $tournament->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('tournaments.show', $tournament) }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-emerald-400 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al Torneo
            </a>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">Generador Automático de Calendario (Berger)</h1>
            <p class="text-sm text-slate-400">Algoritmo matemático Round-Robin con resolución de restricciones de descanso y disponibilidad de sedes.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                <span>⚽</span> {{ $tournament->teams->count() }} Equipos Registrados
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-semibold">
            <p class="font-bold mb-1">Por favor verifica los datos:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($tournament->teams->count() < 2)
        <x-card class="text-center py-8">
            <div class="text-4xl mb-3">⚠️</div>
            <h3 class="text-base font-bold text-white">Insuficientes Equipos</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto mt-1">Se requieren al menos 2 equipos confirmados para generar un calendario de competición.</p>
            <a href="{{ route('tournaments.show', $tournament) }}" class="mt-4 inline-block px-4 py-2 rounded-xl bg-emerald-500 text-slate-950 font-bold text-xs hover:bg-emerald-400 transition">
                Ir a Gestión de Equipos
            </a>
        </x-card>
    @else
        <!-- Algorithm Explanation Card -->
        <x-card class="bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 border-slate-800 space-y-3">
            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xl">
                    📐
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Motor Matemático Berger (Sistema de Rotación de Polígono)</h3>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        El generador calcula de forma determinista todas las jornadas garantizando que cada equipo enfrente a todos los demás, alternando equitativamente localías y respetando descansos mínimos entre encuentros.
                        @if ($tournament->teams->count() % 2 !== 0)
                            <span class="block mt-1 text-amber-300 font-semibold">
                                ℹ️ Cantidad impar de equipos ({{ $tournament->teams->count() }}): se aplicará descanso automático (BYE) rotativo por jornada.
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </x-card>

        <!-- Configuration Form -->
        <form action="{{ route('tournaments.fixtures.generate.submit', $tournament) }}" method="POST" class="space-y-6">
            @csrf

            <x-card class="space-y-6">
                <h3 class="text-base font-bold text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                    <span>⚙️</span> Parámetros del Calendario
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-slate-300 mb-1">Fecha de Inicio de la Jornada 1</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->next(\Carbon\Carbon::SATURDAY)->format('Y-m-d')) }}" required class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                        <span class="text-[11px] text-slate-500 mt-1 block">Primera fecha en la que se disputarán los partidos</span>
                    </div>

                    <!-- Days Between Rounds -->
                    <div>
                        <label for="days_between_rounds" class="block text-xs font-semibold text-slate-300 mb-1">Días de Descanso entre Jornadas</label>
                        <input type="number" name="days_between_rounds" id="days_between_rounds" min="1" max="30" value="{{ old('days_between_rounds', 7) }}" required class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                        <span class="text-[11px] text-slate-500 mt-1 block">7 días para ligas de fin de semana, 3-4 para torneos relámpago</span>
                    </div>

                    <!-- Rounds Count (1 vuelta o 2 vueltas) -->
                    <div>
                        <label for="rounds_count" class="block text-xs font-semibold text-slate-300 mb-1">Formato de Vueltas</label>
                        <select name="rounds_count" id="rounds_count" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                            <option value="1" {{ old('rounds_count') == '1' ? 'selected' : '' }}>1 Vuelta (Solo Ida - Todos contra Todos)</option>
                            <option value="2" {{ old('rounds_count') == '2' ? 'selected' : '' }}>2 Vueltas (Ida y Vuelta - Recíproco con localía invertida)</option>
                        </select>
                    </div>

                    <!-- Time Slots -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Franjas Horarias (Horas de Inicio)</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-950 border border-slate-800 text-slate-300">
                                <input type="checkbox" name="time_slots[]" value="09:00" checked class="rounded border-slate-700 text-emerald-500 focus:ring-0"> 09:00 AM
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-950 border border-slate-800 text-slate-300">
                                <input type="checkbox" name="time_slots[]" value="11:00" checked class="rounded border-slate-700 text-emerald-500 focus:ring-0"> 11:00 AM
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-950 border border-slate-800 text-slate-300">
                                <input type="checkbox" name="time_slots[]" value="14:00" checked class="rounded border-slate-700 text-emerald-500 focus:ring-0"> 02:00 PM
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-950 border border-slate-800 text-slate-300">
                                <input type="checkbox" name="time_slots[]" value="16:00" checked class="rounded border-slate-700 text-emerald-500 focus:ring-0"> 04:00 PM
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Venues Selection -->
                <div class="pt-4 border-t border-slate-800">
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Sedes Disponibles para Asignación de Partidos</label>
                    @if ($venues->isEmpty())
                        <p class="text-xs text-slate-400 italic">No hay sedes registradas en el sistema. Puedes registrarlas en la sección de Sedes.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($venues as $venue)
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white">
                                    <input type="checkbox" name="venue_ids[]" value="{{ $venue->id }}" checked class="rounded border-slate-700 text-emerald-500 focus:ring-0">
                                    <div>
                                        <strong>{{ $venue->name }}</strong>
                                        <span class="block text-[10px] text-slate-400">{{ $venue->city }} &bull; {{ $venue->field_count }} canchas</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Teams in fixture preview -->
            <x-card class="space-y-3">
                <h4 class="text-sm font-bold text-white flex items-center justify-between">
                    <span>Equipos a Programar ({{ $tournament->teams->count() }})</span>
                    <span class="text-xs text-slate-400 font-normal">Se emparejarán automáticamente</span>
                </h4>
                <div class="flex flex-wrap gap-2">
                    @foreach ($tournament->teams as $team)
                        <span class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-200">
                            {{ $team->name }}
                        </span>
                    @endforeach
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('tournaments.show', $tournament) }}" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 font-bold text-xs hover:bg-slate-700 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs transition shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                    <span>🗓️</span> Generar Fixture Oficial
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
