@extends('v1.layouts.public')

@section('title', 'El Torneo en Tus Manos')

@section('content')

    <!-- 1. HERO BANNER PRINCIPAL (IDÉNTICO A LA MAQUETA DEL USUARIO) -->
    <div class="relative overflow-hidden mb-8 sm:mb-12 shadow-md bg-slate-950">
        <!-- Imagen de fondo del estadio -->
        <div class="absolute inset-0 bg-cover bg-center opacity-65"
             style="background-image: url('{{ asset('images/stadium_hero.jpg') }}');"></div>
        
        <!-- Filtros y Gradientes Cinematográficos para máxima legibilidad -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/60 to-slate-950/40"></div>
        <div class="absolute inset-0 bg-radial from-transparent via-slate-950/40 to-slate-950/80"></div>

        <div class="relative z-10 px-4 py-16 sm:px-6 sm:py-24 lg:py-28 max-w-5xl mx-auto text-center">
            
            <!-- Badge de Torneo Oficial -->
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 backdrop-blur-md px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald-300 mb-5 border border-emerald-500/30">
                <span class="size-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>🏆 {{ $tournament?->name ?? 'Copa Élite SIGET-SF 2026' }}</span>
                <span class="text-white/40">|</span>
                <span class="text-slate-300 font-medium capitalize">{{ $tournament?->status ?? 'En Curso' }}</span>
            </div>

            <!-- Título Monumental -->
            <h1 class="font-display text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-tight drop-shadow-lg">
                El Torneo en Tus Manos
            </h1>

            <!-- Subtítulo Pulcro -->
            <p class="mt-4 sm:mt-6 text-sm sm:text-lg lg:text-xl text-slate-200 max-w-2xl mx-auto font-normal leading-relaxed drop-shadow">
                Sigue cada partido, analiza las estadísticas y mantente al día con la acción en vivo. La plataforma oficial de gestión de torneos <span class="font-bold text-white">SIGET-SF</span>.
            </p>

            <!-- Botón de Acción Principal (Pill Verde como en la maqueta) -->
            <div class="mt-8 flex flex-wrap justify-center items-center gap-4">
                <a href="#seccion-partidos" 
                   class="inline-flex items-center gap-2.5 rounded-full bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] px-8 py-3.5 text-xs sm:text-sm font-extrabold uppercase tracking-wider text-white shadow-xl shadow-emerald-950/40 transition transform hover:-translate-y-0.5 group">
                    <span>VER CALENDARIO</span>
                    <svg class="size-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ route('standings.index') }}" 
                   class="inline-flex items-center gap-2 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md px-6 py-3.5 text-xs sm:text-sm font-bold tracking-wider text-white border border-white/20 transition transform hover:-translate-y-0.5">
                    <svg class="size-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M3 14h18M3 18h18M3 6h18"/></svg>
                    <span>Ver Tabla de Posiciones</span>
                </a>
            </div>

            <!-- Resumen de Métricas Rápidas del Torneo -->
            <div class="mt-12 pt-6 border-t border-white/15 grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-3xl mx-auto text-center">
                <div class="p-2">
                    <div class="text-xl sm:text-2xl font-black text-white font-mono">{{ $tournament?->teams->count() ?? 0 }}</div>
                    <div class="text-[11px] text-slate-300 uppercase tracking-wider font-semibold">Equipos Inscritos</div>
                </div>
                <div class="p-2">
                    <div class="text-xl sm:text-2xl font-black text-emerald-400 font-mono">{{ $totalMatchesPlayed }}</div>
                    <div class="text-[11px] text-slate-300 uppercase tracking-wider font-semibold">Partidos Disputados</div>
                </div>
                <div class="p-2">
                    <div class="text-xl sm:text-2xl font-black text-amber-400 font-mono">{{ $totalGoals }}</div>
                    <div class="text-[11px] text-slate-300 uppercase tracking-wider font-semibold">Goles Marcados</div>
                </div>
                <div class="p-2">
                    <div class="text-xl sm:text-2xl font-black text-cyan-400 font-mono">{{ $allTournaments->count() }}</div>
                    <div class="text-[11px] text-slate-300 uppercase tracking-wider font-semibold">Torneos en SIGET</div>
                </div>
            </div>

        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL CON ESPACIADO Y DISEÑO LIMPIO -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- 2. EXPLORADOR & SELECTOR INTUITIVO DE TORNEOS ("que el usuario pueda ver lo mas que pueda sobre los torneos y demás") -->
        <div id="seccion-torneos" class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        <h2 class="font-display text-lg font-bold text-slate-900">Torneos Disponibles</h2>
                    </div>
                    <p class="text-xs text-slate-500">Selecciona cualquier torneo para explorar su fixture, tabla oficial y estadísticas en tiempo real.</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <span>Actualmente viendo:</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 font-bold border border-emerald-200">
                        🏆 {{ $tournament?->name ?? 'Torneo Activo' }}
                    </span>
                </div>
            </div>

            <!-- Carrusel / Pestañas de selección de torneos -->
            <div class="mt-4 flex items-center gap-3 overflow-x-auto pb-2 scrollbar-thin">
                @forelse($allTournaments as $t)
                    @php
                        $isSelected = $tournament && $tournament->id === $t->id;
                    @endphp
                    <a href="{{ url('/?tournament_id=' . $t->id) }}" 
                       class="shrink-0 flex items-center gap-3 px-4 py-2.5 rounded-xl border text-xs font-medium transition-all {{ $isSelected ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm font-bold' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 hover:border-slate-300' }}">
                        <span class="text-base">{{ $t->sport_type === 'Baloncesto' ? '🏀' : ($t->sport_type === 'Voleibol' ? '🏐' : '⚽') }}</span>
                        <div class="text-left">
                            <div class="leading-tight font-bold {{ $isSelected ? 'text-white' : 'text-slate-900' }}">{{ $t->name }}</div>
                            <div class="text-[10px] {{ $isSelected ? 'text-emerald-100' : 'text-slate-500' }}">
                                {{ $t->sport_type }} · {{ $t->teams_count }} equipos
                            </div>
                        </div>
                        @if($t->status === 'active')
                            <span class="size-2 rounded-full {{ $isSelected ? 'bg-white' : 'bg-emerald-500' }} animate-pulse ml-1"></span>
                        @endif
                    </a>
                @empty
                    <p class="text-xs text-slate-400">No hay más torneos registrados en la plataforma.</p>
                @endforelse
            </div>
        </div>

        <!-- 3. CONTENIDO PRINCIPAL: DOS COLUMNAS (EXACTAMENTE COMO EN LA MAQUETA) -->
        <div id="seccion-partidos" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- ================= COLUMNA IZQUIERDA (lg:col-span-8) ================= -->
            <div class="lg:col-span-8 space-y-10">
                
                <!-- SECCIÓN: Partidos de Hoy & Jornada -->
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">⚽</span>
                            <div>
                                <h2 class="font-display text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Partidos de Hoy</h2>
                                <p class="text-xs text-slate-500">Encuentros en vivo, finalizados y programación oficial</p>
                            </div>
                        </div>
                        <a href="{{ route('matches.index') }}" class="group inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-emerald-800 transition">
                            <span>Ver todos</span>
                            <span class="transition-transform group-hover:translate-x-1" aria-hidden="true">&rarr;</span>
                        </a>
                    </div>

                    <!-- Grid de Tarjetas de Partidos (Tarjetas Blancas con círculos de equipos) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse($featuredMatches->take(4) as $match)
                            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md transition duration-200 flex flex-col justify-between group">
                                
                                <!-- Barra Superior de la Tarjeta: Jornada y Horario -->
                                <div class="flex items-center justify-between text-xs pb-3 mb-3 border-b border-slate-100">
                                    <span class="rounded-lg bg-slate-100 text-slate-700 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">
                                        Jornada {{ $match->round_number }}
                                    </span>
                                    <div class="flex items-center gap-1.5 text-slate-500 text-xs font-medium">
                                        <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $match->match_date ? $match->match_date->format('H:i') : '14:00' }}</span>
                                    </div>
                                </div>

                                <!-- Centro: Equipos con Iniciales en Círculos y Marcador Central -->
                                <div class="grid grid-cols-7 items-center gap-2 my-2 text-center">
                                    
                                    <!-- Equipo Local -->
                                    <div class="col-span-3 flex flex-col items-center">
                                        <div class="size-13 rounded-full bg-slate-100 border border-slate-200 text-slate-800 font-bold flex items-center justify-center text-sm shadow-2xs group-hover:border-emerald-500/40 transition">
                                            {{ strtoupper(substr($match->homeTeam?->name ?? 'LOC', 0, 2)) }}
                                        </div>
                                        <span class="mt-2 text-xs font-bold text-slate-800 line-clamp-1 max-w-[110px]" title="{{ $match->homeTeam?->name }}">
                                            {{ $match->homeTeam?->name ?? 'Equipo Local' }}
                                        </span>
                                    </div>

                                    <!-- Marcador & Estado Central -->
                                    <div class="col-span-1 flex flex-col items-center justify-center">
                                        @if($match->status === 'played')
                                            <div class="font-display text-2xl font-black text-slate-900 tracking-tight">
                                                {{ $match->home_score }} - {{ $match->away_score }}
                                            </div>
                                            <span class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                                FINAL
                                            </span>
                                        @elseif($match->status === 'live' || $match->is_timer_running)
                                            <div class="font-display text-2xl font-black text-emerald-600 tracking-tight animate-pulse">
                                                {{ $match->home_score }} - {{ $match->away_score }}
                                            </div>
                                            <span class="mt-1 inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-emerald-600">
                                                <span class="size-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                                EN VIVO
                                            </span>
                                        @else
                                            <div class="font-display text-xl font-bold text-slate-400">
                                                VS
                                            </div>
                                            <span class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                                {{ $match->match_date ? $match->match_date->format('d/m') : 'PRÓXIMO' }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Equipo Visitante -->
                                    <div class="col-span-3 flex flex-col items-center">
                                        <div class="size-13 rounded-full bg-slate-100 border border-slate-200 text-slate-800 font-bold flex items-center justify-center text-sm shadow-2xs group-hover:border-emerald-500/40 transition">
                                            {{ strtoupper(substr($match->awayTeam?->name ?? 'VIS', 0, 2)) }}
                                        </div>
                                        <span class="mt-2 text-xs font-bold text-slate-800 line-clamp-1 max-w-[110px]" title="{{ $match->awayTeam?->name }}">
                                            {{ $match->awayTeam?->name ?? 'Equipo Visita' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Botón Interactivo: Ficha en Vivo & Votación MVP de Fanáticos -->
                                <div class="mt-4 pt-3 border-t border-slate-100">
                                    <button type="button" 
                                            onclick="openMatchLiveModal({{ $match->id }}, '{{ addslashes($match->homeTeam?->name ?? 'Local') }}', '{{ addslashes($match->awayTeam?->name ?? 'Visitante') }}', {{ $match->home_score ?? 0 }}, {{ $match->away_score ?? 0 }}, '{{ $match->status }}', '{{ $match->formatted_clock ?? '00:00' }}')"
                                            class="w-full rounded-xl bg-slate-50 hover:bg-[#057a55] text-slate-700 hover:text-white px-3 py-2 text-xs font-bold transition flex items-center justify-center gap-1.5 border border-slate-200 hover:border-[#057a55]">
                                        <span class="text-sm">⭐</span>
                                        <span>Detalles / Votar MVP</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-xs">
                                <span class="text-3xl block mb-2">⚽</span>
                                No hay partidos registrados en este torneo aún.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- SECCIÓN: Noticias & Crónicas (Idéntico a la Maqueta) -->
                <div>
                    <div class="flex items-center gap-2.5 mb-5">
                        <span class="text-2xl">📰</span>
                        <div>
                            <h2 class="font-display text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Noticias</h2>
                            <p class="text-xs text-slate-500">Crónicas y actualidad oficial del torneo</p>
                        </div>
                    </div>

                    @php
                        $latestChronicle = $chronicles->first();
                    @endphp

                    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs overflow-hidden flex flex-col md:flex-row gap-6 items-center group hover:shadow-md transition">
                        <!-- Imagen de Noticia -->
                        <div class="w-full md:w-5/12 shrink-0 overflow-hidden rounded-xl h-52 md:h-44 relative bg-slate-900">
                            <img src="{{ asset('images/soccer_news.jpg') }}" 
                                 alt="Crónica del Partido" 
                                 class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500">
                        </div>

                        <!-- Contenido de la Noticia -->
                        <div class="flex-1 space-y-2.5">
                            <div class="text-[11px] font-black uppercase tracking-wider text-[#057a55]">
                                ACTUALIDAD
                            </div>
                            <h3 class="font-display text-lg sm:text-xl font-bold text-slate-900 leading-snug group-hover:text-emerald-700 transition">
                                {{ $latestChronicle?->chronicle_title ?? 'Resultados sorpresivos en la jornada de fin de semana' }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed line-clamp-3">
                                {{ $latestChronicle?->chronicle_body ?? 'Los equipos considerados favoritos tropezaron en sus respectivos encuentros, dejando la tabla de posiciones más apretada que nunca. Los directores técnicos ajustan sus tácticas de cara a las próximas fechas decisivas.' }}
                            </p>
                            <div class="pt-2 flex items-center gap-4 text-xs text-slate-400 font-medium border-t border-slate-100">
                                <span>{{ $latestChronicle && $latestChronicle->match_date ? $latestChronicle->match_date->format('d M, Y') : date('d M, Y') }}</span>
                                <span>&bull;</span>
                                <span class="text-emerald-700 font-semibold">Oficial SIGET-SF</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN: Líderes de Goleo & Estadísticas Destacadas -->
                <div id="seccion-estadisticas">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">🌟</span>
                            <div>
                                <h2 class="font-display text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Líderes de Goleo</h2>
                                <p class="text-xs text-slate-500">Máximos artilleros del torneo</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-xs">
                        <div class="divide-y divide-slate-100">
                            @forelse($topScorers as $index => $scorer)
                                <div class="p-4 flex items-center justify-between hover:bg-slate-50/60 transition">
                                    <div class="flex items-center gap-3.5">
                                        <span class="flex size-7 items-center justify-center rounded-full text-xs font-black {{ $index === 0 ? 'bg-amber-100 text-amber-800' : ($index === 1 ? 'bg-slate-200 text-slate-700' : 'bg-amber-50 text-amber-900') }}">
                                            {{ $index + 1 }}
                                        </span>
                                        <div class="size-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-700">
                                            {{ strtoupper(substr($scorer->player?->first_name ?? 'J', 0, 1) . substr($scorer->player?->last_name ?? 'G', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900">
                                                {{ $scorer->player?->first_name }} {{ $scorer->player?->last_name }}
                                            </h4>
                                            <p class="text-[11px] text-slate-500">{{ $scorer->player?->team?->name ?? 'Club' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-mono text-base font-black text-emerald-700">{{ $scorer->goals }}</span>
                                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">Goles</span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-slate-400 text-xs">
                                    Los goleadores se registrarán a medida que se jueguen los encuentros oficiales.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN: Equipos en Competencia -->
                <div id="seccion-equipos">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">🛡️</span>
                            <div>
                                <h2 class="font-display text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Equipos en Competencia</h2>
                                <p class="text-xs text-slate-500">Clubes y plantillas inscritas en este torneo</p>
                            </div>
                        </div>
                        <a href="{{ route('teams.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 transition">
                            Ver todos &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @forelse($teams as $team)
                            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 text-center shadow-xs hover:shadow-md transition flex flex-col justify-between group">
                                <div>
                                    <div class="size-14 rounded-full bg-slate-100 border border-slate-200 text-slate-800 mx-auto flex items-center justify-center font-display font-black text-lg text-emerald-700 mb-2 shadow-2xs group-hover:scale-105 transition transform">
                                        {{ strtoupper(substr($team->name, 0, 2)) }}
                                    </div>
                                    <h4 class="font-bold text-xs sm:text-sm text-slate-900 line-clamp-1 group-hover:text-emerald-700 transition">
                                        {{ $team->name }}
                                    </h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-1">DT: {{ $team->coach_name ?? 'Por definir' }}</p>
                                </div>
                                <div class="mt-3 pt-2 border-t border-slate-100 text-[11px] text-slate-400 font-medium">
                                    {{ $team->players->count() }} Jugadores
                                </div>
                            </div>
                        @empty
                            <p class="col-span-4 text-xs text-slate-500 text-center py-4">No hay equipos registrados aún en este torneo.</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- ================= COLUMNA DERECHA: TABLA DE POSICIONES (lg:col-span-4) ================= -->
            <div id="seccion-posiciones" class="lg:col-span-4 space-y-6">
                
                <!-- TARJETA TABLA DE POSICIONES (IDÉNTICA A LA MAQUETA DEL USUARIO) -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden sticky top-24">
                    
                    <!-- Encabezado de la Tabla -->
                    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📋</span>
                            <h3 class="font-display text-base font-bold text-slate-900">Tabla de Posiciones</h3>
                        </div>
                    </div>

                    <!-- Tabla de Posiciones -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100 font-semibold">
                                <tr>
                                    <th class="py-2.5 px-3 w-8 text-center font-bold">#</th>
                                    <th class="py-2.5 px-2 font-bold">Equipo</th>
                                    <th class="py-2.5 px-2 text-center font-bold">PJ</th>
                                    <th class="py-2.5 px-2 text-center font-bold">DG</th>
                                    <th class="py-2.5 px-3 text-center font-black text-emerald-700">PTS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($standings as $index => $standing)
                                    @php
                                        $dg = $standing->goals_for - $standing->goals_against;
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition {{ $index < 2 ? 'bg-emerald-50/30' : '' }}">
                                        <!-- Puesto (#) -->
                                        <td class="py-3 px-3 text-center font-bold text-slate-700">
                                            {{ $index + 1 }}
                                        </td>

                                        <!-- Equipo con avatar circular -->
                                        <td class="py-3 px-2">
                                            <div class="flex items-center gap-2">
                                                <div class="size-6 rounded-full bg-slate-200/70 text-slate-600 flex items-center justify-center text-[10px] font-bold shrink-0">
                                                    {{ strtoupper(substr($standing->team?->name ?? 'EQ', 0, 2)) }}
                                                </div>
                                                <span class="font-bold text-slate-800 truncate max-w-[110px] text-xs" title="{{ $standing->team?->name }}">
                                                    {{ $standing->team?->name }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Partidos Jugados (PJ) -->
                                        <td class="py-3 px-2 text-center text-slate-600 font-mono">
                                            {{ $standing->matches_played ?? 0 }}
                                        </td>

                                        <!-- Diferencia de Gol (DG) -->
                                        <td class="py-3 px-2 text-center font-mono font-bold {{ $dg > 0 ? 'text-emerald-700' : ($dg < 0 ? 'text-rose-600' : 'text-slate-500') }}">
                                            {{ $dg > 0 ? '+'.$dg : $dg }}
                                        </td>

                                        <!-- Puntos (PTS) en Verde Negrita -->
                                        <td class="py-3 px-3 text-center font-black font-mono text-emerald-700 text-sm">
                                            {{ $standing->points }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-400 text-xs">Sin registros de posiciones en este torneo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Botón VER TABLA COMPLETA al Pie (Igual que la maqueta) -->
                    <div class="p-4 border-t border-slate-100 text-center bg-slate-50/50">
                        <a href="{{ route('standings.index') }}" 
                           class="inline-block text-xs font-black uppercase tracking-wider text-[#057a55] hover:text-[#046c4b] hover:underline transition py-1">
                            VER TABLA COMPLETA
                        </a>
                    </div>
                </div>

                <!-- Próximos Encuentros Rápidos -->
                @if($upcomingMatches->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs">
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
                            <span class="text-base">📅</span>
                            <h4 class="font-display text-sm font-bold text-slate-900">Próximas Fechas</h4>
                        </div>
                        <div class="space-y-2.5">
                            @foreach($upcomingMatches->take(3) as $next)
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                    <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                                        <span class="font-bold text-slate-700">Jornada {{ $next->round_number }}</span>
                                        <span>{{ $next->match_date ? $next->match_date->format('d/m · H:i') : 'Próximamente' }}</span>
                                    </div>
                                    <div class="font-bold text-slate-800 truncate">
                                        {{ $next->homeTeam?->name }} vs {{ $next->awayTeam?->name }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Ficha del Torneo -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs text-xs space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                        <span class="text-base">ℹ️</span>
                        <h4 class="font-display font-bold text-sm text-slate-900">Ficha del Torneo</h4>
                    </div>
                    <div class="space-y-2 text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Deporte:</span>
                            <span class="font-bold text-slate-800">{{ $tournament?->sport_type ?? 'Fútbol' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Estado:</span>
                            <span class="font-bold text-emerald-700 uppercase">{{ $tournament?->status ?? 'Activo' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Equipos:</span>
                            <span class="font-bold text-slate-800">{{ $tournament?->teams->count() ?? 0 }} participantes</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Sede Principal:</span>
                            <span class="font-bold text-slate-800">Estadio Central SIGET</span>
                        </div>
                    </div>
                    @if($tournament?->rules)
                        <div class="pt-2 border-t border-slate-100">
                            <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="text-emerald-700 font-bold hover:underline block text-center">
                                Consultar Reglamento Oficial &rarr;
                            </a>
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>

    <!-- 4. MODAL INTERACTIVO: EN VIVO & VOTACIÓN MVP DE FANÁTICOS -->
    <div id="liveMatchModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-200">
            <!-- Botón Cerrar -->
            <button onclick="closeMatchLiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-800 p-2 rounded-xl hover:bg-slate-100 transition">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Título y Estado -->
            <div class="text-center mb-6">
                <span id="modalMatchStatus" class="inline-block rounded-full bg-emerald-100 text-emerald-800 px-3 py-1 text-xs font-black uppercase tracking-wider mb-2">
                    En Vivo
                </span>
                <div class="flex items-center justify-center gap-3 text-lg sm:text-xl font-display font-black text-slate-900">
                    <span id="modalHomeTeam" class="text-emerald-700">Local</span>
                    <span id="modalScore" class="bg-slate-100 border border-slate-200 px-4 py-1.5 rounded-xl font-mono text-2xl sm:text-3xl text-slate-900">0 - 0</span>
                    <span id="modalAwayTeam" class="text-slate-800">Visitante</span>
                </div>
                <p id="modalClock" class="mt-2 text-xs font-mono text-slate-500">Cronómetro Oficial: 00:00</p>
            </div>

            <!-- Votación de Aficionados: MVP -->
            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 mb-5">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="text-base text-amber-600">⭐</span>
                    <h4 class="font-display font-bold text-slate-900 text-sm">Votación en Vivo: Jugador del Partido (MVP)</h4>
                </div>
                <p class="text-xs text-slate-600 mb-3 leading-relaxed">
                    ¡Vota por la figura del encuentro! Los votos de la fanaticada premian al jugador más destacado.
                </p>

                <form id="mvpVoteForm" onsubmit="submitMvpVote(event)" class="space-y-3">
                    @csrf
                    <input type="hidden" id="modalMatchId" name="match_id">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <select id="mvpPlayerSelect" name="player_id" required class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-emerald-600 focus:outline-none">
                            <option value="">Selecciona el jugador para el MVP...</option>
                        </select>
                        <button type="submit" id="btnSubmitMvp" class="rounded-lg bg-[#057a55] hover:bg-[#046c4b] text-white font-bold px-5 py-2 text-xs uppercase tracking-wider transition shadow-sm active:scale-95">
                            Votar
                        </button>
                    </div>
                </form>

                <div id="mvpVoteFeedback" class="mt-3 hidden rounded-lg p-2.5 text-xs font-bold text-center"></div>

                <!-- Desglose de votos -->
                <div id="mvpLiveStatsContainer" class="mt-4 pt-3 border-t border-amber-200">
                    <div class="flex justify-between text-xs text-amber-900 font-bold mb-1.5">
                        <span>Votos Registrados</span>
                        <span id="mvpTotalVotes" class="font-mono">0 votos</span>
                    </div>
                    <div id="mvpBreakdownList" class="space-y-1.5"></div>
                </div>
            </div>

            <!-- Timeline de Eventos Minuto a Minuto -->
            <div class="border-t border-slate-100 pt-4">
                <h5 class="text-xs font-black uppercase tracking-wider text-slate-600 mb-2.5 flex items-center gap-1.5">
                    <span>⏱️</span> Incidencias del Partido
                </h5>
                <div id="modalEventsList" class="space-y-2 max-h-40 overflow-y-auto pr-1 text-xs text-slate-700">
                    <p class="text-xs text-slate-400 italic">Cargando incidencias del encuentro...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS PARA MODAL Y VOTACIÓN MVP -->
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
                        <div class="flex items-center gap-2.5 bg-slate-50 p-2 rounded-lg border border-slate-100">
                            <span class="font-mono text-xs font-black text-emerald-700 w-10">${e.formatted_time}</span>
                            <span class="text-sm">${e.icon}</span>
                            <div class="flex-1">
                                <span class="font-bold text-slate-800 text-xs">${e.player_name}</span>
                                <span class="text-[11px] text-slate-500">(${e.team_name}) - ${e.label}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    eventsContainer.innerHTML = '<p class="text-xs text-slate-400 italic">No hay incidencias registradas en este encuentro aún.</p>';
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
                select.innerHTML = '<option value="">Selecciona el jugador para el MVP...</option>';

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
                    feedback.className = "mt-2 p-3 rounded-lg text-center bg-amber-100 text-amber-900 border border-amber-300";
                    feedback.innerHTML = `
                        <div class="text-xs uppercase font-black text-amber-800">🏆 MVP Oficial Coronado</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-0.5">${data.official_mvp.name}</div>
                        <div class="text-[11px] text-slate-600">${data.official_mvp.team} · Calificación: ${data.official_mvp.rating || '9.0'} / 10</div>
                    `;
                } else {
                    voteForm.classList.remove('hidden');
                }

                document.getElementById('mvpTotalVotes').innerText = `${data.total_votes || 0} votos`;

                const breakdown = document.getElementById('mvpBreakdownList');
                if (data.breakdown && data.breakdown.length > 0) {
                    breakdown.innerHTML = data.breakdown.map(item => `
                        <div>
                            <div class="flex justify-between text-[11px] text-slate-700 font-semibold mb-1">
                                <span>${item.player_name}</span>
                                <span class="text-amber-700 font-mono">${item.percentage}% (${item.votes})</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: ${item.percentage}%"></div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    breakdown.innerHTML = '<p class="text-xs text-slate-400 italic">Sé el primero en votar por el MVP de este partido.</p>';
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
                    feedback.className = "mt-3 p-2.5 rounded-lg text-xs font-bold text-center bg-emerald-100 text-emerald-800 border border-emerald-300";
                    feedback.innerText = "¡Tu voto ha sido registrado con éxito!";
                    loadMvpLiveStats(currentModalMatchId);
                } else {
                    feedback.className = "mt-3 p-2.5 rounded-lg text-xs font-bold text-center bg-rose-100 text-rose-800 border border-rose-300";
                    feedback.innerText = data.message || "Error registrando el voto.";
                }
            } catch (err) {
                feedback.classList.remove('hidden');
                feedback.className = "mt-3 p-2.5 rounded-lg text-xs font-bold text-center bg-rose-100 text-rose-800 border border-rose-300";
                feedback.innerText = "No se pudo conectar con el servidor.";
            } finally {
                btn.disabled = false;
            }
        }
    </script>
@endsection
