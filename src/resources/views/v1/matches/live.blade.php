@extends('v1.layouts.app')

@section('title', 'Transmisión en Vivo - Marcador en Tiempo Real')

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex size-3 rounded-full bg-rose-500 animate-ping"></span>
                <span class="text-xs font-black uppercase tracking-widest text-rose-400">EN VIVO • LIVE SCORING</span>
            </div>
            <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">Centro de Partidos en Directo</h1>
            <p class="text-sm text-slate-400">Actualización de marcadores, tiempos y eventos de partido al instante sin recargar la página.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-1.5 text-xs font-semibold text-slate-300">
                Sincronización Automática: <strong class="text-emerald-400">Activa</strong>
            </span>
        </div>
    </div>

    @if ($matches->isEmpty())
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-12 text-center">
            <span class="text-4xl">⚽</span>
            <h2 class="mt-3 text-lg font-bold text-white">No hay partidos en disputa en este momento</h2>
            <p class="mt-1 text-sm text-slate-400">Los partidos activos aparecerán aquí automáticamente en cuanto el árbitro inicie el cronómetro.</p>
            <a href="{{ route('matches.index') }}" class="mt-4 inline-block rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-slate-950 hover:bg-emerald-400">
                Ver fixture programado →
            </a>
        </div>
    @else
        @foreach ($matches as $match)
            <div id="match-card-{{ $match->id }}" class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900/90 to-emerald-950/40 p-6 md:p-8 backdrop-blur-xl shadow-2xl space-y-6">
                <!-- Header del Partido -->
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                    <div>
                        <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">{{ $match->tournament->name }}</span>
                        @if ($match->venue)
                            <span class="text-slate-500 text-xs mx-1.5">•</span>
                            <span class="text-xs text-slate-400">📍 {{ $match->venue->name }}</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="live-period-badge rounded-full bg-rose-500/20 px-3 py-1 text-xs font-black uppercase text-rose-400 border border-rose-500/30">
                            {{ $match->current_period === 'second_half' ? '2T EN VIVO' : ($match->current_period === 'halftime' ? 'ENTRETIEMPO' : '1T EN VIVO') }}
                        </span>
                        <span class="live-clock-badge font-mono text-xs font-black text-emerald-400 bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800">
                            {{ $match->formatted_clock }}
                        </span>
                    </div>
                </div>

                <!-- Marcador Dinámico -->
                <div class="grid grid-cols-3 items-center text-center">
                    <div>
                        <h2 class="text-lg font-black text-white sm:text-2xl">{{ $match->homeTeam->name }}</h2>
                        <span class="text-xs font-semibold text-slate-400">Local</span>
                    </div>

                    <div>
                        <div class="font-mono text-4xl font-black text-white sm:text-6xl tracking-tight">
                            <span class="live-home-score">{{ $match->home_score }}</span>
                            <span class="text-emerald-500">:</span>
                            <span class="live-away-score">{{ $match->away_score }}</span>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-white sm:text-2xl">{{ $match->awayTeam->name }}</h2>
                        <span class="text-xs font-semibold text-slate-400">Visitante</span>
                    </div>
                </div>

                <!-- Línea de Tiempo Minuto a Minuto -->
                <div class="border-t border-slate-800/80 pt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Minuto a Minuto Oficial</h3>
                        <a href="{{ route('matches.show', $match) }}" class="text-xs font-bold text-emerald-400 hover:underline">
                            Ver nóminas y ficha completa →
                        </a>
                    </div>

                    <div class="live-events-container divide-y divide-slate-800/80 max-h-48 overflow-y-auto pr-1">
                        @forelse ($match->events as $event)
                            <div class="flex items-center justify-between py-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-emerald-400">{{ $event->formatted_time }}</span>
                                    <span>{{ $event->icon }}</span>
                                    <span class="font-bold text-white">{{ $event->label }}</span>
                                    <span class="text-slate-400">• {{ $event->player?->name ?? 'N/A' }}</span>
                                </div>
                                @if ($event->notes)
                                    <span class="text-[11px] text-slate-500 italic">{{ $event->notes }}</span>
                                @endif
                            </div>
                        @empty
                            <p class="py-3 text-center text-xs text-slate-500">Esperando el pitazo inicial...</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<script>
    // Live polling / WebSocket fallback cada 3 segundos para actualización en tiempo real sin recargar
    @if (!$matches->isEmpty())
        const matchIds = @json($matches->pluck('id'));

        async function syncLiveMatches() {
            for (const matchId of matchIds) {
                try {
                    const res = await fetch(`/matches/${matchId}/live-feed`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) continue;

                    const data = await res.json();
                    const card = document.getElementById(`match-card-${matchId}`);
                    if (!card) continue;

                    // Actualizar marcador
                    const homeScoreEl = card.querySelector('.live-home-score');
                    const awayScoreEl = card.querySelector('.live-away-score');

                    if (homeScoreEl && homeScoreEl.innerText != data.home_score) {
                        homeScoreEl.innerText = data.home_score;
                        homeScoreEl.classList.add('text-emerald-400', 'scale-125');
                        setTimeout(() => homeScoreEl.classList.remove('text-emerald-400', 'scale-125'), 1500);
                    }

                    if (awayScoreEl && awayScoreEl.innerText != data.away_score) {
                        awayScoreEl.innerText = data.away_score;
                        awayScoreEl.classList.add('text-emerald-400', 'scale-125');
                        setTimeout(() => awayScoreEl.classList.remove('text-emerald-400', 'scale-125'), 1500);
                    }

                    // Actualizar reloj
                    const clockEl = card.querySelector('.live-clock-badge');
                    if (clockEl) {
                        clockEl.innerText = data.formatted_clock;
                    }

                    // Actualizar eventos
                    const container = card.querySelector('.live-events-container');
                    if (container && data.events && data.events.length > 0) {
                        let html = '';
                        data.events.forEach(e => {
                            html += `
                                <div class="flex items-center justify-between py-2 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-emerald-400">${e.formatted_time}</span>
                                        <span>${e.icon}</span>
                                        <span class="font-bold text-white">${e.label}</span>
                                        <span class="text-slate-400">• ${e.player_name} (${e.team_name})</span>
                                        ${e.sub_in_player_name ? `<span class="text-emerald-400 font-semibold">(Entra: ${e.sub_in_player_name})</span>` : ''}
                                    </div>
                                    ${e.notes ? `<span class="text-[11px] text-slate-500 italic">${e.notes}</span>` : ''}
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    }
                } catch (err) {
                    console.warn('Error al sincronizar partido en vivo:', err);
                }
            }
        }

        setInterval(syncLiveMatches, 3000);
    @endif
</script>
@endsection
