@extends('v1.layouts.app')

@section('title', 'El Torneo en Tus Manos')

@section('content')
    <!-- 1. HERO BANNER PRINCIPAL (Fiel a la referencia) -->
    <div class="relative overflow-hidden rounded-3xl mb-12 shadow-2xl border border-slate-800/80">
        <!-- Imagen de Fondo del Estadio -->
        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-1000 scale-100 hover:scale-105"
             style="background-image: url('{{ asset('images/stadium_hero.jpg') }}');"></div>
        <!-- Gradiente de superposición para legibilidad premium -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/65 to-slate-900/40"></div>

        <div class="relative z-10 px-6 py-20 sm:px-12 sm:py-28 lg:py-32 max-w-4xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 px-3.5 py-1 text-xs font-bold uppercase tracking-widest text-emerald-400 mb-6 backdrop-blur-md">
                ⚽ {{ $tournament?->name ?? 'Copa Élite SIGET 2026' }}
            </span>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.08] drop-shadow-md">
                El Torneo en Tus Manos
            </h1>
            <p class="mt-5 text-base sm:text-xl text-slate-200 max-w-2xl mx-auto font-normal leading-relaxed drop-shadow">
                Sigue cada partido, analiza las estadísticas y mantente al día con la acción en vivo. La plataforma oficial de gestión de torneos <span class="font-bold text-white">SIGET-SF</span>.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="#partidos-section" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold px-8 py-3.5 text-sm uppercase tracking-wider shadow-lg shadow-emerald-500/30 transition transform hover:-translate-y-0.5">
                    VER CALENDARIO
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ route('standings.index') }}" class="inline-flex items-center gap-2 rounded-full bg-slate-900/80 hover:bg-slate-800 text-white font-bold px-7 py-3.5 text-sm border border-slate-700/80 backdrop-blur-md transition">
                    Ver Clasificación
                </a>
            </div>
        </div>
    </div>

    <!-- 2. CONTENIDO PRINCIPAL: DOS COLUMNAS (PARTIDOS/NOTICIAS A LA IZQUIERDA Y TABLA A LA DERECHA) -->
    <div id="partidos-section" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- COLUMNA IZQUIERDA: Partidos de Hoy & Noticias -->
        <div class="lg:col-span-8 space-y-12">
            
            <!-- SECCIÓN: Partidos de Hoy -->
            <div>
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-emerald-400 text-2xl">⚽</span>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Partidos de Hoy</h2>
                    </div>
                    <a href="{{ route('matches.index') }}" class="text-sm font-semibold text-emerald-400 hover:text-emerald-300 transition flex items-center gap-1">
                        Ver todos <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($featuredMatches->take(4) as $match)
                        <div class="group relative rounded-2xl border border-slate-800/90 bg-slate-900/70 p-5 shadow-lg shadow-black/20 hover:border-emerald-500/40 hover:bg-slate-900 transition backdrop-blur-md">
                            <!-- Barra superior de la tarjeta -->
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-4 pb-2 border-b border-slate-800/50">
                                <span class="rounded-md bg-slate-800 px-2 py-0.5 font-bold uppercase tracking-wider text-slate-300">
                                    Jornada {{ $match->round_number }}
                                </span>
                                <span class="flex items-center gap-1.5 font-medium text-slate-400">
                                    <svg class="size-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $match->match_date->format('H:i') }}
                                </span>
                            </div>

                            <!-- Centro: Equipos y Marcador -->
                            <div class="flex items-center justify-between gap-3 text-center my-2">
                                <!-- Equipo Local -->
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="size-14 rounded-full bg-slate-800 border-2 border-slate-700/80 flex items-center justify-center shadow-md group-hover:border-emerald-500/50 transition">
                                        <span class="font-extrabold text-base text-emerald-400">
                                            {{ strtoupper(substr($match->homeTeam->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-slate-200 line-clamp-1 max-w-[100px]">
                                        {{ $match->homeTeam->name }}
                                    </span>
                                </div>

                                <!-- Marcador & Estado -->
                                <div class="px-2 flex flex-col items-center shrink-0">
                                    @if($match->status === 'played')
                                        <div class="text-3xl font-black text-white font-mono tracking-tight">
                                            {{ $match->home_score }} - {{ $match->away_score }}
                                        </div>
                                        <span class="mt-1 inline-block text-[11px] font-black uppercase tracking-wider text-slate-400">
                                            FINAL
                                        </span>
                                    @elseif($match->status === 'live' || $match->is_timer_running)
                                        <div class="text-3xl font-black text-emerald-400 font-mono tracking-tight animate-pulse">
                                            {{ $match->home_score }} - {{ $match->away_score }}
                                        </div>
                                        <span class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-emerald-400">
                                            <span class="size-2 rounded-full bg-emerald-400 animate-ping"></span> EN VIVO
                                        </span>
                                    @else
                                        <div class="text-2xl font-bold text-slate-400 font-mono">
                                            VS
                                        </div>
                                        <span class="mt-1 inline-block text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                            PROGRAMADO
                                        </span>
                                    @endif
                                </div>

                                <!-- Equipo Visitante -->
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="size-14 rounded-full bg-slate-800 border-2 border-slate-700/80 flex items-center justify-center shadow-md group-hover:border-emerald-500/50 transition">
                                        <span class="font-extrabold text-base text-cyan-400">
                                            {{ strtoupper(substr($match->awayTeam->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-slate-200 line-clamp-1 max-w-[100px]">
                                        {{ $match->awayTeam->name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Botones Interactivos: En Vivo / Votar MVP -->
                            <div class="mt-5 pt-3 border-t border-slate-800/60 flex items-center justify-between gap-2">
                                <button type="button" 
                                        onclick="openMatchLiveModal({{ $match->id }}, '{{ addslashes($match->homeTeam->name) }}', '{{ addslashes($match->awayTeam->name) }}', {{ $match->home_score }}, {{ $match->away_score }}, '{{ $match->status }}', '{{ $match->formatted_clock }}')"
                                        class="w-full rounded-lg bg-slate-800 hover:bg-emerald-600 text-slate-200 hover:text-white px-3 py-2 text-xs font-bold transition flex items-center justify-center gap-1.5 shadow">
                                    <svg class="size-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Votar MVP / En Vivo
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 rounded-2xl border border-slate-800 bg-slate-900/40 p-8 text-center text-slate-400">
                            No hay encuentros programados en esta jornada.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- SECCIÓN: Noticias / Crónicas Destacadas (Fiel a la referencia) -->
            <div>
                <div class="flex items-center gap-2.5 mb-5">
                    <span class="text-emerald-400 text-2xl">📰</span>
                    <h2 class="text-2xl font-bold text-white tracking-tight">Noticias</h2>
                </div>

                @php
                    $latestChronicle = $chronicles->first();
                @endphp

                <div class="overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/70 p-5 shadow-xl shadow-black/20 flex flex-col md:flex-row gap-6 items-center backdrop-blur-md">
                    <div class="w-full md:w-5/12 shrink-0 overflow-hidden rounded-xl h-52 md:h-44 relative group">
                        <img src="{{ asset('images/soccer_news.jpg') }}" alt="Crónica del Partido" class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500">
                        <span class="absolute top-2.5 left-2.5 bg-slate-950/80 backdrop-blur-md px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider text-emerald-400 border border-emerald-500/30">
                            SIGET-SF
                        </span>
                    </div>
                    <div class="flex-1 space-y-2.5">
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-400">
                            ACTUALIDAD
                        </span>
                        <h3 class="text-xl font-extrabold text-white leading-snug">
                            {{ $latestChronicle?->chronicle_title ?? 'Resultados sorpresivos en la jornada de fin de semana' }}
                        </h3>
                        <p class="text-sm text-slate-300 line-clamp-3 leading-relaxed">
                            {{ $latestChronicle?->chronicle_body ?? 'Los equipos considerados favoritos tropezaron en sus respectivos encuentros, dejando la tabla de posiciones más apretada que nunca...' }}
                        </p>
                        @if($latestChronicle)
                            <div class="pt-1 flex items-center gap-3 text-xs text-slate-400 font-medium">
                                <span>📅 {{ $latestChronicle->match_date->format('d M, Y') }}</span>
                                <span>·</span>
                                <span class="text-emerald-400 font-semibold">Crónica Oficial IA</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: Equipos de la Competencia -->
            <div>
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-emerald-400 text-2xl">🛡️</span>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Equipos en Competencia</h2>
                    </div>
                    <a href="{{ route('teams.index') }}" class="text-sm font-semibold text-emerald-400 hover:text-emerald-300 transition">
                        Ver planteles completos →
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($teams as $team)
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4 text-center hover:border-emerald-500/40 transition backdrop-blur-md">
                            <div class="size-14 rounded-full bg-slate-800 border-2 border-slate-700 mx-auto flex items-center justify-center font-black text-lg text-emerald-400 mb-3 shadow">
                                {{ strtoupper(substr($team->name, 0, 2)) }}
                            </div>
                            <h4 class="font-extrabold text-white text-sm line-clamp-1">{{ $team->name }}</h4>
                            <p class="text-xs text-slate-400 mt-1 font-medium">DT: {{ $team->coach_name }}</p>
                            <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                                <span>{{ $team->players->count() }} Jugadores</span>
                                <a href="{{ route('teams.show', $team) }}" class="text-emerald-400 font-bold hover:underline">Ficha →</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- COLUMNA DERECHA: Tabla de Posiciones (Idéntica a la imagen de referencia) -->
        <div class="lg:col-span-4">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 shadow-2xl overflow-hidden backdrop-blur-md sticky top-20">
                
                <!-- Encabezado de la Tabla -->
                <div class="p-5 border-b border-slate-800/90 flex items-center justify-between bg-slate-900/90">
                    <div class="flex items-center gap-2.5">
                        <svg class="size-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <h3 class="text-lg font-extrabold text-white tracking-tight">Tabla de Posiciones</h3>
                    </div>
                </div>

                <!-- Cuerpo de la Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4 w-8 font-semibold">#</th>
                                <th class="py-3 px-2 font-semibold">Equipo</th>
                                <th class="py-3 px-3 text-center font-semibold">PJ</th>
                                <th class="py-3 px-3 text-center font-semibold">DG</th>
                                <th class="py-3 px-4 text-center font-black text-emerald-400">PTS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            @forelse($standings as $index => $standing)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-4 font-bold text-slate-400">{{ $index + 1 }}</td>
                                    <td class="py-3.5 px-2">
                                        <div class="flex items-center gap-2.5">
                                            <span class="size-7 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-black text-emerald-400 shrink-0">
                                                {{ strtoupper(substr($standing->team->name, 0, 2)) }}
                                            </span>
                                            <span class="font-bold text-slate-100 truncate max-w-[130px] sm:max-w-none">
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
                                    <td class="py-3.5 px-3 text-center font-mono text-xs {{ $dg >= 0 ? 'text-slate-300' : 'text-rose-400 font-bold' }}">
                                        {{ $dg > 0 ? '+'.$dg : $dg }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-black font-mono text-emerald-400 text-base">
                                        {{ $standing->points }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-500">Sin datos de clasificación.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer de la Tabla -->
                <div class="p-4 border-t border-slate-800 text-center bg-slate-950/40">
                    <a href="{{ route('standings.index') }}" class="text-xs font-extrabold uppercase tracking-widest text-emerald-400 hover:text-emerald-300 transition block py-1">
                        VER TABLA COMPLETA
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. MODAL INTERACTIVO: EN VIVO & VOTACIÓN MVP DE FANÁTICOS -->
    <div id="liveMatchModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl rounded-3xl border border-slate-700 bg-slate-900 p-6 sm:p-8 shadow-2xl shadow-emerald-950/40 animate-in fade-in zoom-in-95 duration-200">
            <!-- Botón Cerrar -->
            <button onclick="closeMatchLiveModal()" class="absolute top-5 right-5 text-slate-400 hover:text-white p-2 rounded-full hover:bg-slate-800 transition">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Título y Estado -->
            <div class="text-center mb-6">
                <span id="modalMatchStatus" class="inline-block rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-3 py-1 text-xs font-bold uppercase tracking-wider mb-2">
                    En Vivo
                </span>
                <div class="flex items-center justify-center gap-4 text-xl sm:text-2xl font-black text-white">
                    <span id="modalHomeTeam" class="text-emerald-400">Local</span>
                    <span id="modalScore" class="bg-slate-950 border border-slate-800 px-4 py-1.5 rounded-xl font-mono text-3xl">0 - 0</span>
                    <span id="modalAwayTeam" class="text-cyan-400">Visitante</span>
                </div>
                <p id="modalClock" class="mt-2 text-xs font-mono text-slate-400">Cronómetro Oficial: 00:00</p>
            </div>

            <!-- Votación de Aficionados: MVP -->
            <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 mb-6">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xl">⭐</span>
                    <h4 class="font-extrabold text-white text-base">Votación en Vivo: Jugador Más Valioso (MVP)</h4>
                </div>
                <p class="text-xs text-amber-200/80 mb-4">
                    Como aficionado, ¡elige a la figura de la cancha! Tu voto influye en la calificación oficial de rendimiento del jugador.
                </p>

                <form id="mvpVoteForm" onsubmit="submitMvpVote(event)" class="space-y-3">
                    @csrf
                    <input type="hidden" id="modalMatchId" name="match_id">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <select id="mvpPlayerSelect" name="player_id" required class="flex-1 rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-amber-500 focus:outline-none">
                            <option value="">Selecciona el jugador que merece el MVP...</option>
                        </select>
                        <button type="submit" id="btnSubmitMvp" class="rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black px-6 py-2.5 text-xs uppercase tracking-wider transition shadow-lg shadow-amber-500/20">
                            Votar MVP
                        </button>
                    </div>
                </form>

                <div id="mvpVoteFeedback" class="mt-3 hidden rounded-xl p-3 text-xs font-bold text-center"></div>

                <!-- Desglose de votos en vivo -->
                <div id="mvpLiveStatsContainer" class="mt-4 pt-3 border-t border-amber-500/20">
                    <div class="flex justify-between text-xs text-amber-300 font-bold mb-2">
                        <span>Votos de la Afición</span>
                        <span id="mvpTotalVotes">0 votos registrados</span>
                    </div>
                    <div id="mvpBreakdownList" class="space-y-2"></div>
                </div>
            </div>

            <!-- Timeline de Eventos Minuto a Minuto -->
            <div class="border-t border-slate-800 pt-5">
                <h5 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                    <span>⏱️</span> Bitácora Minuto a Minuto del Partido
                </h5>
                <div id="modalEventsList" class="space-y-2 max-h-48 overflow-y-auto pr-1 text-sm text-slate-300">
                    <p class="text-xs text-slate-500 italic">Cargando eventos...</p>
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

            // Cargar datos en vivo del partido y jugadores
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
                        <div class="flex items-center gap-3 bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
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
                        <div class="text-[11px] text-amber-200/90">${data.official_mvp.team} · Calificación: ${data.official_mvp.rating || '9.0'} ⭐</div>
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
                                <span class="text-amber-400">${item.percentage}% (${item.votes})</span>
                            </div>
                            <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-400 h-full rounded-full transition-all duration-500" style="width: ${item.percentage}%"></div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    breakdown.innerHTML = '<p class="text-xs text-slate-500 italic">Sé el primero en votar por el MVP.</p>';
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
