@extends('v1.layouts.public')

@section('title', ($match->homeTeam?->name ?? 'Local') . ' vs ' . ($match->awayTeam?->name ?? 'Visitante'))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Migas de Pan & Acceso Rápido -->
    <div class="flex items-center justify-between text-xs">
        <a href="{{ route('matches.index') }}" class="font-bold text-[#057a55] hover:underline flex items-center gap-1">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver al Calendario</span>
        </a>

        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isReferee())
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-medium">Modo Oficial:</span>
                    <a href="{{ route('admin.matches.show', $match) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 text-emerald-400 text-xs font-bold hover:bg-slate-800 transition">
                        <span>Gestionar en Panel Admin</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            @endif
        @endauth
    </div>

    <!-- TARJETA PRINCIPAL DEL PARTIDO / SCOREBOARD -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        
        <!-- Barra de Torneo & Estado -->
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 font-bold text-slate-700">
                <span class="text-base">🏆</span>
                <span>{{ $match->tournament?->name ?? 'Torneo Oficial' }}</span>
                <span class="text-slate-300">•</span>
                <span class="text-slate-500 font-medium">{{ $match->tournament?->sport_type ?? 'Fútbol' }}</span>
            </div>

            <div>
                @if($match->status === 'live' || $match->is_timer_running)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                        <span class="size-2 rounded-full bg-rose-600"></span>
                        <span>EN VIVO {{ $match->formatted_clock ? '• ' . $match->formatted_clock : '' }}</span>
                    </span>
                @elseif($match->status === 'played')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <span>✓ Finalizado Oficial</span>
                    </span>
                @elseif($match->status === 'suspended')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        <span>Suspendido</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        <span>Programado</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Enfrentamiento Central -->
        <div class="p-6 sm:p-10 grid grid-cols-3 items-center text-center">
            
            <!-- Equipo Local -->
            <div class="flex flex-col items-center">
                <div class="size-20 sm:size-24 rounded-3xl bg-emerald-50 border border-emerald-200 text-[#057a55] font-black text-2xl sm:text-3xl flex items-center justify-center shadow-xs">
                    {{ strtoupper(substr($match->homeTeam?->name ?? 'LOC', 0, 2)) }}
                </div>
                <h2 class="mt-3 font-display text-base sm:text-xl font-bold text-slate-900 leading-tight">
                    {{ $match->homeTeam?->name ?? 'Equipo Local' }}
                </h2>
                <a href="{{ route('teams.show', $match->homeTeam) }}" class="text-xs text-emerald-700 font-semibold hover:underline mt-1">
                    Ver Club
                </a>
            </div>

            <!-- Marcador / VS -->
            <div class="flex flex-col items-center">
                @if($match->status === 'played' || $match->status === 'live' || $match->is_timer_running)
                    <div class="text-4xl sm:text-6xl font-black text-slate-900 font-mono tracking-tight bg-slate-100 px-5 py-2 rounded-2xl border border-slate-200/80">
                        {{ $match->home_score ?? 0 }} <span class="text-slate-400 font-sans">-</span> {{ $match->away_score ?? 0 }}
                    </div>
                @else
                    <div class="text-3xl sm:text-5xl font-black text-slate-300 font-display">VS</div>
                @endif

                <div class="mt-3 text-xs sm:text-sm font-semibold text-slate-600 font-mono">
                    {{ $match->match_date ? $match->match_date->format('d/m/Y • H:i') . ' hrs' : 'Fecha por programar' }}
                </div>

                @if($match->venue)
                    <div class="mt-1 text-xs text-slate-500 flex items-center justify-center gap-1">
                        <span>📍 {{ $match->venue->name }}</span>
                        @if($match->field_number) <span>(Cancha {{ $match->field_number }})</span> @endif
                    </div>
                @endif
            </div>

            <!-- Equipo Visitante -->
            <div class="flex flex-col items-center">
                <div class="size-20 sm:size-24 rounded-3xl bg-blue-50 border border-blue-200 text-blue-700 font-black text-2xl sm:text-3xl flex items-center justify-center shadow-xs">
                    {{ strtoupper(substr($match->awayTeam?->name ?? 'VIS', 0, 2)) }}
                </div>
                <h2 class="mt-3 font-display text-base sm:text-xl font-bold text-slate-900 leading-tight">
                    {{ $match->awayTeam?->name ?? 'Equipo Visitante' }}
                </h2>
                <a href="{{ route('teams.show', $match->awayTeam) }}" class="text-xs text-blue-700 font-semibold hover:underline mt-1">
                    Ver Club
                </a>
            </div>
        </div>

        <!-- Ficha Rápida Inferior: Árbitro y Acta -->
        <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-600">
            <div class="flex items-center gap-4">
                @if($match->referee)
                    <span class="flex items-center gap-1.5 font-medium">
                        <span class="font-bold text-slate-800">Árbitro:</span> {{ $match->referee->name }}
                    </span>
                @endif
            </div>
            <div>
                <a href="{{ route('matches.report', $match) }}" target="_blank" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-[#057a55] hover:underline">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ver Acta Oficial Digital</span>
                </a>
            </div>
        </div>

    </div>

    <!-- SECCIÓN DE 2 COLUMNAS: INCIDENCIAS & VOTACIÓN MVP -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Columna Izquierda: Incidencias del Partido (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="font-display font-bold text-base text-slate-900 flex items-center gap-2">
                        <span>⏱️</span>
                        <span>Incidencias & Cronología Oficial</span>
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">{{ $match->events->count() }} eventos</span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($match->events->sortBy('minute') as $event)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                            <div class="flex items-center gap-3">
                                <span class="font-mono font-bold text-slate-500 w-10 text-right">{{ $event->minute }}'</span>
                                
                                <span class="size-7 rounded-lg flex items-center justify-center font-bold text-sm
                                    {{ $event->event_type === 'goal' ? 'bg-emerald-100 text-emerald-800' : ($event->event_type === 'yellow_card' ? 'bg-amber-100 text-amber-800' : ($event->event_type === 'red_card' ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-700')) }}">
                                    {{ $event->event_type === 'goal' ? '⚽' : ($event->event_type === 'yellow_card' ? '🟨' : ($event->event_type === 'red_card' ? '🟥' : '🔄')) }}
                                </span>

                                <div>
                                    <div class="font-bold text-slate-900">{{ $event->player?->name ?? 'Jugador' }}</div>
                                    @php
                                        $eventLabel = match($event->event_type) {
                                            'goal' => 'Gol',
                                            'yellow_card' => 'Tarjeta Amarilla',
                                            'red_card' => 'Tarjeta Roja',
                                            'substitution' => 'Cambio / Sustitución',
                                            default => ucfirst($event->event_type)
                                        };
                                    @endphp
                                    <div class="text-[11px] text-slate-500">{{ $event->player?->team?->name }} • {{ $eventLabel }}</div>
                                </div>
                            </div>

                            @if($event->notes)
                                <span class="text-[11px] text-slate-400 italic max-w-[150px] truncate">{{ $event->notes }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            No hay incidencias registradas en este encuentro aún.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Alineaciones Oficiales -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs">
                <h3 class="font-display font-bold text-base text-slate-900 pb-4 border-b border-slate-100 flex items-center gap-2">
                    <span>📋</span>
                    <span>Nóminas Oficiales de Jugadores</span>
                </h3>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Local -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-700 pb-2 border-b border-emerald-100 mb-3">
                            {{ $match->homeTeam?->name }}
                        </h4>
                        <div class="space-y-1.5">
                            @forelse($match->homeTeam?->players ?? [] as $p)
                                <div class="flex items-center justify-between text-xs py-1 px-2 rounded hover:bg-slate-50">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-slate-400 w-5">#{{ $p->jersey_number }}</span>
                                        <span class="font-medium text-slate-800">{{ $p->name }}</span>
                                    </div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">{{ substr($p->position ?? 'JUG', 0, 3) }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400">Sin jugadores registrados.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Visitante -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-blue-700 pb-2 border-b border-blue-100 mb-3">
                            {{ $match->awayTeam?->name }}
                        </h4>
                        <div class="space-y-1.5">
                            @forelse($match->awayTeam?->players ?? [] as $p)
                                <div class="flex items-center justify-between text-xs py-1 px-2 rounded hover:bg-slate-50">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-slate-400 w-5">#{{ $p->jersey_number }}</span>
                                        <span class="font-medium text-slate-800">{{ $p->name }}</span>
                                    </div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">{{ substr($p->position ?? 'JUG', 0, 3) }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400">Sin jugadores registrados.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Votación de MVP de la Afición (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-gradient-to-br from-amber-500/10 via-white to-amber-500/5 rounded-2xl border border-amber-200/80 p-6 shadow-xs">
                <div class="flex items-center gap-2.5 pb-4 border-b border-amber-200/50">
                    <span class="text-2xl">🏆</span>
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-900">Votación Jugador MVP</h3>
                        <p class="text-xs text-slate-500">Elige a la figura destacada de este partido</p>
                    </div>
                </div>

                @if(($match->mvp_player_id || $match->official_mvp_player_id) && $match->mvpPlayer)
                    <div class="mt-5 p-4 rounded-xl bg-amber-100 border border-amber-300 text-center">
                        <div class="text-[10px] font-black uppercase tracking-wider text-amber-800">OFICIAL MVP OF THE MATCH</div>
                        <div class="text-lg font-black text-slate-900 mt-1">{{ $match->mvpPlayer->name }}</div>
                        <div class="text-xs text-slate-600 mt-0.5">{{ $match->mvpPlayer->team?->name }} • Dorsal #{{ $match->mvpPlayer->jersey_number }}</div>
                    </div>
                @else
                    <form id="publicMvpForm" class="mt-5 space-y-3" onsubmit="handleMvpVote(event)">
                        @csrf
                        <label class="block text-xs font-bold text-slate-700">Selecciona al Mejor Jugador</label>
                        <select id="mvpPlayerSelect" required class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:border-amber-500 outline-none">
                            <option value="">Seleccionar de la nómina...</option>
                            @foreach($match->homeTeam?->players ?? [] as $p)
                                <option value="{{ $p->id }}">#{{ $p->jersey_number }} {{ $p->name }} ({{ $match->homeTeam?->name }})</option>
                            @endforeach
                            @foreach($match->awayTeam?->players ?? [] as $p)
                                <option value="{{ $p->id }}">#{{ $p->jersey_number }} {{ $p->name }} ({{ $match->awayTeam?->name }})</option>
                            @endforeach
                        </select>
                        <button type="submit" id="btnVoteMvp" class="w-full rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black py-2.5 shadow-xs transition">
                            Emitir Voto de Afición
                        </button>
                    </form>
                    <div id="mvpFeedback" class="hidden mt-3 p-3 rounded-xl text-xs font-bold text-center"></div>
                @endif

                <!-- Desglose de Votos -->
                <div class="mt-6 pt-4 border-t border-amber-200/50">
                    <div class="text-xs font-bold text-slate-700 mb-3 flex items-center justify-between">
                        <span>Preferencia de la Afición</span>
                        <span id="mvpTotalCount" class="text-amber-700 font-mono text-[11px]">{{ $match->mvpVotes->count() }} votos</span>
                    </div>

                    <div id="mvpBreakdownList" class="space-y-2">
                        @php
                            $totalVotes = $match->mvpVotes->count();
                            $groupedVotes = $match->mvpVotes->groupBy('player_id');
                        @endphp
                        @forelse($groupedVotes as $playerId => $votes)
                            @php
                                $player = $votes->first()?->player;
                                $percentage = $totalVotes > 0 ? round(($votes->count() / $totalVotes) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                                    <span>{{ $player?->name ?? 'Jugador' }}</span>
                                    <span class="font-mono text-amber-700">{{ $percentage }}% ({{ $votes->count() }})</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Sé el primero en votar por el MVP de este encuentro.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>

    @if($match->chronicle_body || $match->chronicle_title)
        <!-- CRÓNICA DEPORTIVA PERIODÍSTICA IA (IA MATCH REPORTER) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-4">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="size-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-black">🤖</span>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700">IA MATCH REPORTER</span>
                        <h3 class="font-display font-bold text-lg text-slate-900">{{ $match->chronicle_title ?? 'Crónica Oficial del Encuentro' }}</h3>
                    </div>
                </div>
                @if($match->chronicle_generated_at)
                    <span class="text-xs text-slate-400 font-mono">{{ $match->chronicle_generated_at->format('d/m/Y H:i') }}</span>
                @endif
            </div>

            <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed space-y-3">
                {!! nl2br(e($match->chronicle_body)) !!}
            </div>
        </div>
    @endif

</div>

<script>
async function handleMvpVote(e) {
    e.preventDefault();
    const select = document.getElementById('mvpPlayerSelect');
    const playerId = select.value;
    if (!playerId) return;

    const btn = document.getElementById('btnVoteMvp');
    const feedback = document.getElementById('mvpFeedback');
    btn.disabled = true;

    try {
        const res = await fetch("{{ route('matches.mvp.vote', $match) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({ player_id: playerId })
        });
        const data = await res.json();
        feedback.classList.remove('hidden');
        if (res.ok && data.success) {
            feedback.className = "mt-3 p-3 rounded-xl text-xs font-bold text-center bg-emerald-100 text-emerald-800 border border-emerald-300";
            feedback.innerText = "¡Tu voto ha sido registrado con éxito!";
            setTimeout(() => window.location.reload(), 1500);
        } else {
            feedback.className = "mt-3 p-3 rounded-xl text-xs font-bold text-center bg-rose-100 text-rose-800 border border-rose-300";
            feedback.innerText = data.message || "Error al registrar el voto.";
        }
    } catch (err) {
        feedback.classList.remove('hidden');
        feedback.className = "mt-3 p-3 rounded-xl text-xs font-bold text-center bg-rose-100 text-rose-800 border border-rose-300";
        feedback.innerText = "No se pudo conectar con el servidor.";
    } finally {
        btn.disabled = false;
    }
}
</script>
@endsection
