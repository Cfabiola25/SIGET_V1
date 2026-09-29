@extends('v1.layouts.app')

@section('title', $match->homeTeam->name . ' vs ' . $match->awayTeam->name)

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Marcador Central / Match Header (Formal Light Card) -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 md:p-8 shadow-2xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('matches.index') }}" class="text-xs font-semibold uppercase tracking-wider text-[#057a55] hover:underline flex items-center gap-1">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Partidos
                </a>
                <span class="text-slate-300">•</span>
                <span class="text-xs text-slate-500 font-semibold">{{ $match->tournament->name }}</span>
            </div>
            <div>
                @if ($match->isLocked())
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                        <span class="size-1.5 rounded-full bg-emerald-600"></span>
                        Acta Oficial Cerrada
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold
                        {{ $match->status === 'played' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($match->status === 'suspended' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                        <span class="size-1.5 rounded-full {{ $match->status === 'played' ? 'bg-emerald-600' : ($match->status === 'suspended' ? 'bg-rose-600' : 'bg-slate-500') }}"></span>
                        {{ $match->status === 'played' ? 'Finalizado' : ($match->status === 'suspended' ? 'Suspendido' : 'Programado') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="my-6 grid grid-cols-3 items-center text-center">
            <!-- Equipo Local -->
            <div class="space-y-2">
                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-50 text-2xl font-black text-[#057a55] border border-emerald-100 shadow-2xs">
                    ⚽
                </div>
                <h2 class="text-lg font-bold text-slate-900 md:text-2xl">{{ $match->homeTeam->name }}</h2>
                <span class="inline-block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Local</span>
            </div>

            <!-- Marcador / Versus -->
            <div class="space-y-1">
                @if ($match->status === 'played')
                    <div class="text-4xl font-extrabold text-slate-900 md:text-6xl tracking-tight">
                        {{ $match->home_score }} <span class="text-slate-300">-</span> {{ $match->away_score }}
                    </div>
                @else
                    <div class="text-2xl font-black text-slate-300 md:text-4xl">VS</div>
                @endif
                <p class="text-xs font-medium text-slate-600 font-mono">{{ $match->match_date->format('d/m/Y - H:i') }} hrs</p>
                @if ($match->venue)
                    <p class="text-[11px] text-slate-500 flex items-center justify-center gap-1">
                        <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span>{{ $match->venue->name }}</span>
                        @if ($match->field_number) <span>(Cancha {{ $match->field_number }})</span> @endif
                        @if ($match->venue->navigation_url)
                            • <a href="{{ $match->venue->navigation_url }}" target="_blank" class="text-[#057a55] font-semibold hover:underline">GPS</a>
                        @endif
                    </p>
                @endif
            </div>

            <!-- Equipo Visitante -->
            <div class="space-y-2">
                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-50 text-2xl font-black text-blue-600 border border-blue-100 shadow-2xs">
                    ⚽
                </div>
                <h2 class="text-lg font-bold text-slate-900 md:text-2xl">{{ $match->awayTeam->name }}</h2>
                <span class="inline-block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Visitante</span>
            </div>
        </div>

        <!-- Barra de Acciones de Partido -->
        <div class="border-t border-slate-100 pt-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Acta Oficial Digital -->
                <a href="{{ route('matches.report', $match) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs">
                    <svg class="size-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Acta Oficial Digital</span>
                </a>

                @auth
                    @if (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $match->tournament->admin_id === auth()->id()))
                        @if (! $match->isLocked())
                            <a href="{{ route('matches.console', $match) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#057a55] px-4 py-2 text-xs font-semibold text-white hover:bg-[#046c4b] transition shadow-2xs">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Consola Arbitral</span>
                            </a>
                            <a href="{{ route('matches.closure', $match) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3.5 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100 transition">
                                <svg class="size-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Firmar y Cerrar</span>
                            </a>
                        @endif
                    @endif
                @endauth

                <a href="{{ route('referees.matches.scan.console', $match) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="size-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Escáner QR</span>
                </a>

                @auth
                    @if (! $match->isLocked())
                        @if (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $match->tournament->admin_id === auth()->id()) || $match->homeTeam->isManagedBy(auth()->user()))
                            <a href="{{ route('matches.lineup.edit', [$match, $match->homeTeam]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50/70 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 transition">
                                <span>📋 Nómina {{ $match->homeTeam->name }}</span>
                            </a>
                        @endif

                        @if (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $match->tournament->admin_id === auth()->id()) || $match->awayTeam->isManagedBy(auth()->user()))
                            <a href="{{ route('matches.lineup.edit', [$match, $match->awayTeam]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50/70 px-3 py-2 text-xs font-semibold text-blue-800 hover:bg-blue-100 transition">
                                <span>📋 Nómina {{ $match->awayTeam->name }}</span>
                            </a>
                        @endif

                        @if (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $match->tournament->admin_id === auth()->id()))
                            <a href="{{ route('matches.edit', $match) }}" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                Cargar Marcador
                            </a>
                        @endif
                    @endif
                @endauth
            </div>

            <div class="text-xs text-slate-500 font-medium">
                Reglamento: <span class="font-semibold text-slate-700">{{ $match->tournament->rules?->match_duration_minutes ?? 90 }} min</span> • Máx <span class="font-semibold text-slate-700">{{ $match->tournament->rules?->max_substitutions ?? 5 }}</span> cambios
            </div>
        </div>
    </div>

    <!-- MVP Oficial del Partido -->
    @if($match->mvpPlayer)
        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-6 shadow-2xs">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="grid size-14 place-items-center rounded-xl bg-amber-100 text-2xl font-black text-amber-700 border border-amber-300 shadow-2xs">
                        🏆
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-amber-200/80 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-amber-900 border border-amber-300">
                                OFICIAL MVP DEL PARTIDO
                            </span>
                            <span class="text-xs text-amber-800">Elegido por votación y rendimiento</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $match->mvpPlayer->name }}</h2>
                        <p class="text-xs text-amber-800 font-semibold">{{ $match->mvpPlayer->team?->name }} • Calificación Algorítmica: {{ number_format($match->mvpPlayer->profile?->performance_rating ?? 9.5, 1) }} ★</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- IA Match Reporter: Crónica Deportiva Oficial -->
    @if($match->chronicle_body)
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 md:p-8 shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                        <span class="size-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                        IA MATCH REPORTER
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Crónica Periodística Oficial</span>
                </div>
                <span class="text-xs font-mono text-slate-400">
                    {{ $match->chronicle_generated_at ? $match->chronicle_generated_at->diffForHumans() : 'Publicado' }}
                </span>
            </div>

            <div class="mt-5 space-y-4">
                <h3 class="text-xl font-bold text-slate-900 md:text-2xl tracking-tight leading-snug">
                    {{ $match->chronicle_title }}
                </h3>
                <div class="space-y-3 text-sm text-slate-600 leading-relaxed font-sans">
                    @foreach(explode("\n\n", $match->chronicle_body) as $paragraph)
                        @if(trim($paragraph))
                            <p>{{ trim($paragraph) }}</p>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Redactado automáticamente por el motor narrativo SIGET.</span>
                <span class="font-semibold text-slate-600">SIGET Sports Newsroom</span>
            </div>
        </div>
    @endif

    <!-- Nóminas y Alineaciones Confirmadas -->
    <div class="grid gap-6 md:grid-cols-2">
        <!-- Alineación Local -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ $match->homeTeam->name }}</h3>
                    <p class="text-xs text-slate-500">Nómina Oficial Confirmada</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200">
                    {{ $match->startersForTeam($match->home_team_id)->count() }} Titulares
                </span>
            </div>

            @php
                $homeStarters = $match->startersForTeam($match->home_team_id);
                $homeSubs = $match->substitutesForTeam($match->home_team_id);
            @endphp

            @if ($homeStarters->isEmpty() && $homeSubs->isEmpty())
                <p class="py-6 text-center text-xs text-slate-400">El Director Técnico aún no ha enviado la alineación oficial.</p>
            @else
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Titulares</span>
                    <div class="mt-2 divide-y divide-slate-100">
                        @foreach ($homeStarters as $starter)
                            <div class="flex items-center justify-between py-2 text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">#{{ $starter->jersey_number }}</span>
                                    <a href="{{ route('players.cromo', $starter->player) }}" class="font-semibold text-slate-800 hover:text-emerald-700 hover:underline">
                                        {{ $starter->player->name }}
                                    </a>
                                </div>
                                @if ($starter->verified_by_qr)
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">✅ Verificado QR</span>
                                @else
                                    <span class="text-[10px] text-slate-400 font-medium">Sin escanear</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                @if (! $homeSubs->isEmpty())
                    <div class="pt-3 border-t border-slate-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Suplentes</span>
                        <div class="mt-2 divide-y divide-slate-100">
                            @foreach ($homeSubs as $sub)
                                <div class="flex items-center justify-between py-1.5 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded">#{{ $sub->jersey_number }}</span>
                                        <a href="{{ route('players.cromo', $sub->player) }}" class="text-slate-700 hover:text-emerald-700 hover:underline">
                                            {{ $sub->player->name }}
                                        </a>
                                    </div>
                                    @if ($sub->verified_by_qr)
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">✅ Verificado QR</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Alineación Visitante -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ $match->awayTeam->name }}</h3>
                    <p class="text-xs text-slate-500">Nómina Oficial Confirmada</p>
                </div>
                <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 border border-blue-200">
                    {{ $match->startersForTeam($match->away_team_id)->count() }} Titulares
                </span>
            </div>

            @php
                $awayStarters = $match->startersForTeam($match->away_team_id);
                $awaySubs = $match->substitutesForTeam($match->away_team_id);
            @endphp

            @if ($awayStarters->isEmpty() && $awaySubs->isEmpty())
                <p class="py-6 text-center text-xs text-slate-400">El Director Técnico aún no ha enviado la alineación oficial.</p>
            @else
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Titulares</span>
                    <div class="mt-2 divide-y divide-slate-100">
                        @foreach ($awayStarters as $starter)
                            <div class="flex items-center justify-between py-2 text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">#{{ $starter->jersey_number }}</span>
                                    <a href="{{ route('players.cromo', $starter->player) }}" class="font-semibold text-slate-800 hover:text-blue-700 hover:underline">
                                        {{ $starter->player->name }}
                                    </a>
                                </div>
                                @if ($starter->verified_by_qr)
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">✅ Verificado QR</span>
                                @else
                                    <span class="text-[10px] text-slate-400 font-medium">Sin escanear</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                @if (! $awaySubs->isEmpty())
                    <div class="pt-3 border-t border-slate-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Suplentes</span>
                        <div class="mt-2 divide-y divide-slate-100">
                            @foreach ($awaySubs as $sub)
                                <div class="flex items-center justify-between py-1.5 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded">#{{ $sub->jersey_number }}</span>
                                        <a href="{{ route('players.cromo', $sub->player) }}" class="text-slate-700 hover:text-blue-700 hover:underline">
                                            {{ $sub->player->name }}
                                        </a>
                                    </div>
                                    @if ($sub->verified_by_qr)
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">✅ Verificado QR</span>
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

        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 md:p-8 shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-amber-50 text-amber-700 font-bold border border-amber-200">
                        ⚖
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Evaluación Arbitral Oficial</h3>
                        <p class="text-xs text-slate-500">Árbitro Designado: <strong class="text-slate-800">{{ $match->referee->name }}</strong> (Rating histórico: {{ number_format($match->referee->rating_average, 2) }} ★)</p>
                    </div>
                </div>
            </div>

            @if($existingEvaluation)
                <div class="mt-4 rounded-xl bg-slate-50 p-4 border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-700">✅ Tu club ya ha evaluado a este colegiado</span>
                        <p class="text-xs text-slate-600 mt-1">Calificación general: {{ $existingEvaluation->score_overall }} / 5 ★ • Reglas: {{ $existingEvaluation->score_rule_enforcement }}/5 • Imparcialidad: {{ $existingEvaluation->score_fairness }}/5</p>
                        @if($existingEvaluation->comments)
                            <p class="text-xs italic text-slate-500 mt-1">"{{ $existingEvaluation->comments }}"</p>
                        @endif
                    </div>
                    <span class="text-xs text-slate-400 font-mono">{{ $existingEvaluation->created_at->format('d/m/Y') }}</span>
                </div>
            @elseif($canEvaluate)
                <form method="POST" action="{{ route('matches.referee.evaluate', $match) }}" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $userTeamId }}">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Desempeño General</label>
                            <select name="score_overall" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-amber-600 focus:border-[#057a55] focus:outline-none">
                                <option value="5">5 ★★★★★ (Excelente)</option>
                                <option value="4">4 ★★★★ (Bueno)</option>
                                <option value="3">3 ★★★ (Regular)</option>
                                <option value="2">2 ★★ (Deficiente)</option>
                                <option value="1">1 ★ (Inaceptable)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Aplicación del Reglamento</label>
                            <select name="score_rule_enforcement" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                                <option value="5">5/5 - Rigor perfecto</option>
                                <option value="4">4/5 - Adecuado</option>
                                <option value="3">3/5 - Dudas en faltas</option>
                                <option value="2">2/5 - Permisivo</option>
                                <option value="1">1/5 - Descontrol total</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Imparcialidad y Criterio</label>
                            <select name="score_fairness" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                                <option value="5">5/5 - Totalmente neutral</option>
                                <option value="4">4/5 - Buen criterio</option>
                                <option value="3">3/5 - Criterio dispar</option>
                                <option value="2">2/5 - Sesgo evidente</option>
                                <option value="1">1/5 - Perjudicial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Puntualidad y Presentación</label>
                            <select name="score_punctuality" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                                <option value="5">5/5 - Impecable</option>
                                <option value="4">4/5 - A tiempo</option>
                                <option value="3">3/5 - Justo al inicio</option>
                                <option value="2">2/5 - Retraso leve</option>
                                <option value="1">1/5 - Retraso grave</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <textarea name="comments" rows="2" placeholder="Observaciones técnicas para el comité de arbitraje (opcional)..." class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none"></textarea>
                    </div>

                    <button type="submit" class="rounded-lg bg-[#057a55] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#046c4b] transition">
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
