@extends('v1.layouts.app')

@section('title', 'Llaves de Eliminación Directa: ' . $tournament->name)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('tournaments.show', $tournament) }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-emerald-400 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al Torneo
            </a>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">Cuadro de Playoff y Eliminación Directa</h1>
            <p class="text-sm text-slate-400">Estructura de llaves (Brackets) con progresión y cruces automáticos para <strong class="text-white">{{ $tournament->name }}</strong>.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('standings.index', ['tournament' => $tournament->id]) }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition flex items-center gap-1.5">
                <span>📊</span> Tabla de Posiciones
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    @if (! $hasBrackets)
        <!-- Bracket Initialization Wizard -->
        <x-card class="bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 border-slate-800 space-y-6">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-2xl">
                    🏆
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">Transición a Fase de Playoffs</h2>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        No se han generado llaves de eliminación directa para este torneo. Puedes inicializar la fase final basándote en la tabla de posiciones oficial. El sistema emparejará automáticamente a los mejores clasificados y conectará el avance hacia la Gran Final.
                    </p>
                </div>
            </div>

            <form action="{{ route('tournaments.brackets.generate', $tournament) }}" method="POST" class="space-y-4 pt-4 border-t border-slate-800">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="bracket_size" class="block text-xs font-semibold text-slate-300 mb-1">Estructura del Cuadro</label>
                        <select name="bracket_size" id="bracket_size" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                            <option value="4" selected>4 Clasificados (Semifinales y Final)</option>
                            <option value="8">8 Clasificados (Cuartos, Semifinales y Final)</option>
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-slate-300 mb-1">Fecha Primera Ronda Playoff</label>
                        <input type="date" name="start_date" id="start_date" value="{{ now()->next(\Carbon\Carbon::SATURDAY)->format('Y-m-d') }}" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="days_between_stages" class="block text-xs font-semibold text-slate-300 mb-1">Días entre Rondas</label>
                        <input type="number" name="days_between_stages" id="days_between_stages" value="7" min="1" max="30" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- Preview of Top Teams from Standings -->
                <div class="mt-4 pt-4 border-t border-slate-800/80">
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Previsualización de Posibles Clasificados</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @php $topTeams = $tournament->standings->sortByDesc('points')->take(8); @endphp
                        @forelse ($topTeams as $standing)
                            <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-emerald-400">#{{ $loop->iteration }}</span>
                                    <span class="font-semibold text-white truncate">{{ $standing->team->name }}</span>
                                </div>
                                <span class="font-mono text-slate-400 text-[11px]">{{ $standing->points }} pts</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 italic col-span-4">Aún no hay puntos registrados en la tabla de posiciones. Se tomarán los primeros equipos registrados.</p>
                        @endforelse
                    </div>
                </div>

                @auth
                    @if (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $tournament->admin_id === auth()->id()))
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs transition shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                                <span>⚡</span> Activar Llaves de Eliminación Directa
                            </button>
                        </div>
                    @endif
                @endauth
            </form>
        </x-card>
    @else
        <!-- Visual Bracket Tree -->
        <div class="space-y-4">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Árbol de Eliminación Activo</span>
                </div>
                <span class="text-xs text-slate-400 font-mono">El ganador de cada serie avanza automáticamente al cerrarse el acta</span>
            </div>

            <!-- Bracket Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- 1. Cuartos de Final (si existen) -->
                @if ($bracketTree['quarterfinals']->isNotEmpty())
                    <div class="space-y-4">
                        <div class="text-center py-2 px-3 rounded-xl bg-slate-900 border border-slate-800">
                            <h3 class="text-xs font-black uppercase tracking-wider text-emerald-400">Cuartos de Final</h3>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $bracketTree['quarterfinals']->count() }} Cruces</span>
                        </div>

                        <div class="space-y-4">
                            @foreach ($bracketTree['quarterfinals'] as $match)
                                @include('v1.tournaments.partials.bracket-card', ['match' => $match])
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 2. Semifinales -->
                <div class="space-y-4 {{ $bracketTree['quarterfinals']->isEmpty() ? 'md:col-span-2' : '' }}">
                    <div class="text-center py-2 px-3 rounded-xl bg-slate-900 border border-slate-800">
                        <h3 class="text-xs font-black uppercase tracking-wider text-cyan-400">Semifinales</h3>
                        <span class="text-[10px] text-slate-500 font-mono">{{ $bracketTree['semifinals']->count() }} Cruces</span>
                    </div>

                    <div class="space-y-4">
                        @foreach ($bracketTree['semifinals'] as $match)
                            @include('v1.tournaments.partials.bracket-card', ['match' => $match])
                        @endforeach
                    </div>
                </div>

                <!-- 3. Gran Final -->
                <div class="space-y-4">
                    <div class="text-center py-2 px-3 rounded-xl bg-gradient-to-r from-amber-500/20 via-yellow-500/20 to-amber-500/20 border border-amber-500/40">
                        <h3 class="text-xs font-black uppercase tracking-wider text-amber-300 flex items-center justify-center gap-1.5">
                            <span>👑</span> Gran Final de Campeonato
                        </h3>
                        <span class="text-[10px] text-amber-400/80 font-mono">Partido por el Título</span>
                    </div>

                    @if ($bracketTree['final'])
                        <div class="p-1 rounded-2xl bg-gradient-to-b from-amber-500/40 to-yellow-500/10 shadow-xl shadow-amber-500/5">
                            @include('v1.tournaments.partials.bracket-card', ['match' => $bracketTree['final'], 'isFinal' => true])
                        </div>
                    @else
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-500 italic">
                            Por definir tras las semifinales.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
