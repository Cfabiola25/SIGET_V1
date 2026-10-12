@extends('v1.layouts.public')

@section('title', 'Radar de Talento: ' . $player->name)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Migas de Pan -->
    <div class="flex items-center justify-between text-xs">
        <a href="{{ route('scouting.index') }}" class="font-bold text-[#1e3a8a] hover:underline flex items-center gap-1">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver al Radar de Talentos</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('players.cromo', $player) }}" target="_blank" 
               class="rounded-lg bg-emerald-50 hover:bg-[#1e3a8a] text-emerald-800 hover:text-white px-3 py-1.5 text-xs font-bold transition">
                Cromo Coleccionable
            </a>
            <a href="{{ route('players.carnet', $player) }}" target="_blank" 
               class="rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 px-3 py-1.5 text-xs font-bold transition">
                Carnet QR
            </a>
        </div>
    </div>

    <!-- TARJETA PRINCIPAL DEL JUGADOR -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden p-6 sm:p-10 flex flex-col md:flex-row items-center md:items-start gap-8">
        
        <!-- Avatar Gigante / Cromo Thumbnail -->
        <div class="size-28 sm:size-32 rounded-3xl bg-slate-100 border-2 border-slate-200 text-slate-700 font-black text-4xl flex items-center justify-center shadow-xs shrink-0">
            {{ strtoupper(substr($player->name, 0, 2)) }}
        </div>

        <!-- Info Principal -->
        <div class="flex-1 text-center md:text-left space-y-2">
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 text-xs">
                <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full font-bold bg-cyan-100 text-cyan-900 border border-cyan-300">
                    {{ $player->profile?->position_label ?? ucfirst($player->position) }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full font-black bg-amber-100 text-amber-900 border border-amber-300">
                    <x-reicon name="star" variant="filled" class="size-3.5 text-amber-500 shrink-0" />
                    <span>Rating: {{ number_format($player->profile?->performance_rating ?? $player->profile?->scouting_rating ?? 8.0, 1) }} / 10</span>
                </span>
            </div>

            <h1 class="font-display text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                {{ $player->name }}
            </h1>

            <div class="text-sm text-slate-500 flex flex-wrap items-center justify-center md:justify-start gap-3 pt-1">
                <span><strong>Pierna:</strong> {{ $player->profile?->preferred_foot_label ?? 'Diestro' }}</span>
                <span>&bull;</span>
                <span><strong>Nacionalidad:</strong> {{ $player->profile?->nationality ?? 'Colombia' }}</span>
                <span>&bull;</span>
                <span><strong>Estado:</strong> {{ $player->team ? $player->team->name : 'Agente Libre' }}</span>
            </div>

            @if($player->profile?->scouting_notes || $player->profile?->bio)
                <p class="text-sm text-slate-600 pt-2 italic max-w-2xl leading-relaxed">
                    "{{ $player->profile->scouting_notes ?? $player->profile->bio }}"
                </p>
            @endif
        </div>

    </div>

    <!-- PENTÁGONO DE RENDIMIENTO & ATRIBUTOS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Pentágono / Radar Gráfico SVG -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs flex flex-col items-center justify-center">
            <h3 class="font-display font-bold text-sm text-slate-900 uppercase tracking-wider mb-4">
                Pentágono de Rendimiento
            </h3>
            
            <div class="relative size-64 sm:size-72">
                @php
                    $cx = 140; $cy = 140; $r = 100;
                    $scores = [
                        $metrics['scoring'] / 10,
                        $metrics['discipline'] / 10,
                        $metrics['playmaking'] / 10,
                        $metrics['defense'] / 10,
                        $metrics['mvp_impact'] / 10,
                    ];
                    $labels = ['Goleo', 'Disciplina', 'Creación', 'Defensa', 'Impacto'];
                    $points = [];
                    for ($i = 0; $i < 5; $i++) {
                        $angle = deg2rad($i * 72 - 90);
                        $px = $cx + $r * $scores[$i] * cos($angle);
                        $py = $cy + $r * $scores[$i] * sin($angle);
                        $points[] = "{$px},{$py}";
                    }
                    $polyPoints = implode(' ', $points);
                @endphp

                <svg viewBox="0 0 280 280" class="size-full">
                    @for($lvl = 1; $lvl <= 5; $lvl++)
                        @php
                            $lr = $r * ($lvl / 5);
                            $gridPts = [];
                            for ($i = 0; $i < 5; $i++) {
                                $angle = deg2rad($i * 72 - 90);
                                $gx = $cx + $lr * cos($angle);
                                $gy = $cy + $lr * sin($angle);
                                $gridPts[] = "{$gx},{$gy}";
                            }
                        @endphp
                        <polygon points="{{ implode(' ', $gridPts) }}" fill="none" stroke="#e2e8f0" stroke-width="1"/>
                    @endfor

                    <polygon points="{{ $polyPoints }}" fill="rgba(5, 122, 85, 0.25)" stroke="#1e3a8a" stroke-width="2.5"/>

                    @for($i = 0; $i < 5; $i++)
                        @php
                            $angle = deg2rad($i * 72 - 90);
                            $lx = $cx + ($r + 22) * cos($angle);
                            $ly = $cy + ($r + 22) * sin($angle);
                        @endphp
                        <text x="{{ $lx }}" y="{{ $ly }}" font-size="10" font-weight="700" fill="#475569" text-anchor="middle" dominant-baseline="middle">
                            {{ $labels[$i] }}
                        </text>
                    @endfor
                </svg>
            </div>
        </div>

        <!-- Desglose Numérico de Atributos -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs space-y-5">
            <h3 class="font-display font-bold text-sm text-slate-900 uppercase tracking-wider">
                Métricas del Algoritmo
            </h3>

            <div class="space-y-4 text-xs">
                <div>
                    <div class="flex justify-between font-bold text-slate-700 mb-1">
                        <span>Puntería & Goleo</span>
                        <span class="text-emerald-700 font-mono">{{ $metrics['scoring'] }} / 10</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full" style="width: {{ $metrics['scoring'] * 10 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between font-bold text-slate-700 mb-1">
                        <span>Disciplina & Fair Play</span>
                        <span class="text-emerald-700 font-mono">{{ $metrics['discipline'] }} / 10</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full" style="width: {{ $metrics['discipline'] * 10 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between font-bold text-slate-700 mb-1">
                        <span>Creación de Juego & Visión</span>
                        <span class="text-emerald-700 font-mono">{{ $metrics['playmaking'] }} / 10</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full" style="width: {{ $metrics['playmaking'] * 10 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between font-bold text-slate-700 mb-1">
                        <span>Rigor Defensivo</span>
                        <span class="text-emerald-700 font-mono">{{ $metrics['defense'] }} / 10</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full" style="width: {{ $metrics['defense'] * 10 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between font-bold text-slate-700 mb-1">
                        <span>Impacto MVP en la Cancha</span>
                        <span class="text-amber-700 font-mono">{{ $metrics['mvp_impact'] }} / 10</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-amber-500 h-full rounded-full" style="width: {{ $metrics['mvp_impact'] * 10 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Resumen Informativo -->
            <div class="pt-4 border-t border-slate-100 text-xs text-slate-500">
                Calificación generada por el motor de análisis deportivo SIGET conforme a minutos jugados, incidencias y valoraciones del cuerpo arbitral.
            </div>
        </div>

    </div>

</div>
@endsection
