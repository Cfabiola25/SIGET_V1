@extends('v1.layouts.app')

@section('title', 'Reglamento de Competición - ' . $tournament->name)

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-400">
                <a href="{{ route('tournaments.show', $tournament) }}" class="hover:underline">← Volver al Torneo</a>
                <span>•</span>
                <span>Motor de Reglas de Fútbol</span>
            </div>
            <h1 class="text-2xl font-black text-white md:text-3xl">Reglamento Oficial: {{ $tournament->name }}</h1>
            <p class="text-sm text-slate-400">Configura los parámetros disciplinarios, matemáticos y de partido bajo estándares IFAB.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-400">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-400">
            Por favor corrige los errores antes de continuar.
        </div>
    @endif

    <form method="POST" action="{{ route('tournaments.rules.update', $tournament) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Tarjetas y Disciplina -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="mb-4 flex items-center gap-3 border-b border-slate-800 pb-3">
                <span class="grid size-9 place-items-center rounded-lg bg-amber-500/10 text-lg text-amber-400 font-bold">⚠️</span>
                <div>
                    <h2 class="text-lg font-bold text-white">Régimen Disciplinario</h2>
                    <p class="text-xs text-slate-400">Control automático de inhabilitación de jugadores en plantillas.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="yellow_card_limit_for_suspension" class="block text-sm font-medium text-slate-300">
                        Límite de Tarjetas Amarillas para Suspensión
                    </label>
                    <input type="number" id="yellow_card_limit_for_suspension" name="yellow_card_limit_for_suspension"
                           value="{{ old('yellow_card_limit_for_suspension', $rules->yellow_card_limit_for_suspension) }}"
                           min="1" max="10" required
                           class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    <span class="mt-1 block text-xs text-slate-500">Ej: 2 amarillas acumuladas inhabilitan al jugador para el siguiente encuentro.</span>
                    @error('yellow_card_limit_for_suspension') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="direct_red_suspension_matches" class="block text-sm font-medium text-slate-300">
                        Partidos de Suspensión por Tarjeta Roja Directa
                    </label>
                    <input type="number" id="direct_red_suspension_matches" name="direct_red_suspension_matches"
                           value="{{ old('direct_red_suspension_matches', $rules->direct_red_suspension_matches) }}"
                           min="1" max="10" required
                           class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    <span class="mt-1 block text-xs text-slate-500">Número de jornadas que el sistema bloqueará automáticamente al expulsado.</span>
                    @error('direct_red_suspension_matches') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-5 border-t border-slate-800/80 pt-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="reset_cards_on_knockout" value="0">
                    <input type="checkbox" name="reset_cards_on_knockout" value="1"
                           {{ old('reset_cards_on_knockout', $rules->reset_cards_on_knockout) ? 'checked' : '' }}
                           class="size-4.5 rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500/20">
                    <div>
                        <span class="text-sm font-semibold text-slate-200">Reinicio de tarjetas al pasar a Fase Eliminatoria (Brackets)</span>
                        <p class="text-xs text-slate-400">Limpia las amarillas acumuladas de la fase de grupos para que ningún jugador se pierda la semifinal por acumulación.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Puntuación y Tabla de Posiciones -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="mb-4 flex items-center gap-3 border-b border-slate-800 pb-3">
                <span class="grid size-9 place-items-center rounded-lg bg-emerald-500/10 text-lg text-emerald-400 font-bold">📊</span>
                <div>
                    <h2 class="text-lg font-bold text-white">Sistema de Puntuación y Desempate</h2>
                    <p class="text-xs text-slate-400">Algoritmo matemático para ordenar la tabla de posiciones.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label for="points_for_win" class="block text-sm font-medium text-slate-300">Puntos por Victoria</label>
                    <input type="number" id="points_for_win" name="points_for_win"
                           value="{{ old('points_for_win', $rules->points_for_win) }}"
                           min="1" max="10" required
                           class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    @error('points_for_win') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="points_for_draw" class="block text-sm font-medium text-slate-300">Puntos por Empate</label>
                    <input type="number" id="points_for_draw" name="points_for_draw"
                           value="{{ old('points_for_draw', $rules->points_for_draw) }}"
                           min="0" max="5" required
                           class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    @error('points_for_draw') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="points_for_loss" class="block text-sm font-medium text-slate-300">Puntos por Derrota</label>
                    <input type="number" id="points_for_loss" name="points_for_loss"
                           value="{{ old('points_for_loss', $rules->points_for_loss) }}"
                           min="0" max="5" required
                           class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    @error('points_for_loss') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-5 border-t border-slate-800/80 pt-4">
                <label for="tiebreaker_rule" class="block text-sm font-medium text-slate-300">Criterio Principal de Desempate (Jerarquía FIFA)</label>
                <select id="tiebreaker_rule" name="tiebreaker_rule" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm">
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
                @error('tiebreaker_rule') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Parámetros del Partido y Alineaciones -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="mb-4 flex items-center gap-3 border-b border-slate-800 pb-3">
                <span class="grid size-9 place-items-center rounded-lg bg-blue-500/10 text-lg text-blue-400 font-bold">⏱️</span>
                <div>
                    <h2 class="text-lg font-bold text-white">Parámetros de Partido & Alineaciones</h2>
                    <p class="text-xs text-slate-400">Límites reglamentarios y control de tiempos en Match Day.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label for="match_duration_minutes" class="block text-sm font-medium text-slate-300">Duración del Partido (Minutos)</label>
                    <input type="number" id="match_duration_minutes" name="match_duration_minutes"
                           value="{{ old('match_duration_minutes', $rules->match_duration_minutes) }}"
                           min="20" max="120" required
                           class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    <span class="mt-1 block text-xs text-slate-500">Ej: 90 (2x45) en Mayores, 70 u 80 en Juvenil.</span>
                    @error('match_duration_minutes') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="max_substitutions" class="block text-sm font-medium text-slate-300">Máximo de Sustituciones</label>
                    <input type="number" id="max_substitutions" name="max_substitutions"
                           value="{{ old('max_substitutions', $rules->max_substitutions) }}"
                           min="1" max="11" required
                           class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    <span class="mt-1 block text-xs text-slate-500">Regla moderna IFAB: 5 cambios por equipo.</span>
                    @error('max_substitutions') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="lineup_lock_minutes_before_match" class="block text-sm font-medium text-slate-300">Cierre de Alineación (Minutos antes)</label>
                    <input type="number" id="lineup_lock_minutes_before_match" name="lineup_lock_minutes_before_match"
                           value="{{ old('lineup_lock_minutes_before_match', $rules->lineup_lock_minutes_before_match) }}"
                           min="0" max="120" required
                           class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    <span class="mt-1 block text-xs text-slate-500">El DT no podrá enviar alineación si falta menos tiempo.</span>
                    @error('lineup_lock_minutes_before_match') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('tournaments.show', $tournament) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-700 transition">
                Cancelar
            </a>
            <button type="submit" class="rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 hover:bg-emerald-400 transition">
                Guardar Reglamento
            </button>
        </div>
    </form>
</div>
@endsection
