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
</div>
@endsection
