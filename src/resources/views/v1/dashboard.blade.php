@extends('v1.layouts.app')

@section('title', 'Panel Principal')
@section('header_title', 'Panel Principal')

@section('header_badge')
<div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
    <span>Temporada Oficial</span>
</div>
@endsection

@section('content')
<div class="space-y-6">

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

</div>
@endsection
