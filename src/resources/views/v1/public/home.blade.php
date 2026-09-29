@extends('v1.layouts.app')

@section('title', 'El Torneo en Tus Manos | SIGET-SF')

@section('content')
    <!-- 1. HERO BANNER PRINCIPAL: ULTRA-PREMIUM CINEMATIC DESIGN -->
    <div class="relative overflow-hidden rounded-3xl mb-12 border border-white/[0.08] shadow-2xl bg-slate-950 group">
        <!-- Imagen de Fondo del Estadio con Zoom Suave y Parallax -->
        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-1000 ease-out group-hover:scale-105 opacity-60"
             style="background-image: url('{{ asset('images/stadium_hero.jpg') }}');"></div>
        
        <!-- Multi-Capa de Gradientes Ambientales (Obsidian + Emerald Glow) -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#070a13] via-[#070a13]/70 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#070a13]/90 via-transparent to-[#070a13]/90"></div>
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 size-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 px-6 py-20 sm:px-12 sm:py-24 lg:py-28 max-w-4xl mx-auto text-center">
            <!-- Badge de Torneo Oficial con Efecto Neón -->
            <div class="inline-flex items-center gap-2.5 rounded-full glass-pill px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-emerald-300 mb-6 border border-emerald-500/30 shadow-lg shadow-emerald-500/10">
                <span class="flex size-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>🏆 {{ $tournament?->name ?? 'Copa Élite SIGET-SF 2026' }}</span>
                <span class="text-white/20">|</span>
                <span class="text-slate-400 font-normal lowercase">fase regular</span>
            </div>

            <!-- Título Monumental -->
            <h1 class="font-display text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.05] drop-shadow-2xl">
                El Torneo en <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 bg-clip-text text-transparent">Tus Manos</span>
            </h1>

            <!-- Subtítulo Pulcro -->
            <p class="mt-6 text-base sm:text-xl text-slate-300 max-w-2xl mx-auto font-normal leading-relaxed">
                Sigue cada partido, analiza las estadísticas en tiempo real y vota por el MVP de la jornada. La plataforma oficial de gestión deportiva <span class="font-bold text-white tracking-wide">SIGET-SF</span>.
            </p>

            <!-- Botones de Acción Principal -->
            <div class="mt-8 flex flex-wrap justify-center items-center gap-4">
                <a href="#partidos-section" class="inline-flex items-center gap-2.5 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-teal-400 px-8 py-3.5 text-xs font-black uppercase tracking-wider text-slate-950 shadow-xl shadow-emerald-500/30 transition transform hover:-translate-y-0.5 hover:shadow-emerald-500/50 active:translate-y-0 group/btn">
                    <span>VER CALENDARIO</span>
                    <svg class="size-4 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ route('standings.index') }}" class="inline-flex items-center gap-2 rounded-full glass-card px-7 py-3.5 text-xs font-bold tracking-wider text-slate-200 hover:text-white hover:border-emerald-500/40 transition transform hover:-translate-y-0.5">
                    <svg class="size-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M3 14h18M3 18h18M3 6h18"/></svg>
                    <span>Ver Clasificación</span>
                </a>
            </div>

            <!-- Micro-Highlights Bar -->
            <div class="mt-12 pt-8 border-t border-white/[0.08] grid grid-cols-3 gap-4 max-w-2xl mx-auto text-left">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                        ⚡
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white">En Vivo</div>
                        <div class="text-[11px] text-slate-400">Marcador en directo</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                        <svg class="size-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white">Voto Fan MVP</div>
                        <div class="text-[11px] text-slate-400">Elige a la figura</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
                        🤖
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white">Crónicas IA</div>
                        <div class="text-[11px] text-slate-400">Análisis automático</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. CONTENIDO PRINCIPAL: DOS COLUMNAS -->
    <div id="partidos-section" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- COLUMNA IZQUIERDA: Partidos de Hoy & Noticias -->
        <div class="lg:col-span-8 space-y-12">
            
            <!-- SECCIÓN: Partidos de Hoy / Jornada -->
            <div>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 shadow-sm">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-display text-2xl font-extrabold text-white tracking-tight">Partidos de Hoy</h2>
                            <p class="text-xs text-slate-400">Encuentros programados y en desarrollo de la jornada</p>
                        </div>
                    </div>
                    <a href="{{ route('matches.index') }}" class="group inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 hover:text-emerald-300 transition">
                        <span>Ver todos</span>
                        <span class="transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @forelse($featuredMatches->take(4) as $match)
                        <div class="glass-card glass-card-hover rounded-2xl p-5 flex flex-col justify-between group">
                            <!-- Barra superior de la tarjeta -->
                            <div class="flex items-center justify-between text-xs pb-3 mb-3 border-b border-white/[0.06]">
                                <span class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-emerald-300">
                                    Jornada {{ $match->round_number }}
                                </span>
                                <div class="flex items-center gap-2 text-slate-400 text-xs">
                                    <span class="flex items-center gap-1">
                                        <svg class="size-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $match->match_date->format('H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Centro: Equipos y Marcador -->
                            <div class="grid grid-cols-7 items-center gap-2 my-2">
                                <!-- Equipo Local (cols 3) -->
                                <div class="col-span-3 flex flex-col items-center text-center">
                                    <div class="relative size-14 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-white/10 flex items-center justify-center shadow-lg group-hover:border-emerald-500/50 transition">
                                        <span class="font-display font-black text-lg text-emerald-400">
                                            {{ strtoupper(substr($match->homeTeam->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-slate-200 line-clamp-1 max-w-[110px]" title="{{ $match->homeTeam->name }}">
                                        {{ $match->homeTeam->name }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 uppercase font-semibold">Local</span>
                                </div>

                                <!-- Marcador & Estado Central (col 1) -->
                                <div class="col-span-1 flex flex-col items-center justify-center">
                                    @if($match->status === 'played')
                                        <div class="flex items-center gap-1 font-mono text-2xl font-black text-white tracking-tight">
                                            <span>{{ $match->home_score }}</span>
                                            <span class="text-slate-600">-</span>
                                            <span>{{ $match->away_score }}</span>
                                        </div>
                                        <span class="mt-1 inline-block rounded-md bg-slate-800/80 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-400 border border-slate-700/60">
                                            FINAL
                                        </span>
                                    @elseif($match->status === 'live' || $match->is_timer_running)
                                        <div class="flex items-center gap-1 font-mono text-2xl font-black text-emerald-400 tracking-tight animate-pulse">
                                            <span>{{ $match->home_score }}</span>
                                            <span class="text-emerald-600">-</span>
                                            <span>{{ $match->away_score }}</span>
                                        </div>
                                        <span class="mt-1 inline-flex items-center gap-1 rounded-md bg-emerald-500/20 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-emerald-400 border border-emerald-500/30">
                                            <span class="size-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                            VIVO
                                        </span>
                                    @else
                                        <div class="font-mono text-base font-bold text-slate-500">
                                            VS
                                        </div>
                                        <span class="mt-1 inline-block text-[9px] font-bold uppercase tracking-wider text-slate-500">
                                            FIXTURE
                                        </span>
                                    @endif
                                </div>

                                <!-- Equipo Visitante (cols 3) -->
                                <div class="col-span-3 flex flex-col items-center text-center">
                                    <div class="relative size-14 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-white/10 flex items-center justify-center shadow-lg group-hover:border-cyan-500/50 transition">
                                        <span class="font-display font-black text-lg text-cyan-400">
                                            {{ strtoupper(substr($match->awayTeam->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-slate-200 line-clamp-1 max-w-[110px]" title="{{ $match->awayTeam->name }}">
                                        {{ $match->awayTeam->name }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 uppercase font-semibold">Visita</span>
                                </div>
                            </div>

                            <!-- Botón Interactivo: Modal En Vivo y Voto MVP -->
                            <div class="mt-4 pt-3 border-t border-white/[0.06]">
                                <button type="button" 
                                        onclick="openMatchLiveModal({{ $match->id }}, '{{ addslashes($match->homeTeam->name) }}', '{{ addslashes($match->awayTeam->name) }}', {{ $match->home_score }}, {{ $match->away_score }}, '{{ $match->status }}', '{{ $match->formatted_clock }}')"
                                        class="w-full rounded-xl bg-slate-800/80 hover:bg-emerald-500 text-slate-200 hover:text-slate-950 px-3 py-2 text-xs font-bold transition flex items-center justify-center gap-1.5 border border-white/[0.06] hover:border-emerald-400 shadow-md">
                                    <span>Votar MVP / Ver En Vivo</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 glass-card rounded-2xl p-10 text-center text-slate-400">
                            <span class="text-3xl mb-2 block">⚽</span>
                            No hay encuentros programados en esta jornada.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- SECCIÓN: Noticias / Crónicas Destacadas -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/25 text-amber-400 shadow-sm">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-display text-2xl font-extrabold text-white tracking-tight">Noticias & Crónicas</h2>
                        <p class="text-xs text-slate-400">Resúmenes oficiales generados con inteligencia artificial</p>
                    </div>
                </div>

                @php
                    $latestChronicle = $chronicles->first();
                @endphp

                <div class="glass-card glass-card-hover rounded-2xl p-5 overflow-hidden flex flex-col md:flex-row gap-6 items-center group">
                    <div class="w-full md:w-5/12 shrink-0 overflow-hidden rounded-xl h-56 md:h-48 relative">
                        <img src="{{ asset('images/soccer_news.jpg') }}" alt="Crónica del Partido" class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 glass-pill px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider text-emerald-400 border border-emerald-500/30">
                            SIGET-SF OFICIAL
                        </span>
                    </div>
                    <div class="flex-1 space-y-3">
                        <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-emerald-400">
                            <span class="size-1.5 rounded-full bg-emerald-400"></span>
                            <span>ACTUALIDAD DE LA COMPETICIÓN</span>
                        </div>
                        <h3 class="font-display text-xl font-extrabold text-white leading-snug group-hover:text-emerald-300 transition">
                            {{ $latestChronicle?->chronicle_title ?? 'Resultados sorpresivos en la jornada de fin de semana' }}
                        </h3>
                        <p class="text-xs text-slate-300 leading-relaxed line-clamp-3">
                            {{ $latestChronicle?->chronicle_body ?? 'Los equipos considerados favoritos tropezaron en sus respectivos encuentros, dejando la tabla de posiciones más apretada que nunca. Los directores técnicos ajustan sus tácticas de cara a las próximas fechas decisivas.' }}
                        </p>
                        <div class="pt-2 flex items-center gap-4 text-xs text-slate-400 font-medium border-t border-white/[0.06]">
                            <span class="flex items-center gap-1.5">
                                <svg class="size-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $latestChronicle ? $latestChronicle->match_date->format('d M, Y') : now()->format('d M, Y') }}
                            </span>
                            <span class="text-slate-600">·</span>
                            <span class="text-emerald-400 font-semibold flex items-center gap-1">
                                <svg class="size-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Acta Oficial Verificada
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: Equipos de la Competencia -->
            <div>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-cyan-500/10 border border-cyan-500/25 text-cyan-400 shadow-sm">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-display text-2xl font-extrabold text-white tracking-tight">Equipos en Competencia</h2>
                            <p class="text-xs text-slate-400">Directores técnicos y plantillas activas</p>
                        </div>
                    </div>
                    <a href="{{ route('teams.index') }}" class="group inline-flex items-center gap-1.5 text-xs font-bold text-cyan-400 hover:text-cyan-300 transition">
                        <span>Ver planteles completos</span>
                        <span class="transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($teams as $team)
                        <div class="glass-card glass-card-hover rounded-2xl p-4 text-center flex flex-col justify-between group">
                            <div>
                                <div class="size-16 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-white/10 mx-auto flex items-center justify-center font-display font-black text-xl text-emerald-400 mb-3 shadow-lg group-hover:scale-105 transition transform">
                                    {{ strtoupper(substr($team->name, 0, 2)) }}
                                </div>
                                <h4 class="font-display font-extrabold text-white text-sm line-clamp-1 group-hover:text-emerald-300 transition">{{ $team->name }}</h4>
                                <div class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-slate-800/80 px-2.5 py-0.5 text-[10px] text-slate-300 font-semibold border border-slate-700/60">
                                    <span>👔 DT:</span>
                                    <span class="text-slate-100 truncate max-w-[100px]">{{ $team->coach_name }}</span>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between text-xs text-slate-400 font-medium">
                                <span>{{ $team->players->count() }} Jugadores</span>
                                <a href="{{ route('teams.show', $team) }}" class="text-emerald-400 font-bold hover:underline">Ficha →</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- COLUMNA DERECHA: Tabla de Posiciones Sticky -->
        <div class="lg:col-span-4">
            <div class="glass-card rounded-3xl overflow-hidden shadow-2xl sticky top-24 border border-white/[0.08]">
                
                <!-- Encabezado de la Tabla -->
                <div class="p-5 border-b border-white/[0.06] flex items-center justify-between bg-slate-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M3 14h18M3 18h18M3 6h18"/></svg>
                        </div>
                        <h3 class="font-display text-lg font-black text-white tracking-tight">Tabla de Posiciones</h3>
                    </div>
                    <span class="rounded-full bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-emerald-400">
                        En Vivo
                    </span>
                </div>

                <!-- Cuerpo de la Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/70 text-[11px] uppercase tracking-wider text-slate-400 border-b border-white/[0.06]">
                            <tr>
                                <th class="py-3 px-4 w-8 font-bold">#</th>
                                <th class="py-3 px-2 font-bold">Equipo</th>
                                <th class="py-3 px-3 text-center font-bold">PJ</th>
                                <th class="py-3 px-3 text-center font-bold">DG</th>
                                <th class="py-3 px-4 text-center font-black text-emerald-400">PTS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04] font-medium">
                            @forelse($standings as $index => $standing)
                                <tr class="hover:bg-white/[0.03] transition {{ $index < 2 ? 'bg-emerald-500/[0.02]' : '' }}">
                                    <td class="py-3.5 px-4 font-bold text-xs">
                                        @if($index === 0)
                                            <span class="flex size-5 items-center justify-center rounded-full bg-amber-400 text-slate-950 font-black text-[10px] shadow-sm">1</span>
                                        @elseif($index === 1)
                                            <span class="flex size-5 items-center justify-center rounded-full bg-slate-300 text-slate-950 font-black text-[10px] shadow-sm">2</span>
                                        @elseif($index === 2)
                                            <span class="flex size-5 items-center justify-center rounded-full bg-amber-700 text-white font-black text-[10px] shadow-sm">3</span>
                                        @else
                                            <span class="text-slate-500">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-2">
                                        <div class="flex items-center gap-2.5">
                                            <span class="size-7 rounded-lg bg-slate-800 border border-white/10 flex items-center justify-center text-[10px] font-black text-emerald-400 shrink-0">
                                                {{ strtoupper(substr($standing->team->name, 0, 2)) }}
                                            </span>
                                            <span class="font-bold text-slate-100 truncate max-w-[120px] text-xs">
                                                {{ $standing->team->name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center text-slate-300 font-mono text-xs">
                                        {{ $standing->matches_played }}
                                    </td>
                                    @php
                                        $dg = $standing->goals_for - $standing->goals_against;
                                    @endphp
                                    <td class="py-3.5 px-3 text-center font-mono text-xs {{ $dg > 0 ? 'text-emerald-400 font-bold' : ($dg < 0 ? 'text-rose-400 font-bold' : 'text-slate-400') }}">
                                        {{ $dg > 0 ? '+'.$dg : $dg }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-black font-mono text-emerald-400 text-sm">
                                        <span class="inline-block rounded-md bg-emerald-500/10 px-2 py-0.5 border border-emerald-500/20">
                                            {{ $standing->points }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500 text-xs">Sin datos de clasificación disponibles.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer de la Tabla -->
                <div class="p-4 border-t border-white/[0.06] text-center bg-slate-950/50">
                    <a href="{{ route('standings.index') }}" class="group inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-emerald-400 hover:text-emerald-300 transition py-1">
                        <span>VER TABLA COMPLETA</span>
                        <span class="transition-transform group-hover:translate-x-1">→</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. MODAL INTERACTIVO: EN VIVO & VOTACIÓN MVP DE FANÁTICOS -->
    <div id="liveMatchModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/85 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl rounded-3xl glass-card p-6 sm:p-8 shadow-2xl shadow-emerald-950/50 border border-white/10 animate-in fade-in zoom-in-95 duration-200">
            <!-- Botón Cerrar -->
            <button onclick="closeMatchLiveModal()" class="absolute top-5 right-5 text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/[0.06] transition">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Título y Estado -->
            <div class="text-center mb-6">
                <span id="modalMatchStatus" class="inline-block rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-3.5 py-1 text-xs font-black uppercase tracking-wider mb-3">
                    En Vivo
                </span>
                <div class="flex items-center justify-center gap-4 text-xl sm:text-2xl font-display font-black text-white">
                    <span id="modalHomeTeam" class="text-emerald-400">Local</span>
                    <span id="modalScore" class="bg-slate-950 border border-white/10 px-5 py-2 rounded-2xl font-mono text-3xl sm:text-4xl text-white shadow-inner">0 - 0</span>
                    <span id="modalAwayTeam" class="text-cyan-400">Visitante</span>
                </div>
                <p id="modalClock" class="mt-2 text-xs font-mono text-slate-400">Cronómetro Oficial: 00:00</p>
            </div>

            <!-- Votación de Aficionados: MVP -->
            <div class="rounded-2xl border border-amber-500/30 bg-amber-500/[0.08] p-5 mb-6">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="size-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <h4 class="font-display font-extrabold text-white text-sm">Votación en Vivo: Jugador Más Valioso (MVP)</h4>
                </div>
                <p class="text-xs text-amber-200/80 mb-4 leading-relaxed">
                    Como aficionado, ¡vota por la figura del encuentro! Los votos de la fanaticada se contabilizan en tiempo real para la entrega del trofeo oficial.
                </p>

                <form id="mvpVoteForm" onsubmit="submitMvpVote(event)" class="space-y-3">
                    @csrf
                    <input type="hidden" id="modalMatchId" name="match_id">
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <select id="mvpPlayerSelect" name="player_id" required class="flex-1 rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-xs text-white focus:border-amber-400 focus:outline-none">
                            <option value="">Selecciona el jugador que merece el MVP...</option>
                        </select>
                        <button type="submit" id="btnSubmitMvp" class="rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black px-6 py-3 text-xs uppercase tracking-wider transition shadow-lg shadow-amber-500/25 active:scale-95">
                            Votar MVP
                        </button>
                    </div>
                </form>

                <div id="mvpVoteFeedback" class="mt-3 hidden rounded-xl p-3 text-xs font-bold text-center"></div>

                <!-- Desglose de votos en vivo -->
                <div id="mvpLiveStatsContainer" class="mt-5 pt-3 border-t border-amber-500/20">
                    <div class="flex justify-between text-xs text-amber-300 font-bold mb-2">
                        <span>Votos Registrados en Tiempo Real</span>
                        <span id="mvpTotalVotes" class="font-mono">0 votos</span>
                    </div>
                    <div id="mvpBreakdownList" class="space-y-2"></div>
                </div>
            </div>

            <!-- Timeline de Eventos Minuto a Minuto -->
            <div class="border-t border-white/[0.06] pt-5">
                <h5 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                    <span>⏱️</span> Bitácora Minuto a Minuto del Partido
                </h5>
                <div id="modalEventsList" class="space-y-2 max-h-48 overflow-y-auto pr-1 text-sm text-slate-300">
                    <p class="text-xs text-slate-500 italic">Cargando eventos en tiempo real...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS PARA LA INTERACTIVIDAD DEL MODAL Y VOTACIÓN MVP -->
    <script>
        let currentModalMatchId = null;

        function openMatchLiveModal(matchId, homeName, awayName, homeScore, awayScore, status, clock) {
            currentModalMatchId = matchId;
            document.getElementById('modalMatchId').value = matchId;
            document.getElementById('modalHomeTeam').innerText = homeName;
            document.getElementById('modalAwayTeam').innerText = awayName;
            document.getElementById('modalScore').innerText = `${homeScore} - ${awayScore}`;
            document.getElementById('modalMatchStatus').innerText = status === 'played' ? 'FINALIZADO' : (status === 'live' ? 'EN VIVO' : 'PROGRAMADO');
            document.getElementById('modalClock').innerText = `Tiempo de Juego: ${clock}`;
            
            document.getElementById('mvpVoteFeedback').classList.add('hidden');
            document.getElementById('liveMatchModal').classList.remove('hidden');

            loadMatchLiveFeed(matchId);
            loadMvpLiveStats(matchId);
        }

        function closeMatchLiveModal() {
            document.getElementById('liveMatchModal').classList.add('hidden');
            currentModalMatchId = null;
        }

        async function loadMatchLiveFeed(matchId) {
            try {
                const res = await fetch(`/matches/${matchId}/live-feed`);
                if (!res.ok) return;
                const data = await res.json();

                document.getElementById('modalScore').innerText = `${data.home_score} - ${data.away_score}`;
                document.getElementById('modalClock').innerText = `Tiempo: ${data.formatted_clock}`;

                const eventsContainer = document.getElementById('modalEventsList');
                if (data.events && data.events.length > 0) {
                    eventsContainer.innerHTML = data.events.map(e => `
                        <div class="flex items-center gap-3 bg-slate-950/60 p-2.5 rounded-xl border border-white/[0.06]">
                            <span class="font-mono text-xs font-black text-emerald-400 w-12">${e.formatted_time}</span>
                            <span class="text-base">${e.icon}</span>
                            <div class="flex-1">
                                <span class="font-bold text-white text-xs">${e.player_name}</span>
                                <span class="text-[11px] text-slate-400">(${e.team_name}) - ${e.label}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    eventsContainer.innerHTML = '<p class="text-xs text-slate-500 italic">No hay eventos registrados aún en este encuentro.</p>';
                }
            } catch (err) {
                console.error("Error cargando live feed:", err);
            }
        }

        async function loadMvpLiveStats(matchId) {
            try {
                const res = await fetch(`/matches/${matchId}/mvp/live-stats`);
                if (!res.ok) return;
                const data = await res.json();

                const select = document.getElementById('mvpPlayerSelect');
                select.innerHTML = '<option value="">Selecciona el jugador que merece el MVP...</option>';

                if (data.lineup_players) {
                    data.lineup_players.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = `#${p.jersey_number} ${p.name} (${p.team_name} - ${p.position})`;
                        select.appendChild(opt);
                    });
                }

                const voteForm = document.getElementById('mvpVoteForm');
                const feedback = document.getElementById('mvpVoteFeedback');

                if (data.is_locked && data.official_mvp) {
                    voteForm.classList.add('hidden');
                    feedback.classList.remove('hidden');
                    feedback.className = "mt-2 p-3.5 rounded-xl text-center bg-amber-500/20 text-amber-300 border border-amber-500/40";
                    feedback.innerHTML = `
                        <div class="text-xs uppercase font-black text-amber-400">🏆 MVP Oficial Coronado</div>
                        <div class="text-base font-extrabold text-white mt-0.5">${data.official_mvp.name}</div>
                        <div class="text-[11px] text-amber-200/90">${data.official_mvp.team} · Calificación: ${data.official_mvp.rating || '9.0'} / 10</div>
                    `;
                } else {
                    voteForm.classList.remove('hidden');
                }

                document.getElementById('mvpTotalVotes').innerText = `${data.total_votes || 0} votos registrados`;

                const breakdown = document.getElementById('mvpBreakdownList');
                if (data.breakdown && data.breakdown.length > 0) {
                    breakdown.innerHTML = data.breakdown.map(item => `
                        <div>
                            <div class="flex justify-between text-[11px] text-slate-300 font-semibold mb-1">
                                <span>${item.player_name}</span>
                                <span class="text-amber-400 font-mono">${item.percentage}% (${item.votes})</span>
                            </div>
                            <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-amber-400 to-amber-300 h-full rounded-full transition-all duration-500" style="width: ${item.percentage}%"></div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    breakdown.innerHTML = '<p class="text-xs text-slate-500 italic">Sé el primero en votar por el MVP de este partido.</p>';
                }
            } catch (err) {
                console.error("Error cargando estadísticas MVP:", err);
            }
        }

        async function submitMvpVote(e) {
            e.preventDefault();
            if (!currentModalMatchId) return;

            const select = document.getElementById('mvpPlayerSelect');
            const playerId = select.value;
            if (!playerId) return;

            const feedback = document.getElementById('mvpVoteFeedback');
            const btn = document.getElementById('btnSubmitMvp');
            btn.disabled = true;

            try {
                const res = await fetch(`/matches/${currentModalMatchId}/mvp/vote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ player_id: playerId })
                });

                const data = await res.json();
                feedback.classList.remove('hidden');

                if (res.ok && data.success) {
                    feedback.className = "mt-3 p-3 rounded-xl text-xs font-bold text-center bg-emerald-500/20 text-emerald-400 border border-emerald-500/30";
                    feedback.innerText = "¡Tu voto ha sido registrado con éxito!";
                    loadMvpLiveStats(currentModalMatchId);
                } else {
                    feedback.className = "mt-3 p-3 rounded-xl text-xs font-bold text-center bg-rose-500/20 text-rose-400 border border-rose-500/30";
                    feedback.innerText = data.message || "Error registrando el voto.";
                }
            } catch (err) {
                feedback.classList.remove('hidden');
                feedback.className = "mt-3 p-3 rounded-xl text-xs font-bold text-center bg-rose-500/20 text-rose-400 border border-rose-500/30";
                feedback.innerText = "No se pudo conectar con el servidor.";
            } finally {
                btn.disabled = false;
            }
        }
    </script>
@endsection
