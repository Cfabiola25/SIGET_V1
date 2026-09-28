@extends('v1.layouts.app')

@section('title', "Radar de {$player->name}")

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('scouting.index') }}" class="text-xs font-semibold text-cyan-400 hover:underline">← Volver al Radar de Talentos</a>
                <span class="text-slate-600">•</span>
                <span class="text-xs text-slate-400">Ficha Técnica Algorítmica</span>
            </div>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">{{ $player->name }}</h1>
            <p class="text-sm text-slate-400">{{ $player->profile?->position_label ?? 'Mediocampista' }} • {{ $player->profile?->preferred_foot_label ?? 'Diestro' }} • {{ $player->profile?->nationality ?? 'Nacional' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('players.cromo', $player) }}" target="_blank" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-bold text-slate-200 transition hover:bg-slate-700">
                Ver Cromo Coleccionable
            </a>
            <a href="{{ route('players.carnet', $player) }}" target="_blank" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-bold text-slate-200 transition hover:bg-slate-700">
                Ver Carnet QR
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-300">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->has('scouting'))
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-300">
            {{ $errors->first('scouting') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Columna 1: Radar Gráfico de Habilidades -->
        <div class="flex flex-col items-center justify-center rounded-3xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-xl shadow-xl">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">Pentágono de Rendimiento</h3>
            <div class="relative size-64 sm:size-72">
                @php
                    $cx = 140; $cy = 140; $r = 100;
                    $scores = [
                        $metrics['scoring'] / 10,
                        $metrics['discipline'] / 10,
                        $metrics['leadership'] / 10,
                        $metrics['consistency'] / 10,
                        $metrics['overall'] / 10,
                    ];
                    $points = [];
                    foreach ($scores as $i => $s) {
                        $angle = deg2rad(-90 + ($i * 72));
                        $px = $cx + ($r * $s * cos($angle));
                        $py = $cy + ($r * $s * sin($angle));
                        $points[] = "{$px},{$py}";
                    }
                    $polyPoints = implode(' ', $points);
                @endphp
                <svg viewBox="0 0 280 280" class="size-full">
                    <!-- Background Grid concentric circles -->
                    <circle cx="140" cy="140" r="100" fill="none" stroke="#334155" stroke-dasharray="4 4" stroke-width="1"/>
                    <circle cx="140" cy="140" r="75" fill="none" stroke="#334155" stroke-dasharray="3 3" stroke-width="1"/>
                    <circle cx="140" cy="140" r="50" fill="none" stroke="#334155" stroke-dasharray="2 2" stroke-width="1"/>
                    <circle cx="140" cy="140" r="25" fill="none" stroke="#334155" stroke-dasharray="1 1" stroke-width="1"/>

                    <!-- Axes -->
                    @for($i=0; $i<5; $i++)
                        @php
                            $a = deg2rad(-90 + ($i * 72));
                            $ax = 140 + (100 * cos($a));
                            $ay = 140 + (100 * sin($a));
                        @endphp
                        <line x1="140" y1="140" x2="{{ $ax }}" y2="{{ $ay }}" stroke="#334155" stroke-width="1.5"/>
                    @endfor

                    <!-- Radar Polygon Area -->
                    <polygon points="{{ $polyPoints }}" fill="rgba(6, 182, 212, 0.35)" stroke="#06b6d4" stroke-width="2.5"/>

                    <!-- Vertex Dots -->
                    @foreach($points as $pt)
                        @php [$px, $py] = explode(',', $pt); @endphp
                        <circle cx="{{ $px }}" cy="{{ $py }}" r="4" fill="#38bdf8" stroke="#082f49" stroke-width="2"/>
                    @endforeach
                </svg>
            </div>

            <!-- Labels for the 5 vertices -->
            <div class="mt-4 grid grid-cols-2 gap-2 w-full text-xs font-semibold text-slate-300">
                <div class="flex items-center justify-between rounded-lg bg-slate-950 p-2 border border-slate-800">
                    <span class="text-slate-400">Goleador:</span>
                    <span class="text-cyan-400">{{ $metrics['scoring'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-950 p-2 border border-slate-800">
                    <span class="text-slate-400">Disciplina:</span>
                    <span class="text-emerald-400">{{ $metrics['discipline'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-950 p-2 border border-slate-800">
                    <span class="text-slate-400">Liderazgo/MVP:</span>
                    <span class="text-amber-400">{{ $metrics['leadership'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-950 p-2 border border-slate-800">
                    <span class="text-slate-400">Regularidad:</span>
                    <span class="text-indigo-400">{{ $metrics['consistency'] }}</span>
                </div>
            </div>
        </div>

        <!-- Columna 2: Ficha Técnica & Estadísticas -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Overall Score Card -->
            <div class="flex items-center justify-between rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 to-slate-950 p-6 shadow-xl">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-cyan-400">Calificación Algorítmica SIGET</span>
                    <h2 class="text-3xl font-black text-white sm:text-4xl mt-1">{{ number_format($metrics['overall'], 1) }} <span class="text-lg text-amber-400">★</span></h2>
                    <p class="text-xs text-slate-400 mt-1">Calculado dinámicamente con base en partidos jugados, goles, tarjetas y premios MVP.</p>
                </div>
                <div class="text-right">
                    @if($player->team)
                        <span class="inline-flex rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">
                            En Plantel: {{ $player->team->name }} (#{{ $player->jersey_number ?? 'S/N' }})
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-bold text-cyan-400 border border-cyan-500/20">
                            Agente Libre Disponible
                        </span>
                    @endif
                </div>
            </div>

            <!-- Detailed Grid of Metrics -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4 text-center">
                    <span class="block text-2xl font-black text-white">{{ $metrics['matches_played'] }}</span>
                    <span class="text-xs font-semibold text-slate-400">Partidos Oficiales</span>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4 text-center">
                    <span class="block text-2xl font-black text-cyan-400">{{ $metrics['goals_scored'] }}</span>
                    <span class="text-xs font-semibold text-slate-400">Goles Marcados</span>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4 text-center">
                    <span class="block text-2xl font-black text-amber-400">{{ $metrics['mvp_awards'] }}</span>
                    <span class="text-xs font-semibold text-slate-400">Premios MVP</span>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4 text-center">
                    <span class="block text-2xl font-black text-rose-400">{{ $player->redCardsCount() }}</span>
                    <span class="text-xs font-semibold text-slate-400">Tarjetas Rojas</span>
                </div>
            </div>

            <!-- Scouting Notes -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-md">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">Notas de Scouting & Perfil</h3>
                <p class="mt-2 text-sm text-slate-400 leading-relaxed">
                    {{ $player->profile?->scouting_notes ?? 'Jugador con perfil versátil y disciplina táctica comprobada. Ha demostrado regularidad en las convocatorias y capacidad de adaptación en distintas zonas del terreno.' }}
                </p>
            </div>

            <!-- Recruitment Action for DTs -->
            @if($availableTeams->isNotEmpty())
                <div class="rounded-2xl border border-cyan-500/30 bg-cyan-950/20 p-6 backdrop-blur-md">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-cyan-400">Fichar Jugador para tu Club</h3>
                    <p class="text-xs text-slate-400 mt-1">Incorpora a este talento a tu nómina activa seleccionando el club y el dorsal.</p>
                    <form method="POST" action="{{ route('scouting.recruit', $player) }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                        @csrf
                        <select name="team_id" required class="flex-1 rounded-xl border border-slate-800 bg-slate-950 px-3 py-2.5 text-xs text-slate-200 focus:border-cyan-500 focus:outline-none">
                            @foreach($availableTeams as $t)
                                <option value="{{ $t->id }}">Fichar en {{ $t->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="jersey_number" placeholder="Dorsal (1-99)" min="1" max="99" class="w-32 rounded-xl border border-slate-800 bg-slate-950 px-3 py-2.5 text-xs text-slate-200 focus:border-cyan-500 focus:outline-none">
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-teal-400 px-6 py-2.5 text-xs font-black text-slate-950 shadow-md shadow-cyan-500/20 hover:from-cyan-400 transition">
                            Confirmar Fichaje
                        </button>
                    </form>
                </div>
            @endif

            <!-- Release to free agency button if user manages this team -->
            @if($player->team && $availableTeams->pluck('id')->contains($player->team_id))
                <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-4 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-300">Dar de baja al jugador</h4>
                        <p class="text-[11px] text-slate-500">Transfiere al jugador de regreso a la Agencia Libre.</p>
                    </div>
                    <form method="POST" action="{{ route('scouting.release', $player) }}" onsubmit="return confirm('¿Seguro que deseas liberar a este jugador a la agencia libre?')">
                        @csrf
                        <button type="submit" class="rounded-xl border border-rose-800 bg-rose-950/40 px-3 py-1.5 text-xs font-bold text-rose-300 hover:bg-rose-900 transition">
                            Liberar a Agencia Libre
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
