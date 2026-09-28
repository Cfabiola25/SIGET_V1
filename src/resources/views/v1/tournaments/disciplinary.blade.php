@extends('v1.layouts.app')

@section('title', 'Tribunal de Penas y Disciplina')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('tournaments.show', $tournament) }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-emerald-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver al Torneo
                </a>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">Tribunal de Penas y Disciplina</h1>
                <p class="text-sm text-slate-400">Automatización reglamentaria IFAB de suspensiones e inhabilitaciones para <strong class="text-white">{{ $tournament->name }}</strong>.</p>
            </div>

            <!-- Tournament Rules Pill -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs font-bold">
                    <span>🟨</span> Límite Amarillas: {{ $tournament->rules?->yellow_card_limit_for_suspension ?? 2 }}
                </span>
                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-bold">
                    <span>🟥</span> Suspensión Roja: {{ $tournament->rules?->direct_red_suspension_matches ?? 1 }} fecha(s)
                </span>
            </div>
        </div>

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('status') }}
            </div>
        @endif

        <!-- Quick KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card class="bg-gradient-to-br from-rose-950/40 via-slate-900 to-slate-900 border-rose-900/40">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-400">Inhabilitados Vigentes</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-black font-mono text-white">{{ $activeSanctions->count() }}</span>
                    <span class="text-xs text-rose-300">jugadores suspendidos</span>
                </div>
            </x-card>

            <x-card class="bg-gradient-to-br from-amber-950/40 via-slate-900 to-slate-900 border-amber-900/40">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Total Tarjetas Amarillas</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-black font-mono text-white">{{ $yellowCardLeaders->sum('yellow_cards_count') }}</span>
                    <span class="text-xs text-amber-300">amonestaciones en torneo</span>
                </div>
            </x-card>

            <x-card class="bg-gradient-to-br from-purple-950/40 via-slate-900 to-slate-900 border-purple-900/40">
                <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Total Tarjetas Rojas</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-black font-mono text-white">{{ $redCardLeaders->sum('red_cards_count') }}</span>
                    <span class="text-xs text-purple-300">expulsiones registradas</span>
                </div>
            </x-card>
        </div>

        <!-- Section 1: Active Sanctions -->
        <x-card class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-500 animate-ping"></span>
                    <h2 class="text-lg font-bold text-white">Sanciones Activas (Inhabilitaciones Vigentes para la Próxima Fecha)</h2>
                </div>
                <span class="text-xs text-slate-400">Filtro Tripartito activo en la alineación</span>
            </div>

            @if ($activeSanctions->isEmpty())
                <div class="p-8 text-center text-slate-400 italic">
                    <span class="text-3xl block mb-2">🛡️</span>
                    No hay jugadores con sanciones disciplinarias activas en este momento. Fair play impecable.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/80 text-xs uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Jugador</th>
                                <th class="px-4 py-3">Equipo</th>
                                <th class="px-4 py-3">Causa de Sanción</th>
                                <th class="px-4 py-3 text-center">Fechas</th>
                                <th class="px-4 py-3 text-center">Restantes</th>
                                <th class="px-4 py-3">Detalle / Origen</th>
                                @if (auth()->user()?->isSuperAdmin() || (auth()->user()?->isAdmin() && $tournament->admin_id === auth()->id()))
                                    <th class="px-4 py-3 text-right">Acción</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach ($activeSanctions as $sanction)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="px-4 py-3 font-semibold text-white">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-slate-800 font-mono text-xs flex items-center justify-center text-slate-300">#{{ $sanction->player->jersey_number }}</span>
                                            <span>{{ $sanction->player->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-300">{{ $sanction->player->team->name }}</td>
                                    <td class="px-4 py-3">
                                        @if ($sanction->sanction_type === 'direct_red')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                                🟥 Roja Directa
                                            </span>
                                        @elseif ($sanction->sanction_type === 'double_yellow')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                                🟨🟨 Doble Amarilla
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                                🟨 Acumulación Amarillas
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-bold text-slate-300">
                                        {{ $sanction->matches_suspended }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded font-mono font-black text-xs bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            {{ $sanction->remainingMatches() }} fecha(s)
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-400 max-w-xs truncate" title="{{ $sanction->notes }}">
                                        {{ $sanction->notes }}
                                    </td>
                                    @if (auth()->user()?->isSuperAdmin() || (auth()->user()?->isAdmin() && $tournament->admin_id === auth()->id()))
                                        <td class="px-4 py-3 text-right">
                                            <form action="{{ route('tournaments.disciplinary.pardon', [$tournament, $sanction]) }}" method="POST" onsubmit="return confirm('¿Confirma indultar administrativamente esta sanción? El jugador quedará habilitado de inmediato.')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold transition">
                                                    Indultar
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        <!-- Section 2: Cards Leaderboards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Yellow Cards Ranking -->
            <x-card class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <h3 class="font-bold text-white flex items-center gap-2">
                        <span>🟨</span> Tarjetas Amarillas Acumuladas
                    </h3>
                    <span class="text-xs text-slate-400">Límite: {{ $tournament->rules?->yellow_card_limit_for_suspension ?? 2 }}</span>
                </div>

                @if ($yellowCardLeaders->isEmpty())
                    <p class="text-xs text-slate-400 py-4 italic text-center">Sin amonestaciones registradas.</p>
                @else
                    <div class="divide-y divide-slate-800/60 text-xs">
                        @php $limit = $tournament->rules?->yellow_card_limit_for_suspension ?? 2; @endphp
                        @foreach ($yellowCardLeaders as $player)
                            <div class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded bg-slate-800 text-slate-300 font-mono text-[10px] flex items-center justify-center">#{{ $player->jersey_number }}</span>
                                    <div>
                                        <p class="font-bold text-white">{{ $player->name }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $player->team->name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($player->yellow_cards_count % $limit === $limit - 1)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            ¡En capilla! (1 para suspensión)
                                        </span>
                                    @endif
                                    <span class="w-7 h-7 rounded-lg bg-yellow-500/20 border border-yellow-500/30 font-mono font-black text-yellow-300 flex items-center justify-center">
                                        {{ $player->yellow_cards_count }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            <!-- Red Cards Ranking -->
            <x-card class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <h3 class="font-bold text-white flex items-center gap-2">
                        <span>🟥</span> Tarjetas Rojas en el Torneo
                    </h3>
                    <span class="text-xs text-slate-400">Expulsiones</span>
                </div>

                @if ($redCardLeaders->isEmpty())
                    <p class="text-xs text-slate-400 py-4 italic text-center">Sin tarjetas rojas registradas en el torneo.</p>
                @else
                    <div class="divide-y divide-slate-800/60 text-xs">
                        @foreach ($redCardLeaders as $player)
                            <div class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded bg-slate-800 text-slate-300 font-mono text-[10px] flex items-center justify-center">#{{ $player->jersey_number }}</span>
                                    <div>
                                        <p class="font-bold text-white">{{ $player->name }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $player->team->name }}</p>
                                    </div>
                                </div>
                                <span class="w-7 h-7 rounded-lg bg-rose-500/20 border border-rose-500/30 font-mono font-black text-rose-300 flex items-center justify-center">
                                    {{ $player->red_cards_count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Section 3: History of Served or Pardoned Sanctions -->
        @if ($historySanctions->isNotEmpty())
            <x-card class="space-y-3">
                <h3 class="font-bold text-white text-sm">Historial de Sanciones Cumplidas o Indultadas</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="py-2">Jugador</th>
                                <th class="py-2">Equipo</th>
                                <th class="py-2">Tipo</th>
                                <th class="py-2">Estado</th>
                                <th class="py-2">Notas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 text-slate-300">
                            @foreach ($historySanctions as $sanction)
                                <tr>
                                    <td class="py-2 font-semibold text-white">{{ $sanction->player->name }}</td>
                                    <td class="py-2">{{ $sanction->player->team->name }}</td>
                                    <td class="py-2 capitalize">{{ str_replace('_', ' ', $sanction->sanction_type) }}</td>
                                    <td class="py-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $sanction->status === 'pardoned' ? 'bg-purple-500/20 text-purple-300' : 'bg-slate-800 text-slate-300' }}">
                                            {{ $sanction->status === 'pardoned' ? 'Indultada' : 'Cumplida' }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-slate-400">{{ $sanction->notes }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>
@endsection
