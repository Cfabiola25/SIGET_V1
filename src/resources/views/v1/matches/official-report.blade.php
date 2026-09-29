@extends('v1.layouts.app')

@section('title', 'Acta Oficial de Partido')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Action Toolbar (hidden on print) -->
        <div class="print:hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div>
                <a href="{{ route('matches.show', $match) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[#057a55] hover:underline transition">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver al Detalle del Partido
                </a>
                <h1 class="text-xl font-bold text-slate-900 mt-1">Acta Oficial Digital de Partido</h1>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('tournaments.disciplinary', $match->tournament) }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="size-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Tribunal Disciplinario
                </a>
                <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-[#057a55] hover:bg-[#046c4b] text-white text-xs font-semibold transition shadow-2xs flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimir / Exportar PDF
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="print:hidden p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('status') }}
            </div>
        @endif

        <!-- PRINTABLE OFFICIAL ACT CONTAINER -->
        <div id="official-acta" class="bg-white text-slate-900 p-6 sm:p-10 rounded-2xl shadow-2xl border border-slate-200 print:shadow-none print:border-none print:p-0 print:m-0 space-y-6">
            
            <!-- Acta Header -->
            <div class="border-b-2 border-slate-900 pb-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 rounded bg-slate-900 text-white font-mono font-black text-xs uppercase tracking-widest">SIGET</span>
                            <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest">Acta Arbitral Oficial &bull; IFAB / FIFA Standard</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-950 mt-1 uppercase tracking-tight">Acta Oficial de Juego</h2>
                        <p class="text-xs text-slate-600 font-medium">Torneo: <strong class="text-slate-900">{{ $match->tournament->name }}</strong> &bull; Código de Encuentro: #MATCH-{{ str_pad($match->id, 5, '0', STR_PAD_LEFT) }}</p>
                    </div>

                    <div class="text-left sm:text-right">
                        @if ($match->isLocked())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                &#10003; CERRADA E INMUTABLE
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">
                                BORRADOR PRELIMINAR
                            </span>
                        @endif
                        <p class="text-[11px] text-slate-500 font-mono mt-1">Cierre: {{ $match->locked_at?->format('d/m/Y H:i:s') ?? 'Pendiente' }}</p>
                    </div>
                </div>

                <!-- Match Details Strip -->
                <div class="mt-4 pt-4 border-t border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="block text-slate-500 uppercase font-semibold text-[10px]">Fecha y Hora</span>
                        <strong class="text-slate-900">{{ $match->match_date->format('d/m/Y - H:i') }} hrs</strong>
                    </div>
                    <div>
                        <span class="block text-slate-500 uppercase font-semibold text-[10px]">Sede y Cancha</span>
                        <strong class="text-slate-900">{{ $match->venue?->name ?? 'Sede Central' }} (Cancha #{{ $match->field_number ?? 1 }})</strong>
                    </div>
                    <div>
                        <span class="block text-slate-500 uppercase font-semibold text-[10px]">Árbitro Principal</span>
                        <strong class="text-slate-900">{{ $match->referee?->name ?? 'Colegio de Árbitros' }}</strong>
                    </div>
                    <div>
                        <span class="block text-slate-500 uppercase font-semibold text-[10px]">Duración</span>
                        <strong class="text-slate-900">{{ $match->tournament->rules?->match_duration_minutes ?? 90 }} minutos reglamentarios</strong>
                    </div>
                </div>
            </div>

            <!-- Scoreboard Banner -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 text-center">
                <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                    <div class="sm:text-right">
                        <h3 class="text-xl sm:text-2xl font-black text-slate-950 uppercase">{{ $match->homeTeam->name }}</h3>
                        <p class="text-xs text-slate-500 font-semibold">Director Técnico: {{ $match->homeTeam->coach_name ?? 'N/D' }}</p>
                    </div>

                    <div class="flex flex-col items-center">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Resultado Final</span>
                        <div class="flex items-center gap-3 text-4xl sm:text-5xl font-mono font-black text-slate-900">
                            <span>{{ $match->home_score }}</span>
                            <span class="text-slate-300">-</span>
                            <span>{{ $match->away_score }}</span>
                        </div>
                    </div>

                    <div class="sm:text-left">
                        <h3 class="text-xl sm:text-2xl font-black text-slate-950 uppercase">{{ $match->awayTeam->name }}</h3>
                        <p class="text-xs text-slate-500 font-semibold">Director Técnico: {{ $match->awayTeam->coach_name ?? 'N/D' }}</p>
                    </div>
                </div>
            </div>

            <!-- Lineups (Titulares y Suplentes) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Local Team Lineup -->
                <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h4 class="font-black text-sm text-slate-900 uppercase">{{ $match->homeTeam->name }} (Local)</h4>
                        <span class="text-[11px] font-mono text-slate-500">Titulares / Suplentes</span>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alineación Inicial</p>
                        <div class="divide-y divide-slate-100 text-xs">
                            @forelse ($match->startersForTeam($match->home_team_id) as $lineup)
                                <div class="py-1.5 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded bg-slate-900 text-white font-mono font-bold text-[10px] flex items-center justify-center">{{ $lineup->jersey_number }}</span>
                                        <span class="font-semibold text-slate-800">{{ $lineup->player->name }}</span>
                                        @if ($lineup->is_captain)
                                            <span class="px-1 rounded bg-amber-200 text-amber-900 font-black text-[9px]">C</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-mono text-slate-400 uppercase">{{ $lineup->position }}</span>
                                </div>
                            @empty
                                <p class="text-slate-400 italic text-[11px] py-2">Sin titulares registrados oficialmente.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Banca de Suplentes</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($match->substitutesForTeam($match->home_team_id) as $sub)
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px] font-medium">
                                    #{{ $sub->jersey_number }} {{ $sub->player->name }}
                                </span>
                            @empty
                                <span class="text-slate-400 italic text-[11px]">Sin suplentes registrados.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Away Team Lineup -->
                <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h4 class="font-black text-sm text-slate-900 uppercase">{{ $match->awayTeam->name }} (Visitante)</h4>
                        <span class="text-[11px] font-mono text-slate-500">Titulares / Suplentes</span>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alineación Inicial</p>
                        <div class="divide-y divide-slate-100 text-xs">
                            @forelse ($match->startersForTeam($match->away_team_id) as $lineup)
                                <div class="py-1.5 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded bg-slate-900 text-white font-mono font-bold text-[10px] flex items-center justify-center">{{ $lineup->jersey_number }}</span>
                                        <span class="font-semibold text-slate-800">{{ $lineup->player->name }}</span>
                                        @if ($lineup->is_captain)
                                            <span class="px-1 rounded bg-amber-200 text-amber-900 font-black text-[9px]">C</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-mono text-slate-400 uppercase">{{ $lineup->position }}</span>
                                </div>
                            @empty
                                <p class="text-slate-400 italic text-[11px] py-2">Sin titulares registrados oficialmente.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Banca de Suplentes</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($match->substitutesForTeam($match->away_team_id) as $sub)
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px] font-medium">
                                    #{{ $sub->jersey_number }} {{ $sub->player->name }}
                                </span>
                            @empty
                                <span class="text-slate-400 italic text-[11px]">Sin suplentes registrados.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Incident Chronology / Minutero Oficial -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                <h4 class="font-black text-sm text-slate-900 uppercase">Cronología Oficial de Incidencias</h4>
                @if ($match->events->isEmpty())
                    <p class="text-slate-500 italic text-xs py-2">No se presentaron incidencias disciplinarias ni goles registrados.</p>
                @else
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 text-[10px] uppercase font-bold">
                                <th class="py-2">Minuto</th>
                                <th class="py-2">Tipo</th>
                                <th class="py-2">Equipo</th>
                                <th class="py-2">Jugador</th>
                                <th class="py-2">Detalles / Sustituto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($match->events as $event)
                                <tr class="py-2">
                                    <td class="py-2 font-mono font-bold text-slate-700">{{ $event->formatted_time }}</td>
                                    <td class="py-2">
                                        <span class="inline-flex items-center gap-1 font-bold">
                                            <span>{{ $event->icon }}</span>
                                            <span>{{ $event->label }}</span>
                                        </span>
                                    </td>
                                    <td class="py-2 font-semibold text-slate-800">{{ $event->team->name }}</td>
                                    <td class="py-2 text-slate-900 font-bold">{{ $event->player?->name ?? 'N/A' }}</td>
                                    <td class="py-2 text-slate-500">
                                        @if ($event->event_type === 'substitution')
                                            Entra: <strong class="text-slate-700">{{ $event->subInPlayer?->name ?? 'N/D' }}</strong>
                                        @else
                                            {{ $event->notes ?? '-' }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Referee Observations -->
            @if ($match->match_sheet_notes)
                <div class="border border-slate-200 rounded-xl p-4 space-y-1 bg-slate-50/50">
                    <h4 class="font-black text-xs text-slate-900 uppercase">Informe y Observaciones del Árbitro</h4>
                    <p class="text-xs text-slate-700 whitespace-pre-line">{{ $match->match_sheet_notes }}</p>
                </div>
            @endif

            <!-- Tripartite Signatures Certification -->
            <div class="border-2 border-slate-900 rounded-2xl p-6 bg-slate-50/40">
                <div class="text-center mb-6">
                    <h4 class="text-sm font-black text-slate-950 uppercase tracking-widest">Certificación de Firmas Digitales</h4>
                    <p class="text-[11px] text-slate-500">Las firmas adjuntas certifican de forma indeleble e inmutable la validez jurídica del resultado.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                    <!-- Referee Signature Box -->
                    @php $refSig = $match->getSignature('referee'); @endphp
                    <div class="flex flex-col items-center justify-between p-3 border border-slate-300 rounded-xl bg-white shadow-sm">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Árbitro Principal</span>
                        <div class="h-20 w-full flex items-center justify-center my-2">
                            @if ($refSig)
                                <img src="{{ $refSig->signature_data }}" alt="Firma Árbitro" class="max-h-full max-w-full object-contain">
                            @else
                                <span class="text-xs text-slate-400 italic">Sin firma registrada</span>
                            @endif
                        </div>
                        <div class="border-t border-slate-300 pt-2 w-full">
                            <strong class="block text-xs text-slate-900">{{ $refSig?->signer_name ?? $match->referee?->name ?? 'Colegiado' }}</strong>
                            <span class="text-[9px] text-slate-400 font-mono">{{ $refSig?->signed_at?->format('d/m/Y H:i') ?? 'Pendiente' }}</span>
                        </div>
                    </div>

                    <!-- Home Coach Signature Box -->
                    @php $homeSig = $match->getSignature('home_coach'); @endphp
                    <div class="flex flex-col items-center justify-between p-3 border border-slate-300 rounded-xl bg-white shadow-sm">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">DT Local ({{ $match->homeTeam->name }})</span>
                        <div class="h-20 w-full flex items-center justify-center my-2">
                            @if ($homeSig)
                                <img src="{{ $homeSig->signature_data }}" alt="Firma DT Local" class="max-h-full max-w-full object-contain">
                            @else
                                <span class="text-xs text-slate-400 italic">Sin firma registrada</span>
                            @endif
                        </div>
                        <div class="border-t border-slate-300 pt-2 w-full">
                            <strong class="block text-xs text-slate-900">{{ $homeSig?->signer_name ?? $match->homeTeam->coach_name ?? 'DT Local' }}</strong>
                            <span class="text-[9px] text-slate-400 font-mono">{{ $homeSig?->signed_at?->format('d/m/Y H:i') ?? 'Pendiente' }}</span>
                        </div>
                    </div>

                    <!-- Away Coach Signature Box -->
                    @php $awaySig = $match->getSignature('away_coach'); @endphp
                    <div class="flex flex-col items-center justify-between p-3 border border-slate-300 rounded-xl bg-white shadow-sm">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">DT Visitante ({{ $match->awayTeam->name }})</span>
                        <div class="h-20 w-full flex items-center justify-center my-2">
                            @if ($awaySig)
                                <img src="{{ $awaySig->signature_data }}" alt="Firma DT Visitante" class="max-h-full max-w-full object-contain">
                            @else
                                <span class="text-xs text-slate-400 italic">Sin firma registrada</span>
                            @endif
                        </div>
                        <div class="border-t border-slate-300 pt-2 w-full">
                            <strong class="block text-xs text-slate-900">{{ $awaySig?->signer_name ?? $match->awayTeam->coach_name ?? 'DT Visitante' }}</strong>
                            <span class="text-[9px] text-slate-400 font-mono">{{ $awaySig?->signed_at?->format('d/m/Y H:i') ?? 'Pendiente' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cryptographic Verification Footer -->
            <div class="border-t border-slate-200 pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[10px] text-slate-500 font-mono">
                <div>
                    <span>HASH CRIPTOGRÁFICO DE VERIFICACIÓN:</span>
                    <span class="font-bold text-slate-700 ml-1 select-all break-all">{{ $verificationHash }}</span>
                </div>
                <div class="sm:text-right">
                    <span>SIGET SAAS &bull; PLATAFORMA DEPORTIVA CERTIFICADA</span>
                </div>
            </div>
        </div>
    </div>
@endsection
