@extends('v1.layouts.app')

@section('title', 'Panel Arbitral | Mis Asignaciones')
@section('header_title', 'Portal del Árbitro')

@section('content')
<div class="space-y-8">
    
    <!-- BANNER DE PERFIL DEL ÁRBITRO -->
    <div class="bg-gradient-to-r from-[#182232] to-[#223147] rounded-3xl p-6 sm:p-8 text-white shadow-lg border border-slate-700/60 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 size-60 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="size-16 sm:size-20 rounded-2xl bg-emerald-600 border-2 border-emerald-400/40 flex items-center justify-center font-display font-black text-2xl text-white shadow-md shrink-0">
                    {{ strtoupper(substr($referee->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="font-display text-2xl sm:text-3xl font-extrabold tracking-tight text-white">{{ $referee->name }}</h1>
                        <span class="rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-0.5 text-xs font-bold uppercase tracking-wider">
                            {{ $referee->license_number ?? 'Colegiado Oficial' }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1">Cuerpo Arbitral Oficial &bull; Plataforma SIGET-SF</p>
                    <div class="flex items-center gap-4 mt-3 text-xs text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <span class="text-amber-400">⭐</span>
                            <strong>{{ number_format($referee->rating_average, 2) }}</strong> / 5.00 Calificación Promedio
                        </span>
                        <span>&bull;</span>
                        <span class="flex items-center gap-1.5">
                            <span class="text-emerald-400">📋</span>
                            <strong>{{ $referee->total_matches_officiated }}</strong> Partidos Dirigidos
                        </span>
                    </div>
                </div>
            </div>

            <!-- Acceso Rápido al Portal Público -->
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 text-white px-4 py-2.5 text-xs font-bold transition border border-white/20">
                    <svg class="size-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Ver Portal Público</span>
                </a>
            </div>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN RÁPIDO -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex items-center gap-4">
            <div class="size-12 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center text-xl">
                📅
            </div>
            <div>
                <div class="text-2xl font-black font-mono text-slate-900">{{ $upcomingMatches->count() }}</div>
                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Próximos Asignados</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex items-center gap-4">
            <div class="size-12 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center text-xl">
                ✓
            </div>
            <div>
                <div class="text-2xl font-black font-mono text-slate-900">{{ $pastMatches->count() }}</div>
                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Partidos Concluidos</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex items-center gap-4">
            <div class="size-12 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-700 flex items-center justify-center text-xl">
                ⭐
            </div>
            <div>
                <div class="text-2xl font-black font-mono text-slate-900">{{ $evaluations->count() }}</div>
                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Evaluaciones Recibidas</div>
            </div>
        </div>
    </div>

    <!-- 1. PRÓXIMOS ENCUENTROS ASIGNADOS (HERRAMIENTAS PARA EL DÍA DEL PARTIDO) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">📋</span>
                <div>
                    <h2 class="font-display text-xl font-extrabold text-slate-900 tracking-tight">Tus Próximas Asignaciones</h2>
                    <p class="text-xs text-slate-500">Partidos oficiales programados bajo tu dirección arbitral</p>
                </div>
            </div>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                {{ $upcomingMatches->count() }} programados
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse($upcomingMatches as $match)
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <!-- Cabecera del Partido -->
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 text-xs">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-bold uppercase tracking-wider text-[11px]">
                                {{ $match->tournament?->name ?? 'Torneo Oficial' }} &bull; Jornada {{ $match->round_number }}
                            </span>
                            <span class="text-slate-500 font-medium flex items-center gap-1">
                                <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $match->match_date ? $match->match_date->format('d M, Y &bull; H:i') : 'Fecha por confirmar' }}
                            </span>
                        </div>

                        <!-- Enfrentamiento -->
                        <div class="grid grid-cols-7 items-center gap-2 my-4 text-center">
                            <!-- Local -->
                            <div class="col-span-3 flex flex-col items-center">
                                <div class="size-13 rounded-full bg-slate-100 border border-slate-200 text-slate-800 font-bold flex items-center justify-center text-sm shadow-2xs group-hover:border-emerald-500/40 transition">
                                    {{ strtoupper(substr($match->homeTeam?->name ?? 'LOC', 0, 2)) }}
                                </div>
                                <span class="mt-2 text-xs font-bold text-slate-800 line-clamp-1" title="{{ $match->homeTeam?->name }}">
                                    {{ $match->homeTeam?->name }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-semibold uppercase">Local</span>
                            </div>

                            <div class="col-span-1 font-display text-lg font-bold text-slate-400">
                                VS
                            </div>

                            <!-- Visitante -->
                            <div class="col-span-3 flex flex-col items-center">
                                <div class="size-13 rounded-full bg-slate-100 border border-slate-200 text-slate-800 font-bold flex items-center justify-center text-sm shadow-2xs group-hover:border-emerald-500/40 transition">
                                    {{ strtoupper(substr($match->awayTeam?->name ?? 'VIS', 0, 2)) }}
                                </div>
                                <span class="mt-2 text-xs font-bold text-slate-800 line-clamp-1" title="{{ $match->awayTeam?->name }}">
                                    {{ $match->awayTeam?->name }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-semibold uppercase">Visitante</span>
                            </div>
                        </div>

                        <!-- Info de Sede y Cancha -->
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-600 flex items-center justify-between mb-4">
                            <span class="flex items-center gap-1.5">
                                <span>📍</span>
                                <strong>{{ $match->venue?->name ?? 'Estadio Central' }}</strong>
                            </span>
                            <span class="text-slate-400">Cancha #{{ $match->field_number ?? 1 }}</span>
                        </div>
                    </div>

                    <!-- Botones de Acción Directa para el Árbitro -->
                    <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2">
                        <!-- Scanner QR -->
                        <a href="{{ route('referees.matches.scan.console', $match) }}" 
                           class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition" 
                           title="Escanear carnets y validar nómina de jugadores">
                            <span>📷</span>
                            <span>Validar QR</span>
                        </a>

                        <!-- Consola Match Day -->
                        <a href="{{ route('matches.console', $match) }}" 
                           class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-[#057a55] hover:bg-[#046c4b] text-white font-bold text-xs transition shadow-xs" 
                           title="Llevar cronómetro, goles y tarjetas">
                            <span>⏱️</span>
                            <span>Consola Partido</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-xs">
                    <span class="text-3xl block mb-2">⚽</span>
                    No tienes partidos programados en este momento. Las nuevas designaciones aparecerán automáticamente aquí.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. HISTORIAL DE PARTIDOS DIRIGIDOS & ACTAS -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">📑</span>
                <div>
                    <h2 class="font-display text-xl font-extrabold text-slate-900 tracking-tight">Historial de Partidos Dirigidos</h2>
                    <p class="text-xs text-slate-500">Actas arbitrales, marcadores oficiales y cierres de partido</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Fecha</th>
                            <th class="py-3 px-4">Torneo</th>
                            <th class="py-3 px-4">Partido</th>
                            <th class="py-3 px-4 text-center">Marcador</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($pastMatches as $match)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 text-slate-600 font-mono">
                                    {{ $match->match_date ? $match->match_date->format('d/m/Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    {{ $match->tournament?->name ?? 'Oficial' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-800">
                                    <strong>{{ $match->homeTeam?->name }}</strong> vs <strong>{{ $match->awayTeam?->name }}</strong>
                                </td>
                                <td class="py-3.5 px-4 text-center font-display font-black text-slate-900 text-sm">
                                    {{ $match->home_score }} - {{ $match->away_score }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700">
                                        Finalizado
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <a href="{{ route('matches.report', $match) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-800 font-bold hover:underline">
                                        <span>Ver Acta Oficial &rarr;</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 text-xs">Aún no hay partidos finalizados registrados bajo tu arbitraje.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. EVALUACIONES RECIBIDAS DE LOS DIRECTORES TÉCNICOS -->
    <div class="space-y-4">
        <div class="flex items-center gap-2.5">
            <span class="text-2xl">⭐</span>
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900 tracking-tight">Evaluaciones de Desempeño</h2>
                <p class="text-xs text-slate-500">Calificaciones emitidas por los cuerpos técnicos tras los encuentros</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($evaluations as $eval)
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs text-xs space-y-2.5">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div>
                            <span class="font-bold text-slate-800">{{ $eval->team?->name ?? 'Equipo Oficial' }}</span>
                            <span class="text-slate-400 block text-[10px]">{{ $eval->created_at ? $eval->created_at->format('d M, Y') : '' }}</span>
                        </div>
                        <div class="flex items-center gap-1 bg-amber-50 text-amber-900 font-bold px-2.5 py-1 rounded-lg border border-amber-200">
                            <span>⭐</span>
                            <span>{{ $eval->score_overall }} / 5</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-[11px] text-slate-600 bg-slate-50 p-2 rounded-xl">
                        <div>
                            <div class="font-bold text-slate-900">{{ $eval->score_rule_enforcement }}/5</div>
                            <div class="text-[10px] text-slate-400">Reglamento</div>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900">{{ $eval->score_fairness }}/5</div>
                            <div class="text-[10px] text-slate-400">Imparcialidad</div>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900">{{ $eval->score_punctuality }}/5</div>
                            <div class="text-[10px] text-slate-400">Puntualidad</div>
                        </div>
                    </div>
                    @if($eval->comments)
                        <p class="text-slate-600 italic bg-slate-50/70 p-2.5 rounded-lg border border-slate-100">
                            "{{ $eval->comments }}"
                        </p>
                    @endif
                </div>
            @empty
                <div class="col-span-2 bg-white rounded-2xl border border-slate-200 p-6 text-center text-slate-400 text-xs">
                    Las evaluaciones de los directores técnicos aparecerán aquí al concluir los partidos.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
