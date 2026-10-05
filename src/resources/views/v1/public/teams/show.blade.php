@extends('v1.layouts.public')

@section('title', 'Club ' . $team->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Migas de Pan & Acceso Rápido -->
    <div class="flex items-center justify-between text-xs">
        <a href="{{ route('teams.index') }}" class="font-bold text-[#057a55] hover:underline flex items-center gap-1">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver al Directorio de Equipos</span>
        </a>

        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->id() === $team->captain_id)
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-medium">Modo Oficial:</span>
                    <a href="{{ route('admin.teams.show', $team) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 text-emerald-400 text-xs font-bold hover:bg-slate-800 transition">
                        <span>Gestionar en Panel Admin</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            @endif
        @endauth
    </div>

    <!-- BANNER PRINCIPAL DEL CLUB -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="p-6 sm:p-10 flex flex-col md:flex-row items-center md:items-start gap-6 border-b border-slate-100">
            <!-- Escudo Gigante -->
            <div class="size-24 sm:size-28 rounded-3xl bg-emerald-50 border-2 border-emerald-200 text-[#057a55] font-black text-4xl flex items-center justify-center shadow-xs shrink-0">
                <x-reicon name="shield-check" class="size-12 text-[#057a55]" />
            </div>

            <!-- Datos del Club -->
            <div class="flex-1 text-center md:text-left space-y-2">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <x-reicon name="trophy" class="size-3.5 text-emerald-700 shrink-0" />
                        <span>{{ $team->tournament?->name ?? 'Torneo Oficial' }}</span>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px] bg-slate-100 text-slate-700">
                        {{ $team->status === 'active' || $team->status === 'approved' ? 'ACTIVO' : ($team->status === 'completed' ? 'FINALIZADO' : 'PENDIENTE') }}
                    </span>
                </div>

                <h1 class="font-display text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    {{ $team->name }}
                </h1>

                <div class="text-sm text-slate-500 flex flex-wrap items-center justify-center md:justify-start gap-4 pt-1">
                    <span>
                        <strong class="text-slate-800">Director Técnico:</strong> 
                        {{ $team->captain?->name ?? 'Sin asignar' }}
                    </span>
                    <span>&bull;</span>
                    <span>
                        <strong class="text-slate-800">Plantel:</strong> 
                        {{ $team->players->count() }} Jugadores Oficiales
                    </span>
                </div>
            </div>

            <!-- Acción de Navegación Rápida -->
            <div class="shrink-0 flex flex-col sm:flex-row gap-2.5">
                <a href="{{ route('matches.index', ['search' => $team->name]) }}" 
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-4 py-2.5 transition">
                    <span>Ver Calendario del Equipo</span>
                </a>
            </div>
        </div>

        <!-- Barra Inferior de Estadísticas Rápidas -->
        <div class="px-6 py-4 bg-slate-50/70 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-xs">
            <div class="p-2">
                <span class="block text-xl font-black text-slate-900 font-mono">{{ $team->standing?->points ?? 0 }}</span>
                <span class="text-[11px] text-slate-400 font-semibold uppercase">Puntos</span>
            </div>
            <div class="p-2">
                <span class="block text-xl font-black text-slate-900 font-mono">{{ $team->standing?->played ?? 0 }}</span>
                <span class="text-[11px] text-slate-400 font-semibold uppercase">Partidos Jugados</span>
            </div>
            <div class="p-2">
                <span class="block text-xl font-black text-emerald-700 font-mono">{{ $team->standing?->won ?? 0 }}</span>
                <span class="text-[11px] text-slate-400 font-semibold uppercase">Victorias</span>
            </div>
            <div class="p-2">
                <span class="block text-xl font-black text-slate-900 font-mono">{{ $team->standing?->goals_for ?? 0 }}</span>
                <span class="text-[11px] text-slate-400 font-semibold uppercase">Goles a Favor</span>
            </div>
        </div>
    </div>

    <!-- SECCIÓN: NÓMINA & PLANTEL OFICIAL -->
    <div class="space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
            <div>
                <h2 class="font-display text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Plantel Oficial de Jugadores
                </h2>
                <p class="text-xs text-slate-500">Nómina registrada con perfiles cromográficos y estadísticas individuales.</p>
            </div>
            <span class="text-xs font-bold text-slate-400 font-mono">{{ $team->players->count() }} futbolistas</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($team->players as $player)
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs hover:shadow-md transition group flex flex-col justify-between">
                    <div>
                        <!-- Encabezado Tarjeta Jugador -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="size-8 rounded-lg bg-emerald-50 text-[#057a55] font-mono font-black text-sm flex items-center justify-center border border-emerald-200">
                                #{{ $player->jersey_number }}
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                {{ ucfirst($player->position ?? 'Jugador') }}
                            </span>
                        </div>

                        <!-- Nombre e info -->
                        <div class="mt-3.5 space-y-1">
                            <h3 class="font-display font-bold text-sm text-slate-900 group-hover:text-[#057a55] transition line-clamp-1">
                                {{ $player->name }}
                            </h3>
                            <p class="text-[11px] text-slate-500 flex items-center gap-2">
                                <span>Pierna: {{ $player->preferred_foot === 'right' ? 'Diestro' : ($player->preferred_foot === 'left' ? 'Zurdo' : 'Ambidiestro') }}</span>
                                @if($player->profile?->scouting_rating)
                                    <span>&bull;</span>
                                    <span class="font-bold text-amber-700 inline-flex items-center gap-1">
                                        <x-reicon name="star" variant="filled" class="size-3 text-amber-500" />
                                        <span>{{ number_format($player->profile->scouting_rating, 1) }}</span>
                                    </span>
                                @endif
                            </p>
                            @if($player->profile?->bio)
                                <p class="text-[11px] text-slate-400 line-clamp-2 italic pt-1">
                                    "{{ $player->profile->bio }}"
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Enlaces al Cromo y Carnet -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                        <a href="{{ route('players.cromo', $player) }}" target="_blank" 
                           class="flex-1 rounded-lg bg-emerald-50 hover:bg-[#057a55] text-emerald-800 hover:text-white py-1.5 text-center text-[11px] font-bold transition">
                            Cromo Digital
                        </a>
                        <a href="{{ route('players.carnet', $player) }}" target="_blank" 
                           class="rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 px-2.5 py-1.5 text-center text-[11px] font-bold transition" title="Carnet QR Oficial">
                            QR
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    No hay jugadores inscritos en este plantel actualmente.
                </div>
            @endforelse
        </div>
    </div>

    <!-- SECCIÓN: PRÓXIMOS PARTIDOS DEL EQUIPO -->
    @if(isset($recentMatches) && $recentMatches->isNotEmpty())
        <div class="space-y-4 pt-4">
            <h2 class="font-display text-xl font-bold text-slate-900 tracking-tight">
                Encuentros Recientes y Próximos
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($recentMatches as $m)
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="size-2 rounded-full {{ $m->status === 'played' ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                            <div>
                                <div class="font-bold text-slate-900">
                                    {{ $m->homeTeam?->name }} vs {{ $m->awayTeam?->name }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $m->match_date ? $m->match_date->format('d/m/Y - H:i') : 'Fecha pendiente' }} • {{ $m->venue?->name ?? 'Estadio' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            @if($m->status === 'played')
                                <span class="font-mono font-black text-slate-900 text-sm bg-slate-100 px-2.5 py-1 rounded-lg">
                                    {{ $m->home_score }} - {{ $m->away_score }}
                                </span>
                            @else
                                <span class="text-[11px] font-bold text-slate-500 uppercase">Programado</span>
                            @endif
                            <a href="{{ route('matches.show', $m) }}" class="text-[#057a55] font-bold hover:underline">
                                Ficha
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
