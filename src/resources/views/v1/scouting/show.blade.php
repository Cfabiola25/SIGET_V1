@extends('v1.layouts.app')

@section('title', "Radar de {$player->name}")

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('scouting.index') }}" class="text-xs font-semibold text-[#057a55] hover:underline flex items-center gap-1">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Radar de Talentos
                </a>
                <span class="text-slate-300">•</span>
                <span class="text-xs text-slate-500 font-medium">Ficha Técnica Algorítmica</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $player->name }}</h1>
            <p class="text-xs text-slate-500">{{ $player->profile?->position_label ?? 'Mediocampista' }} • {{ $player->profile?->preferred_foot_label ?? 'Diestro' }} • {{ $player->profile?->nationality ?? 'Nacional' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('players.cromo', $player) }}" target="_blank" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 shadow-2xs">
                Cromo Coleccionable
            </a>
            <a href="{{ route('players.carnet', $player) }}" target="_blank" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 shadow-2xs">
                Carnet QR
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->has('scouting'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Columna 1: Radar Gráfico de Habilidades -->
        <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Pentágono de Rendimiento</h3>
            <div class="relative size-60 sm:size-64">
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
                    <!-- Concentric Circles -->
                    <circle cx="140" cy="140" r="100" fill="none" stroke="#e2e8f0" stroke-dasharray="4 4" stroke-width="1.5"/>
                    <circle cx="140" cy="140" r="75" fill="none" stroke="#e2e8f0" stroke-dasharray="3 3" stroke-width="1"/>
                    <circle cx="140" cy="140" r="50" fill="none" stroke="#e2e8f0" stroke-dasharray="2 2" stroke-width="1"/>
                    <circle cx="140" cy="140" r="25" fill="none" stroke="#e2e8f0" stroke-dasharray="1 1" stroke-width="1"/>

                    <!-- Axes -->
                    @for($i=0; $i<5; $i++)
                        @php
                            $a = deg2rad(-90 + ($i * 72));
                            $ax = 140 + (100 * cos($a));
                            $ay = 140 + (100 * sin($a));
                        @endphp
                        <line x1="140" y1="140" x2="{{ $ax }}" y2="{{ $ay }}" stroke="#cbd5e1" stroke-width="1.5"/>
                    @endfor

                    <!-- Radar Polygon Area -->
                    <polygon points="{{ $polyPoints }}" fill="rgba(5, 122, 85, 0.2)" stroke="#057a55" stroke-width="2.5"/>

                    <!-- Vertex Dots -->
                    @foreach($points as $pt)
                        @php [$px, $py] = explode(',', $pt); @endphp
                        <circle cx="{{ $px }}" cy="{{ $py }}" r="4.5" fill="#057a55" stroke="#ffffff" stroke-width="2"/>
                    @endforeach
                </svg>
            </div>

            <!-- Labels for the 5 vertices -->
            <div class="mt-4 grid grid-cols-2 gap-2 w-full text-xs font-semibold">
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-2 border border-slate-100">
                    <span class="text-slate-500">Goleador:</span>
                    <span class="text-emerald-700 font-bold">{{ $metrics['scoring'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-2 border border-slate-100">
                    <span class="text-slate-500">Disciplina:</span>
                    <span class="text-emerald-700 font-bold">{{ $metrics['discipline'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-2 border border-slate-100">
                    <span class="text-slate-500">Liderazgo/MVP:</span>
                    <span class="text-amber-600 font-bold">{{ $metrics['leadership'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-2 border border-slate-100">
                    <span class="text-slate-500">Regularidad:</span>
                    <span class="text-blue-700 font-bold">{{ $metrics['consistency'] }}</span>
                </div>
            </div>
        </div>

        <!-- Columna 2: Ficha Técnica & Estadísticas -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Overall Score Card -->
            <div class="flex items-center justify-between rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Calificación Algorítmica SIGET</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($metrics['overall'], 1) }} <span class="text-xl text-amber-500">★</span></h2>
                    <p class="text-xs text-slate-500 mt-1">Calculado con base en partidos disputados, goles, tarjetas y premios MVP.</p>
                </div>
                <div class="text-right">
                    @if($player->team)
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                            En Plantel: {{ $player->team->name }} (#{{ $player->jersey_number ?? 'S/N' }})
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-800 border border-cyan-200">
                            Agente Libre Disponible
                        </span>
                    @endif
                </div>
            </div>

            <!-- Detailed Grid of Metrics -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 text-center shadow-2xs">
                    <span class="block text-2xl font-extrabold text-slate-900">{{ $metrics['matches_played'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">Partidos Oficiales</span>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 text-center shadow-2xs">
                    <span class="block text-2xl font-extrabold text-emerald-700">{{ $metrics['goals_scored'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">Goles Marcados</span>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 text-center shadow-2xs">
                    <span class="block text-2xl font-extrabold text-amber-600">{{ $metrics['mvp_awards'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">Premios MVP</span>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 text-center shadow-2xs">
                    <span class="block text-2xl font-extrabold text-rose-600">{{ $player->redCardsCount() }}</span>
                    <span class="text-xs font-semibold text-slate-500">Tarjetas Rojas</span>
                </div>
            </div>

            <!-- Scouting Notes -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Notas de Scouting & Perfil</h3>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    {{ $player->profile?->scouting_notes ?? 'Jugador con perfil versátil y disciplina táctica comprobada. Ha demostrado regularidad en las convocatorias y capacidad de adaptación en distintas zonas del terreno.' }}
                </p>
            </div>

            <!-- Recruitment Action for DTs -->
            @if($availableTeams->isNotEmpty())
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-6 shadow-2xs">
                    <h3 class="text-sm font-bold text-emerald-950">Fichar Jugador para tu Club</h3>
                    <p class="text-xs text-emerald-800 mt-0.5">Incorpora a este talento a tu nómina activa seleccionando el club y el dorsal.</p>
                    <form method="POST" action="{{ route('scouting.recruit', $player) }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                        @csrf
                        <select name="team_id" required class="flex-1 rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                            @foreach($availableTeams as $t)
                                <option value="{{ $t->id }}">Fichar en {{ $t->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="jersey_number" placeholder="Dorsal (1-99)" min="1" max="99" class="w-32 rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                        <button type="submit" class="rounded-lg bg-[#057a55] px-5 py-2 text-xs font-semibold text-white hover:bg-[#046c4b] transition shadow-2xs">
                            Confirmar Fichaje
                        </button>
                    </form>
                </div>
            @endif

            <!-- Release to free agency button if user manages this team -->
            @if($player->team && $availableTeams->pluck('id')->contains($player->team_id))
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 flex items-center justify-between shadow-2xs">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Dar de baja al jugador</h4>
                        <p class="text-[11px] text-slate-500">Transfiere al jugador de regreso a la Agencia Libre.</p>
                    </div>
                    <form method="POST" action="{{ route('scouting.release', $player) }}" onsubmit="return confirm('¿Seguro que deseas liberar a este jugador a la agencia libre?')">
                        @csrf
                        <button type="submit" class="rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition shadow-2xs">
                            Liberar a Agencia Libre
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
