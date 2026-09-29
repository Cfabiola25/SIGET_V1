@extends('v1.layouts.app')

@section('title', 'Llaves de Eliminación Directa: ' . $tournament->name)
@section('header_title', 'Playoff Brackets')

@section('content')
<div class="space-y-6">

    <!-- Header bar -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-800">
                <a href="{{ route('tournaments.show', $tournament) }}" class="hover:underline">&larr; Volver al Torneo</a>
            </div>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight mt-1">Cuadro de Playoff y Eliminación Directa</h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Estructura de llaves (Brackets) con progresión y cruces automáticos para <strong>{{ $tournament->name }}</strong>.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('standings.index', ['tournament' => $tournament->id]) }}" class="px-4 py-2 rounded-lg bg-sky-100 text-sky-900 text-xs font-bold hover:bg-sky-200 transition flex items-center gap-1.5">
                <span>📊</span> Tabla de Posiciones
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if (! $hasBrackets)
        <!-- Bracket Initialization Wizard -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 space-y-6">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 text-2xl">
                    🏆
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Transición a Fase de Playoffs</h3>
                    <p class="text-xs md:text-sm text-slate-500 mt-1 leading-relaxed">
                        No se han generado llaves de eliminación directa para este torneo. Puedes inicializar la fase final basándote en la tabla de posiciones oficial. El sistema emparejará automáticamente a los mejores clasificados y conectará el avance hacia la Gran Final.
                    </p>
                </div>
            </div>

            <form action="{{ route('tournaments.brackets.generate', $tournament) }}" method="POST" class="space-y-4 pt-4 border-t border-slate-100">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="bracket_size" class="block text-xs font-semibold text-slate-700 mb-1">Estructura del Cuadro</label>
                        <select name="bracket_size" id="bracket_size" class="w-full rounded-lg bg-white border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                            <option value="4" selected>4 Clasificados (Semifinales y Final)</option>
                            <option value="8">8 Clasificados (Cuartos, Semifinales y Final)</option>
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1">Fecha Primera Ronda Playoff</label>
                        <input type="date" name="start_date" id="start_date" value="{{ now()->next(\Carbon\Carbon::SATURDAY)->format('Y-m-d') }}" class="w-full rounded-lg bg-white border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="days_between_stages" class="block text-xs font-semibold text-slate-700 mb-1">Días entre Rondas</label>
                        <input type="number" name="days_between_stages" id="days_between_stages" value="7" min="1" max="30" class="w-full rounded-lg bg-white border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none">
                    </div>
                </div>

                <!-- Preview of Top Teams from Standings -->
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Previsualización de Posibles Clasificados</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @php $topTeams = $tournament->standings->sortByDesc('points')->take(8); @endphp
                        @forelse ($topTeams as $standing)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-emerald-800">#{{ $loop->iteration }}</span>
                                    <span class="font-semibold text-slate-800 truncate">{{ $standing->team?->name }}</span>
                                </div>
                                <span class="text-slate-500 text-[11px]">{{ $standing->points }} pts</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic col-span-4">Aún no hay puntos registrados en la tabla de posiciones. Se tomarán los primeros equipos registrados.</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white font-bold text-xs transition shadow-xs flex items-center gap-2 cursor-pointer">
                        <span>⚡</span> Activar Llaves de Eliminación Directa
                    </button>
                </div>
            </form>
        </div>
    @else
        <!-- Visual Bracket Tree -->
        <div class="space-y-4">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Árbol de Eliminación Activo</span>
                </div>
                <span class="text-xs text-slate-500">El ganador de cada serie avanza automáticamente al cerrarse el acta</span>
            </div>

            <!-- Bracket Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- 1. Cuartos de Final (si existen) -->
                @if ($bracketTree['quarterfinals']->isNotEmpty())
                    <div class="space-y-4">
                        <div class="text-center py-2 px-3 rounded-xl bg-slate-100 border border-slate-200">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Cuartos de Final</h3>
                            <span class="text-[10px] text-slate-500">{{ $bracketTree['quarterfinals']->count() }} Cruces</span>
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
                    <div class="text-center py-2 px-3 rounded-xl bg-slate-100 border border-slate-200">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-sky-800">Semifinales</h3>
                        <span class="text-[10px] text-slate-500">{{ $bracketTree['semifinals']->count() }} Cruces</span>
                    </div>

                    <div class="space-y-4">
                        @foreach ($bracketTree['semifinals'] as $match)
                            @include('v1.tournaments.partials.bracket-card', ['match' => $match])
                        @endforeach
                    </div>
                </div>

                <!-- 3. Gran Final -->
                <div class="space-y-4">
                    <div class="text-center py-2 px-3 rounded-xl bg-amber-50 border border-amber-200">
                        <h3 class="text-xs font-black uppercase tracking-wider text-amber-900 flex items-center justify-center gap-1.5">
                            <span>👑</span> Gran Final de Campeonato
                        </h3>
                        <span class="text-[10px] text-amber-700">Partido por el Título</span>
                    </div>

                    @if ($bracketTree['final'])
                        <div>
                            @include('v1.tournaments.partials.bracket-card', ['match' => $bracketTree['final'], 'isFinal' => true])
                        </div>
                    @else
                        <div class="p-6 rounded-2xl bg-white border border-slate-200 text-center text-xs text-slate-400 italic">
                            Por definir tras las semifinales.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
