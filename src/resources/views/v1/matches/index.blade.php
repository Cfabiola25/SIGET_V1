@extends('v1.layouts.app')

@section('title', 'Partidos & Calendario')
@section('header_title', 'Matches Management')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
                Competition Matches & Results
            </h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1">
                Official fixtures, live scoring console, results validation and referee reports.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('matches.schedule') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs md:text-sm font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                <svg class="size-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Scheduler View</span>
            </a>
            <a href="{{ route('matches.live') }}" class="px-4 py-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs md:text-sm font-bold hover:bg-rose-100 transition flex items-center gap-1.5">
                <span class="size-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Partidos En Vivo</span>
            </a>
            <a href="{{ route('matches.schedule') }}" class="px-4 py-2.5 rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs md:text-sm font-bold shadow-xs transition">
                + Programar Partido
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5">
        <form method="GET" action="{{ route('matches.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Buscar partido</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Torneo o equipo..." 
                       class="w-full text-xs md:text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600 transition">
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deporte</label>
                <select name="sport_type" class="w-full text-xs md:text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="">Todos</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->name }}" @selected(($filters['sport_type'] ?? '') === $sport->name)>{{ $sport->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Estado</label>
                <select name="status" class="w-full text-xs md:text-sm px-3.5 py-2 bg-white border border-slate-300 rounded-lg focus:border-emerald-600">
                    <option value="">Todos</option>
                    <option value="scheduled" @selected(($filters['status'] ?? '') === 'scheduled')>Programado</option>
                    <option value="played" @selected(($filters['status'] ?? '') === 'played')>Jugado</option>
                    <option value="suspended" @selected(($filters['status'] ?? '') === 'suspended')>Suspendido</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Fecha</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full text-xs md:text-sm px-3 py-2 bg-white border border-slate-300 rounded-lg">
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-3 bg-sky-100 hover:bg-sky-200 text-sky-900 text-xs md:text-sm font-bold rounded-lg transition text-center cursor-pointer shadow-2xs">
                    Filtrar
                </button>
                @if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['sport_type']))
                    <a href="{{ route('matches.index') }}" class="p-2 text-slate-400 hover:text-slate-700" title="Limpiar">&times;</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Match Cards List -->
    <div class="space-y-4">
        @forelse ($matches as $match)
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-5 hover:border-slate-300 transition">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">
                            {{ $match->tournament->name }}
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="text-xs text-slate-500 font-medium">
                            {{ $match->match_date->format('d M Y · H:i') }}
                        </span>
                        @if ($match->venue)
                            <span class="text-slate-300">•</span>
                            <span class="text-xs text-slate-500 font-medium">
                                📍 {{ $match->venue->name }} (Cancha {{ $match->field_number ?? 1 }})
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <h3 class="text-lg md:text-xl font-bold text-slate-900">
                            {{ $match->homeTeam->name }}
                        </h3>
                        <span class="text-slate-400 font-normal text-sm">vs</span>
                        <h3 class="text-lg md:text-xl font-bold text-slate-900">
                            {{ $match->awayTeam->name }}
                        </h3>
                    </div>

                    @if ($match->referee)
                        <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                            <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span>Árbitro Oficial: {{ $match->referee->name }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-6 self-end md:self-center">
                    <div class="text-center min-w-24">
                        <div class="text-2xl md:text-3xl font-black text-slate-900">
                            {{ $match->status === 'played' ? ($match->home_score ?? 0) . ' - ' . ($match->away_score ?? 0) : ($match->status === 'in_progress' ? '0 - 0' : '—') }}
                        </div>
                        <div class="mt-1">
                            @if ($match->status === 'played')
                                <span class="inline-block bg-[#d1fae5] text-[#065f46] text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase">Final</span>
                            @elseif ($match->status === 'in_progress')
                                <span class="inline-block bg-sky-100 text-sky-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase">En Juego</span>
                            @else
                                <span class="inline-block bg-slate-100 text-slate-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase">Programado</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('matches.show', $match) }}" class="px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                            Detalles
                        </a>
                        <a href="{{ route('matches.console', $match) }}" class="px-3.5 py-2 rounded-lg bg-[#057a55] hover:bg-[#046c4b] text-white text-xs font-bold transition">
                            Consola
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center text-slate-500">
                No hay partidos que coincidan con los filtros seleccionados.
            </div>
        @endforelse
    </div>

    @if ($matches instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6">
            {{ $matches->links('pagination::tailwind') }}
        </div>
    @endif

</div>
@endsection
