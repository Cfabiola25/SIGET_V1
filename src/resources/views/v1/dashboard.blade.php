@extends('v1.layouts.app')

@section('title', auth()->user()->isPlayer() ? 'Panel del Jugador' : 'Panel Principal')
@section('header_title', auth()->user()->isPlayer() ? 'Portal del Jugador' : 'Panel Principal')

@section('header_badge')
<div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
    <span>Temporada Oficial</span>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @if(auth()->user()->isPlayer())
        <!-- ================= PANEL EXCLUSIVO DEL JUGADOR ================= -->
        <!-- Header del Jugador -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="size-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-2xl shadow-md border-2 border-emerald-400/40">
                    {{ $player?->jersey_number ? '#'.$player->jersey_number : strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-700">
                        <span>Jugador Oficial</span>
                        @if($team)
                            <span class="text-slate-300">•</span>
                            <span>{{ $team->tournament?->name ?? 'Torneo Federativo' }}</span>
                        @endif
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                        {{ auth()->user()->name }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Club: <span class="font-bold text-slate-800">{{ $team?->name ?? 'Sin equipo vinculado' }}</span>
                        @if($player?->profile)
                            • Posición: <span class="font-semibold text-slate-700">{{ $player->profile->position_label ?? 'Futbolista' }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <span class="self-start md:self-auto rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold px-3.5 py-1 uppercase tracking-wider border border-emerald-300">
                Ficha Habilitada
            </span>
        </div>

        @if(!$player)
            <!-- Aviso si aún no está vinculado a una ficha de jugador -->
            <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-6 shadow-2xs">
                <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
                    <x-reicon name="alert-triangle" class="size-5 text-amber-600" />
                    <span>Ficha de Jugador en Proceso de Vinculación</span>
                </div>
                <p class="text-xs text-amber-800 mt-2 leading-relaxed">
                    Tu Director Técnico (DT) o el Administrador del Torneo te asignará el número de dorsal y tu ficha deportiva oficial para habilitar tu Carnet Digital con código QR.
                </p>
            </div>
        @else
            <!-- Tarjetas de Acceso Rápido del Jugador -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Carnet Digital QR -->
                <div class="bg-gradient-to-br from-slate-900 via-slate-950 to-emerald-950 rounded-2xl border border-emerald-500/30 p-6 shadow-xl text-white flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-emerald-500/20">
                            <span class="text-[11px] font-black tracking-widest text-emerald-400 uppercase">IDENTIFICACIÓN</span>
                            <x-reicon name="mobile" class="size-5 text-emerald-400" />
                        </div>
                        <h3 class="text-lg font-black mt-3">Mi Carnet Digital QR</h3>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                            Presenta tu carnet móvil al árbitro en la mesa de control para verificar tu identidad y dorsal antes de ingresar a la cancha.
                        </p>
                    </div>
                    <a href="{{ route('players.carnet', $player) }}" class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs py-2.5 shadow-md transition transform active:scale-95">
                        <x-reicon name="mobile" class="size-4" />
                        <span>Abrir Mi Carnet QR</span>
                    </a>
                </div>

                <!-- Cromo / Ficha Deportiva -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">ESTADÍSTICAS</span>
                            <x-reicon name="shield-check" class="size-5 text-[#1e3a8a]" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mt-3">Mi Cromo Oficial</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Consulta tu hoja de vida deportiva: goles anotados, tarjetas, partidos disputados y premios MVP de la temporada.
                        </p>
                    </div>
                    <a href="{{ route('players.cromo', $player) }}" class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#1e3a8a] hover:bg-[#0284c7] text-white font-bold text-xs py-2.5 shadow-sm transition">
                        <span>Ver Ficha Deportiva</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <!-- Ficha Médica & EPS -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">SALUD & COBERTURA</span>
                            <span class="text-base">🩺</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mt-3">Ficha Médica</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Grupo sanguíneo: <span class="font-bold text-slate-800">{{ $player->medicalRecord?->blood_type ?? 'O+' }}</span> • EPS: <span class="font-bold text-slate-800">{{ $player->medicalRecord?->health_provider ?? 'Registrada' }}</span>.
                        </p>
                    </div>
                    <a href="{{ route('players.medical.edit', $player) }}" class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs py-2.5 shadow-2xs transition">
                        <span>Actualizar Datos Médicos</span>
                    </a>
                </div>
            </div>

            <!-- Próximos Partidos de Mi Equipo -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <svg class="size-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Partidos de Mi Equipo ({{ $team?->name ?? 'Mi Club' }})</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Consulta fecha, hora, cancha asignada y árbitro oficial de tus próximos encuentros.</p>
                    </div>
                    <a href="{{ route('matches.index') }}" class="text-xs font-bold text-[#1e3a8a] hover:underline">
                        Ver Calendario Completo &rarr;
                    </a>
                </div>

                @if($upcomingMatches->isEmpty())
                    <p class="text-xs text-slate-400 py-4 text-center">No hay partidos próximos programados en este momento para tu equipo.</p>
                @else
                    <div class="space-y-3">
                        @foreach($upcomingMatches as $match)
                            @php
                                $isHome = $team && $match->home_team_id === $team->id;
                                $opponent = $isHome ? $match->awayTeam : $match->homeTeam;
                            @endphp
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $isHome ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $isHome ? 'LOCAL' : 'VISITANTE' }}
                                        </span>
                                        <span class="font-bold text-slate-800">
                                            {{ $match->match_date?->format('d/m/Y - H:i') ?? 'Fecha por definir' }} hrs
                                        </span>
                                    </div>
                                    <div class="text-sm font-bold text-slate-900 mt-1">
                                        {{ $match->homeTeam?->name }} <span class="text-slate-400 font-normal">vs</span> {{ $match->awayTeam?->name }}
                                    </div>
                                    <div class="text-slate-500 mt-0.5">
                                        Rival: <span class="font-bold text-slate-800">{{ $opponent?->name ?? 'Rival' }}</span>
                                    </div>
                                </div>

                                <div class="space-y-1 sm:text-right">
                                    <div class="text-slate-700 font-semibold flex sm:justify-end items-center gap-1">
                                        <x-reicon name="pin" class="size-3 text-slate-400" />
                                        <span>{{ $match->venue?->name ?? 'Sede por asignar' }}</span>
                                        <span class="font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200 text-[10px]">Cancha #{{ $match->field_number ?? 1 }}</span>
                                    </div>
                                    <div class="text-slate-500 text-[11px]">
                                        Árbitro: <span class="font-bold text-slate-700">{{ $match->referee?->name ?? 'Oficial por designar' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Tabla de Posiciones de Mi Torneo -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Tabla de Clasificación Oficial</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Puntos, diferencia de goles y rendimiento de los clubes de tu grupo.</p>
                </div>
                <a href="{{ route('standings.index') }}" class="px-4 py-2 rounded-xl bg-[#1e3a8a] text-white text-xs font-bold hover:bg-[#0284c7] transition shadow-2xs">
                    Ver Posiciones &rarr;
                </a>
            </div>
        @endif

    @else
        <!-- ================= PANEL PRINCIPAL ADMINISTRATIVO ================= -->
        <!-- Header greeting -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                    Hola, {{ auth()->user()->name }}
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Bienvenido al centro de operaciones deportivas de SIGET.
                </p>
            </div>
            <span class="self-start sm:self-auto rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 uppercase tracking-wider">
                {{ auth()->user()->role === 'super_admin' ? 'Super Administrador' : (auth()->user()->role === 'admin' ? 'Director de Torneo' : (auth()->user()->role === 'referee' ? 'Árbitro Oficial' : (auth()->user()->role === 'captain' ? 'Director Técnico' : ucfirst(auth()->user()->role)))) }}
            </span>
        </div>

        <!-- Quick access cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Torneos Registrados</span>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $tournamentsCount ?? 0 }}</div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Equipos en Competencia</span>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $teamsCount ?? 0 }}</div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Partidos Programados</span>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $matchesCount ?? 0 }}</div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Canchas Habilitadas</span>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $venuesCount ?? 0 }}</div>
            </div>
        </div>

        @if(isset($teams) && $teams->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 mb-3">
                    <x-reicon name="shield-check" class="size-4 text-emerald-600" />
                    <span>Mis Equipos Gestionados</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($teams as $team)
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ $team->name }}</h4>
                                <p class="text-xs text-slate-400">{{ $team->tournament?->name ?? 'Sin torneo asignado' }}</p>
                            </div>
                            <a href="{{ route('dt.dashboard', $team) }}" class="text-xs font-bold text-[#1e3a8a] hover:underline">
                                Panel DT &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Actions & Shortcuts -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if(auth()->user()?->isSuperAdmin() || (auth()->user()?->isAdmin() && auth()->user()?->hasManagedTournaments()))
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Agendador de Partidos</h3>
                        <p class="text-xs text-slate-500 mt-1">Organiza horarios, árbitros y canchas de cada fecha.</p>
                    </div>
                    <a href="{{ route('matches.schedule') }}" class="mt-5 inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800">
                        <span>Ir al Agendador</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Calendario de Partidos</h3>
                        <p class="text-xs text-slate-500 mt-1">Consulta fechas, resultados y horarios oficiales.</p>
                    </div>
                    <a href="{{ route('matches.index') }}" class="mt-5 inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800">
                        <span>Ver Calendario</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Tabla de Posiciones</h3>
                    <p class="text-xs text-slate-500 mt-1">Puntos, diferencia de gol y estadísticas en tiempo real.</p>
                </div>
                <a href="{{ route('standings.index') }}" class="mt-5 inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800">
                    <span>Ver Clasificación</span>
                    <span>&rarr;</span>
                </a>
            </div>

            @if(auth()->user()?->isSuperAdmin())
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Crear Torneo</h3>
                        <p class="text-xs text-slate-500 mt-1">Configura fases de grupos, playoffs y modalidades deportivas.</p>
                    </div>
                    <a href="{{ route('tournaments.create') }}" class="mt-5 inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800">
                        <span>Crear Torneo</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Radar de Talentos</h3>
                        <p class="text-xs text-slate-500 mt-1">Explora jugadores destacados y estadísticas individuales.</p>
                    </div>
                    <a href="{{ route('scouting.index') }}" class="mt-5 inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800">
                        <span>Explorar Scouting</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
