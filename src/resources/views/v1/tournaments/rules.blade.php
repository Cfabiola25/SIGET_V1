@extends('v1.layouts.app')

@section('title', 'Reglamento de Competición - ' . $tournament->name)
@section('header_title', 'Competition Rules & Settings')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-800">
                <a href="{{ route('tournaments.show', $tournament) }}" class="hover:underline">&larr; Volver al Torneo</a>
                <span>•</span>
                <span>Motor de Reglas IFAB</span>
            </div>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight mt-1">Reglamento Oficial: {{ $tournament->name }}</h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Configura los parámetros disciplinarios, matemáticos y de partido bajo estándares oficiales.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('tournaments.rules.update', $tournament) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Tarjetas y Disciplina -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="size-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-sm">⚠️</div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Régimen Disciplinario</h3>
                    <p class="text-xs text-slate-400">Control automático de inhabilitación de jugadores en plantillas.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="yellow_card_limit_for_suspension" class="block text-xs font-semibold text-slate-700 mb-1">
                        Límite de Tarjetas Amarillas para Suspensión
                    </label>
                    <input type="number" id="yellow_card_limit_for_suspension" name="yellow_card_limit_for_suspension"
                           value="{{ old('yellow_card_limit_for_suspension', $rules->yellow_card_limit_for_suspension) }}"
                           min="1" max="10" required
                           class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600" />
                    <span class="mt-1 block text-[11px] text-slate-400">Ej: 2 amarillas acumuladas inhabilitan al jugador para el siguiente encuentro.</span>
                </div>

                <div>
                    <label for="direct_red_suspension_matches" class="block text-xs font-semibold text-slate-700 mb-1">
                        Partidos de Suspensión por Tarjeta Roja Directa
                    </label>
                    <input type="number" id="direct_red_suspension_matches" name="direct_red_suspension_matches"
                           value="{{ old('direct_red_suspension_matches', $rules->direct_red_suspension_matches) }}"
                           min="1" max="10" required
                           class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600" />
                    <span class="mt-1 block text-[11px] text-slate-400">Sanción base automática (sujeta a revisión por Tribunal).</span>
                </div>
            </div>
        </div>

        <!-- Sistema de Puntos y Tabla General -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="size-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">📊</div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Sistema de Puntuación & Criterio de Desempate</h3>
                    <p class="text-xs text-slate-400">Puntajes aplicados automáticamente tras el cierre oficial de cada partido.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label for="points_for_win" class="block text-xs font-semibold text-slate-700 mb-1">Puntos por Victoria</label>
                    <input type="number" id="points_for_win" name="points_for_win"
                           value="{{ old('points_for_win', $rules->points_for_win) }}"
                           min="1" max="10" required
                           class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg" />
                </div>

                <div>
                    <label for="points_for_draw" class="block text-xs font-semibold text-slate-700 mb-1">Puntos por Empate</label>
                    <input type="number" id="points_for_draw" name="points_for_draw"
                           value="{{ old('points_for_draw', $rules->points_for_draw) }}"
                           min="0" max="5" required
                           class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg" />
                </div>

                <div>
                    <label for="points_for_loss" class="block text-xs font-semibold text-slate-700 mb-1">Puntos por Derrota</label>
                    <input type="number" id="points_for_loss" name="points_for_loss"
                           value="{{ old('points_for_loss', $rules->points_for_loss) }}"
                           min="0" max="5" required
                           class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg" />
                </div>
            </div>

            <div class="mt-5 border-t border-slate-100 pt-4">
                <label for="tiebreaker_rule" class="block text-xs font-semibold text-slate-700 mb-1">Criterio Principal de Desempate (Jerarquía FIFA)</label>
                <select id="tiebreaker_rule" name="tiebreaker_rule" required class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg">
                    <option value="goal_difference" {{ old('tiebreaker_rule', $rules->tiebreaker_rule) === 'goal_difference' ? 'selected' : '' }}>
                        1. Diferencia de Goles General (GF - GC)
                    </option>
                    <option value="head_to_head" {{ old('tiebreaker_rule', $rules->tiebreaker_rule) === 'head_to_head' ? 'selected' : '' }}>
                        1. Enfrentamiento Directo entre los equipos empatados
                    </option>
                    <option value="goals_for" {{ old('tiebreaker_rule', $rules->tiebreaker_rule) === 'goals_for' ? 'selected' : '' }}>
                        1. Mayor Cantidad de Goles a Favor
                    </option>
                    <option value="fair_play" {{ old('tiebreaker_rule', $rules->tiebreaker_rule) === 'fair_play' ? 'selected' : '' }}>
                        1. Puntuación de Juego Limpio (Fair Play - menos tarjetas)
                    </option>
                </select>
            </div>
        </div>

        <!-- Parámetros del Partido y Alineaciones -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="size-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-sm">⏱️</div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Parámetros de Partido & Alineaciones</h3>
                    <p class="text-xs text-slate-400">Límites reglamentarios y control de tiempos en Match Day.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label for="match_duration_minutes" class="block text-xs font-semibold text-slate-700 mb-1">Duración (Minutos)</label>
                    <input type="number" id="match_duration_minutes" name="match_duration_minutes"
                           value="{{ old('match_duration_minutes', $rules->match_duration_minutes) }}"
                           min="20" max="120" required
                           class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg" />
                    <span class="mt-1 block text-[11px] text-slate-400">Ej: 90 en Mayores, 70 u 80 en Juvenil.</span>
                </div>

                <div>
                    <label for="max_substitutions" class="block text-xs font-semibold text-slate-700 mb-1">Máximo de Sustituciones</label>
                    <input type="number" id="max_substitutions" name="max_substitutions"
                           value="{{ old('max_substitutions', $rules->max_substitutions) }}"
                           min="1" max="11" required
                           class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg" />
                    <span class="mt-1 block text-[11px] text-slate-400">Regla moderna IFAB: 5 cambios por equipo.</span>
                </div>

                <div>
                    <label for="lineup_lock_minutes_before_match" class="block text-xs font-semibold text-slate-700 mb-1">Cierre Alineación (Minutos antes)</label>
                    <input type="number" id="lineup_lock_minutes_before_match" name="lineup_lock_minutes_before_match"
                           value="{{ old('lineup_lock_minutes_before_match', $rules->lineup_lock_minutes_before_match) }}"
                           min="0" max="120" required
                           class="w-full text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg" />
                    <span class="mt-1 block text-[11px] text-slate-400">Bloqueo de envíos de alineación de DT.</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('tournaments.show', $tournament) }}" class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs md:text-sm font-semibold hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs md:text-sm font-bold shadow-xs transition cursor-pointer">
                Guardar Reglamento
            </button>
        </div>
    </form>
</div>
@endsection
