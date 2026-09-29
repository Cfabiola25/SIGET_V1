@extends('v1.layouts.app')

@section('title', $match->homeTeam->name . ' vs ' . $match->awayTeam->name)

@section('content')
<div class="space-y-8">
    <!-- Marcador Central / Match Header -->
    <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900/90 to-emerald-950/40 p-6 md:p-8 backdrop-blur-xl shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
            <div>
                <a href="{{ route('matches.index') }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
                    ← Partidos
                </a>
                <span class="text-slate-500 mx-2">•</span>
                <span class="text-xs text-slate-300 font-semibold">{{ $match->tournament->name }}</span>
            </div>
            @if ($match->isLocked())
                <span class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 inline-flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full bg-emerald-400"></span>
                    Acta Oficial Cerrada
                </span>
            @else
                <span class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider
                    {{ $match->status === 'played' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($match->status === 'suspended' ? 'bg-rose-500/20 text-rose-400' : 'bg-slate-800 text-slate-300') }}">
                    {{ $match->status === 'played' ? 'Finalizado' : ($match->status === 'suspended' ? 'Suspendido' : 'Programado') }}
                </span>
            @endif
        </div>

        <div class="my-6 grid grid-cols-3 items-center text-center">
            <!-- Equipo Local -->
            <div class="space-y-2">
                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-500/10 text-2xl font-black text-emerald-400 border border-emerald-500/20">
                    ⚽
                </div>
                <h2 class="text-lg font-black text-white md:text-2xl">{{ $match->homeTeam->name }}</h2>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Local</span>
            </div>

            <!-- Marcador / Versus -->
            <div class="space-y-1">
                @if ($match->status === 'played')
                    <div class="text-4xl font-black text-white md:text-6xl tracking-tight">
                        {{ $match->home_score }} <span class="text-emerald-500">-</span> {{ $match->away_score }}
                    </div>
                @else
                    <div class="text-2xl font-black text-slate-500 md:text-4xl">VS</div>
                @endif
                <p class="text-xs font-mono text-emerald-400">{{ $match->match_date->format('d/m/Y - H:i') }}</p>
                @if ($match->venue)
                    <p class="text-[11px] text-slate-400">
                        📍 {{ $match->venue->name }}
                        @if ($match->field_number) ({{ $match->field_number }}) @endif
                        @if ($match->venue->navigation_url)
                            • <a href="{{ $match->venue->navigation_url }}" target="_blank" class="text-emerald-400 font-semibold hover:underline">GPS</a>
                        @endif
                    </p>
                @endif
            </div>

            <!-- Equipo Visitante -->
            <div class="space-y-2">
                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-500/10 text-2xl font-black text-blue-400 border border-blue-500/20">
                    ⚽
                </div>
                <h2 class="text-lg font-black text-white md:text-2xl">{{ $match->awayTeam->name }}</h2>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Visitante</span>
            </div>
        </div>

        <!-- Barra de Acciones de Partido -->
        <div class="border-t border-slate-800/80 pt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Acta Oficial Digital -->
                <a href="{{ route('matches.report', $match) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/50 bg-emerald-500/10 px-3.5 py-2 text-xs font-bold text-emerald-300 hover:bg-emerald-500/20 transition">
                    <span>📜</span> Acta Oficial Digital
                </a>


                @auth
                    @if (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $match->tournament->admin_id === auth()->id()))
                        @if (! $match->isLocked())
                            <a href="{{ route('matches.console', $match) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-black text-slate-950 hover:bg-emerald-400 transition shadow-md">
                                <span>⏱️</span> Consola Arbitral
                            </a>
                            <a href="{{ route('matches.closure', $match) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-amber-500/40 bg-amber-500/10 px-3.5 py-2 text-xs font-bold text-amber-300 hover:bg-amber-500/20 transition">
                                <span>✍️</span> Firmar y Cerrar
                            </a>
                        @endif
                    @endif
                @endauth

                <a href="{{ route('referees.matches.scan.console', $match) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 border border-slate-700 px-3.5 py-2 text-xs font-bold text-white hover:bg-slate-700 transition">
                    <span>📷</span> Escáner QR de Cancha
                </a>

                @auth
                    @if (! $match->isLocked())
                        @if (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $match->tournament->admin_id === auth()->id()) || $match->homeTeam->isManagedBy(auth()->user()))
                            <a href="{{ route('matches.lineup.edit', [$match, $match->homeTeam]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-3.5 py-2 text-xs font-bold text-emerald-400 hover:bg-emerald-500/20 transition">
                                📋 Alineación {{ $match->homeTeam->name }}
                            </a>
                        @endif

                        @if (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $match->tournament->admin_id === auth()->id()) || $match->awayTeam->isManagedBy(auth()->user()))
                            <a href="{{ route('matches.lineup.edit', [$match, $match->awayTeam]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-blue-500/40 bg-blue-500/10 px-3.5 py-2 text-xs font-bold text-blue-400 hover:bg-blue-500/20 transition">
                                📋 Alineación {{ $match->awayTeam->name }}
                            </a>
                        @endif

                        @if (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $match->tournament->admin_id === auth()->id()))
                            <a href="{{ route('matches.edit', $match) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2 text-xs font-bold text-slate-300 hover:bg-slate-700 transition">
                                Cargar Marcador
                            </a>
                        @endif
                    @endif
                @endauth
            </div>

            <div class="text-xs text-slate-400">
                Reglamento: {{ $match->tournament->rules?->match_duration_minutes ?? 90 }} min • Máx {{ $match->tournament->rules?->max_substitutions ?? 5 }} cambios
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-400">
            {{ session('status') }}
        </div>
    @endif

    <!-- MVP Oficial del Partido -->
    @if($match->mvpPlayer)
        <div class="relative overflow-hidden rounded-3xl border border-amber-500/40 bg-gradient-to-r from-amber-950/40 via-slate-900 to-slate-900/90 p-6 shadow-2xl backdrop-blur-xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="grid size-16 place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-600 text-3xl font-black text-slate-950 shadow-lg shadow-amber-500/20">
                        🏆
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-amber-500/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-amber-400 border border-amber-500/40">
                                OFICIAL MVP OF THE MATCH
                            </span>
                            <span class="text-xs text-slate-400">Elegido por votación popular & rendimiento</span>
                        </div>
                        <h2 class="text-xl font-black text-white sm:text-2xl mt-1">{{ $match->mvpPlayer->name }}</h2>
                        <p class="text-xs text-amber-300 font-semibold">{{ $match->mvpPlayer->team?->name }} • Calificación Algorítmica: {{ number_format($match->mvpPlayer->profile?->performance_rating ?? 9.5, 1) }} ★</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- IA Match Reporter: Crónica Deportiva Oficial -->
    @if($match->chronicle_body)
        <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-900/80 p-6 md:p-8 shadow-xl backdrop-blur-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">
                        <span class="size-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        IA MATCH REPORTER
                    </span>
                    <span class="text-xs text-slate-400">Crónica Periodística Oficial</span>
                </div>
                <span class="text-xs font-mono text-slate-500">
                    {{ $match->chronicle_generated_at ? $match->chronicle_generated_at->diffForHumans() : 'Publicado' }}
                </span>
            </div>

            <div class="mt-5 space-y-4">
                <h3 class="text-xl font-black text-white md:text-2xl tracking-tight leading-snug">
                    {{ $match->chronicle_title }}
                </h3>
                <div class="space-y-3 text-sm text-slate-300 leading-relaxed font-sans">
                    @foreach(explode("\n\n", $match->chronicle_body) as $paragraph)
                        @if(trim($paragraph))
                            <p>{{ trim($paragraph) }}</p>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-500">
                <span>Redactado y verificado automáticamente por el motor narrativo SIGET.</span>
                <span class="font-bold text-slate-400">SIGET Sports Newsroom</span>
            </div>
        </div>
    @endif

    <!-- Nóminas y Alineaciones Confirmadas -->
    <div class="grid gap-8 md:grid-cols-2">
        <!-- Alineación Local -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-white">{{ $match->homeTeam->name }}</h3>
                    <p class="text-xs text-slate-400">Nómina Oficial Confirmada</p>
                </div>
                <span class="rounded bg-emerald-500/10 px-2 py-0.5 text-xs font-bold text-emerald-400">
                    {{ $match->startersForTeam($match->home_team_id)->count() }} Titulares
                </span>
            </div>

            @php
                $homeStarters = $match->startersForTeam($match->home_team_id);
                $homeSubs = $match->substitutesForTeam($match->home_team_id);
            @endphp

            @if ($homeStarters->isEmpty() && $homeSubs->isEmpty())
                <p class="py-4 text-center text-xs text-slate-400">El Director Técnico aún no ha enviado la alineación oficial.</p>
            @else
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Titulares</span>
                    <div class="mt-2 divide-y divide-slate-800/80">
                        @foreach ($homeStarters as $starter)
                            <div class="flex items-center justify-between py-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-black text-emerald-400">#{{ $starter->jersey_number }}</span>
                                    <a href="{{ route('players.cromo', $starter->player) }}" class="font-bold text-white hover:underline">
                                        {{ $starter->player->name }}
                                    </a>
                                </div>
                                @if ($starter->verified_by_qr)
                                    <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400">✅ Verificado QR</span>
                                @else
                                    <span class="text-[10px] text-slate-500">Sin escanear</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                @if (! $homeSubs->isEmpty())
                    <div class="pt-3 border-t border-slate-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Suplentes</span>
                        <div class="mt-2 divide-y divide-slate-800/80">
                            @foreach ($homeSubs as $sub)
                                <div class="flex items-center justify-between py-1.5 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-slate-400">#{{ $sub->jersey_number }}</span>
                                        <a href="{{ route('players.cromo', $sub->player) }}" class="text-slate-300 hover:underline">
                                            {{ $sub->player->name }}
                                        </a>
                                    </div>
                                    @if ($sub->verified_by_qr)
                                        <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400">✅ Verificado QR</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Alineación Visitante -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-white">{{ $match->awayTeam->name }}</h3>
                    <p class="text-xs text-slate-400">Nómina Oficial Confirmada</p>
                </div>
                <span class="rounded bg-blue-500/10 px-2 py-0.5 text-xs font-bold text-blue-400">
                    {{ $match->startersForTeam($match->away_team_id)->count() }} Titulares
                </span>
            </div>

            @php
                $awayStarters = $match->startersForTeam($match->away_team_id);
                $awaySubs = $match->substitutesForTeam($match->away_team_id);
            @endphp

            @if ($awayStarters->isEmpty() && $awaySubs->isEmpty())
                <p class="py-4 text-center text-xs text-slate-400">El Director Técnico aún no ha enviado la alineación oficial.</p>
            @else
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Titulares</span>
                    <div class="mt-2 divide-y divide-slate-800/80">
                        @foreach ($awayStarters as $starter)
                            <div class="flex items-center justify-between py-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-black text-blue-400">#{{ $starter->jersey_number }}</span>
                                    <a href="{{ route('players.cromo', $starter->player) }}" class="font-bold text-white hover:underline">
                                        {{ $starter->player->name }}
                                    </a>
                                </div>
                                @if ($starter->verified_by_qr)
                                    <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400">✅ Verificado QR</span>
                                @else
                                    <span class="text-[10px] text-slate-500">Sin escanear</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                @if (! $awaySubs->isEmpty())
                    <div class="pt-3 border-t border-slate-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Suplentes</span>
                        <div class="mt-2 divide-y divide-slate-800/80">
                            @foreach ($awaySubs as $sub)
                                <div class="flex items-center justify-between py-1.5 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-slate-400">#{{ $sub->jersey_number }}</span>
                                        <a href="{{ route('players.cromo', $sub->player) }}" class="text-slate-300 hover:underline">
                                            {{ $sub->player->name }}
                                        </a>
                                    </div>
                                    @if ($sub->verified_by_qr)
                                        <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400">✅ Verificado QR</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Evaluación Arbitral Post-Partido (DTs) -->
    @if($match->referee && ($match->status === 'played' || $match->isLocked()))
        @php
            $user = auth()->user();
            $canEvaluate = $user && (
                $user->isSuperAdmin() ||
                $user->isAdmin() ||
                ($user->isCoach() && ($match->homeTeam?->coach_id === $user->id || $match->awayTeam?->coach_id === $user->id))
            );
            $userTeamId = null;
            if ($user?->isCoach()) {
                $userTeamId = $match->homeTeam?->coach_id === $user->id ? $match->home_team_id : $match->away_team_id;
            } elseif ($user?->isSuperAdmin() || $user?->isAdmin()) {
                $userTeamId = $match->home_team_id;
            }
            $existingEvaluation = $userTeamId ? $match->refereeEvaluations()->where('team_id', $userTeamId)->first() : null;
        @endphp

        <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6 md:p-8 backdrop-blur-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 place-items-center rounded-xl bg-amber-500/10 text-amber-400 font-bold border border-amber-500/20">
                        ⚖
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-white">Evaluación Arbitral Oficial</h3>
                        <p class="text-xs text-slate-400">Árbitro Designado: <strong class="text-slate-200">{{ $match->referee->name }}</strong> (Rating histórico: {{ number_format($match->referee->rating_average, 2) }} ★)</p>
                    </div>
                </div>
            </div>

            @if($existingEvaluation)
                <div class="mt-4 rounded-2xl bg-slate-950 p-4 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-400">✅ Tu club ya ha evaluado a este colegiado</span>
                        <p class="text-xs text-slate-400 mt-1">Calificación general: {{ $existingEvaluation->score_overall }} / 5 ★ • Reglas: {{ $existingEvaluation->score_rule_enforcement }}/5 • Imparcialidad: {{ $existingEvaluation->score_fairness }}/5</p>
                        @if($existingEvaluation->comments)
                            <p class="text-xs italic text-slate-500 mt-1">"{{ $existingEvaluation->comments }}"</p>
                        @endif
                    </div>
                    <span class="text-xs text-slate-500">{{ $existingEvaluation->created_at->format('d/m/Y') }}</span>
                </div>
            @elseif($canEvaluate)
                <form method="POST" action="{{ route('matches.referee.evaluate', $match) }}" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $userTeamId }}">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Desempeño General</label>
                            <select name="score_overall" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs font-bold text-amber-400 focus:border-emerald-500 focus:outline-none">
                                <option value="5">5 ★★★★★ (Excelente)</option>
                                <option value="4">4 ★★★★ (Bueno)</option>
                                <option value="3">3 ★★★ (Regular)</option>
                                <option value="2">2 ★★ (Deficiente)</option>
                                <option value="1">1 ★ (Inaceptable)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Aplicación del Reglamento</label>
                            <select name="score_rule_enforcement" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none">
                                <option value="5">5/5 - Rigor perfecto</option>
                                <option value="4">4/5 - Adecuado</option>
                                <option value="3">3/5 - Dudas en faltas</option>
                                <option value="2">2/5 - Permisivo</option>
                                <option value="1">1/5 - Descontrol total</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Imparcialidad y Criterio</label>
                            <select name="score_fairness" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none">
                                <option value="5">5/5 - Totalmente neutral</option>
                                <option value="4">4/5 - Buen criterio</option>
                                <option value="3">3/5 - Criterio dispar</option>
                                <option value="2">2/5 - Sesgo evidente</option>
                                <option value="1">1/5 - Perjudicial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Puntualidad y Presentación</label>
                            <select name="score_punctuality" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none">
                                <option value="5">5/5 - Impecable</option>
                                <option value="4">4/5 - A tiempo</option>
                                <option value="3">3/5 - Justo al inicio</option>
                                <option value="2">2/5 - Retraso leve</option>
                                <option value="1">1/5 - Retraso grave</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <textarea name="comments" rows="2" placeholder="Observaciones técnicas para el comité de arbitraje (opcional)..." class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <button type="submit" class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 px-5 py-2 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/10 hover:from-emerald-400 transition">
                        Enviar Calificación Arbitral Oficial
                    </button>
                </form>
            @else
                <p class="mt-4 text-xs text-slate-500">Inicia sesión como Director Técnico de uno de los clubes participantes para calificar el arbitraje de este encuentro.</p>
            @endif
        </div>
    @endif
</div>
@endsection
