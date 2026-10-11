@extends('v1.layouts.public')

@section('title', 'Tabla de Posiciones Oficial')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Principal -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 mb-2 border border-emerald-300">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                <span>Clasificación en Tiempo Real</span>
            </div>
            <h1 class="font-display text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                Tabla de Posiciones
            </h1>
            <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                Puntajes oficiales, diferencia de gol, rendimiento y zonas de clasificación reglamentarias.
            </p>
        </div>

        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <div class="shrink-0">
                    <a href="{{ route('admin.standings.index') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 shadow-sm transition">
                        <svg class="size-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Panel Admin de Posiciones</span>
                    </a>
                </div>
            @endif
        @endauth
    </div>

    <!-- Selector de Torneos -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Torneo:</span>
            <div class="flex items-center gap-2 overflow-x-auto">
                @forelse($tournaments as $t)
                    <a href="{{ route('standings.index', ['tournament_id' => $t->id]) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap inline-flex items-center gap-1.5 {{ ($selectedTournamentId ?? '') == $t->id ? 'bg-[#057a55] text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200' }}">
                        <x-reicon name="trophy" class="size-3.5 {{ ($selectedTournamentId ?? '') == $t->id ? 'text-white' : 'text-emerald-600' }} shrink-0" />
                        <span>{{ $t->name }}</span>
                        @if($t->is_featured)
                            <x-reicon name="star" variant="filled" class="size-3 text-amber-300 ml-0.5 shrink-0" />
                        @endif
                    </a>
                @empty
                    <span class="text-xs text-slate-400 italic px-2">No hay torneos activos aún</span>
                @endforelse
            </div>
        </div>

        <div class="text-xs font-semibold text-slate-400">
            Reglamento: Victoria (3 pts) • Empate (1 pt) • Derrota (0 pts)
        </div>
    </div>

    <!-- Tabla Oficial de Posiciones -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-black uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 pl-6 pr-3 w-12 text-center">Pos</th>
                        <th class="py-3.5 px-4 min-w-[200px]">Club / Equipo</th>
                        <th class="py-3.5 px-3 text-center font-black text-slate-900 bg-emerald-50/50">PTS</th>
                        <th class="py-3.5 px-3 text-center">PJ</th>
                        <th class="py-3.5 px-3 text-center">PG</th>
                        <th class="py-3.5 px-3 text-center">PE</th>
                        <th class="py-3.5 px-3 text-center">PP</th>
                        <th class="py-3.5 px-3 text-center">GF</th>
                        <th class="py-3.5 px-3 text-center">GC</th>
                        <th class="py-3.5 px-3 text-center">DG</th>
                        <th class="py-3.5 pr-6 pl-4 text-right">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($standings as $index => $s)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <!-- Posición con indicador de zona -->
                            <td class="py-4 pl-6 pr-3 text-center">
                                <span class="inline-flex size-6 items-center justify-center rounded-lg font-black text-xs font-mono
                                    {{ $index === 0 ? 'bg-amber-100 text-amber-900 border border-amber-300 font-bold' : ($index < 4 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'text-slate-500') }}">
                                    {{ $index + 1 }}
                                </span>
                            </td>

                            <!-- Equipo -->
                            <td class="py-4 px-4 font-bold text-slate-900">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-xl bg-slate-100 border border-slate-200 text-[#057a55] font-black text-xs flex items-center justify-center">
                                        {{ strtoupper(substr($s->team?->name ?? 'EQ', 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('teams.show', $s->team) }}" class="text-slate-900 hover:text-[#057a55] transition hover:underline">
                                            {{ $s->team?->name ?? 'Equipo' }}
                                        </a>
                                        <div class="text-[10px] text-slate-400 font-normal">
                                            DT: {{ $s->team?->captain?->name ?? 'Oficial' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Puntos -->
                            <td class="py-4 px-3 text-center font-black text-sm text-[#057a55] bg-emerald-50/40 font-mono">
                                {{ $s->points }}
                            </td>

                            <!-- PJ -->
                            <td class="py-4 px-3 text-center font-medium text-slate-600 font-mono">{{ $s->played }}</td>
                            <!-- PG -->
                            <td class="py-4 px-3 text-center font-medium text-slate-600 font-mono text-emerald-700 font-bold">{{ $s->won }}</td>
                            <!-- PE -->
                            <td class="py-4 px-3 text-center font-medium text-slate-500 font-mono">{{ $s->drawn }}</td>
                            <!-- PP -->
                            <td class="py-4 px-3 text-center font-medium text-slate-500 font-mono text-rose-600">{{ $s->lost }}</td>
                            <!-- GF -->
                            <td class="py-4 px-3 text-center font-medium text-slate-600 font-mono">{{ $s->goals_for }}</td>
                            <!-- GC -->
                            <td class="py-4 px-3 text-center font-medium text-slate-600 font-mono">{{ $s->goals_against }}</td>
                            <!-- DG -->
                            <td class="py-4 px-3 text-center font-bold font-mono {{ ($s->goals_for - $s->goals_against) > 0 ? 'text-emerald-700' : (($s->goals_for - $s->goals_against) < 0 ? 'text-rose-600' : 'text-slate-500') }}">
                                {{ ($s->goals_for - $s->goals_against) > 0 ? '+' : '' }}{{ $s->goals_for - $s->goals_against }}
                            </td>

                            <!-- Enlace a Club -->
                            <td class="py-4 pr-6 pl-4 text-right">
                                <a href="{{ route('teams.show', $s->team) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500 hover:text-[#057a55] group-hover:translate-x-0.5 transition">
                                    <span>Ver Club</span>
                                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-slate-400 text-xs">
                                No hay posiciones registradas para este torneo aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Leyenda Inferior -->
        <div class="p-4 bg-slate-50/70 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5">
                    <span class="size-2.5 rounded-full bg-amber-400"></span>
                    <span>Líder / Campeón</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="size-2.5 rounded-full bg-emerald-500"></span>
                    <span>Zona de Clasificación</span>
                </span>
            </div>
            <div class="text-[11px]">
                Desempate oficial: 1° Puntos, 2° Gol Diferencia, 3° Goles a Favor, 4° Enfrentamiento Directo.
            </div>
        </div>
    </div>

</div>
@endsection
