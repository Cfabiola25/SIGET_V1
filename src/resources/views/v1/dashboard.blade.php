@extends('v1.layouts.app')

@section('title', 'Overview')
@section('header_title', 'Overview')

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
                Bienvenido al centro de operaciones deportivas de SIGET-SF.
            </p>
        </div>
        <span class="self-start sm:self-auto rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 uppercase tracking-wider">
            {{ auth()->user()->role }}
        </span>
    </div>

    <!-- Quick access cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Torneos Registrados</span>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $tournamentsCount ?? 1 }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Equipos en Competencia</span>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $teamsCount ?? 4 }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Partidos Programados</span>
            <div class="mt-3 text-3xl font-black text-slate-900">6</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Canchas Habilitadas</span>
            <div class="mt-3 text-3xl font-black text-slate-900">2</div>
        </div>
    </div>

    <!-- Actions & Shortcuts -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Gestión de Jugadores</h3>
                <p class="text-xs text-slate-500 mt-1">Administra fichas, posiciones, dorsales y estado de sanciones.</p>
            </div>
            <a href="{{ route('players.index') }}" class="mt-5 inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800">
                <span>Ver Jugadores</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Crear Torneo</h3>
                <p class="text-xs text-slate-500 mt-1">Configura fases de grupos, playoffs y sistemas de puntuación.</p>
            </div>
            <a href="{{ route('tournaments.create') }}" class="mt-5 inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-800">
                <span>Crear Torneo</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

</div>
@endsection
