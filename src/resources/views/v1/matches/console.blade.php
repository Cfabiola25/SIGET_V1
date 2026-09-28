@extends('v1.layouts.app')

@section('title', 'Consola Match Day: ' . $match->homeTeam->name . ' vs ' . $match->awayTeam->name)

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <!-- Header de Consola -->
    <div class="flex items-center justify-between">
        <a href="{{ route('matches.show', $match) }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
            ← Salir de Consola
        </a>
        <div class="flex items-center gap-2">
            <span class="inline-flex size-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-xs font-bold text-slate-300 uppercase tracking-widest">Consola Oficial de Arbitraje</span>
        </div>
    </div>

    <!-- Marcador Central & Cronómetro Sincronizado -->
    <div class="relative overflow-hidden rounded-3xl border-2 border-emerald-500/40 bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 p-6 text-center shadow-2xl backdrop-blur-xl">
        <!-- Cronómetro Central Gigante -->
        <div class="space-y-1">
            <span id="periodBadge" class="rounded-full bg-slate-800 px-3 py-1 text-xs font-black uppercase tracking-wider text-emerald-400 border border-slate-700">
                {{ strtoupper(str_replace('_', ' ', $match->current_period)) }}
            </span>
            <div id="stopwatchDisplay" class="font-mono text-5xl font-black text-white sm:text-6xl tracking-tight">
                {{ $match->formatted_clock }}
            </div>
        </div>

        <!-- Controles Rápidos del Cronómetro -->
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
            <button type="button" id="btnToggleTimer" onclick="handleTimerAction('start')"
                    class="rounded-xl bg-emerald-500 px-5 py-2.5 text-xs font-black text-slate-950 shadow-md hover:bg-emerald-400 transition">
                ▶ Iniciar / Reanudar
            </button>
            <button type="button" onclick="handleTimerAction('pause')"
                    class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-xs font-bold text-slate-200 hover:bg-slate-700 transition">
                ⏸ Pausar
            </button>
            <button type="button" onclick="handleTimerAction('halftime')"
                    class="rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-2.5 text-xs font-bold text-amber-400 hover:bg-amber-500/20 transition">
                ☕ Entretiempo
            </button>
            <button type="button" onclick="handleTimerAction('start_2h')"
                    class="rounded-xl border border-blue-500/40 bg-blue-500/10 px-4 py-2.5 text-xs font-bold text-blue-400 hover:bg-blue-500/20 transition">
                ▶ Iniciar 2T (45')
            </button>
            <button type="button" onclick="handleTimerAction('end_match')"
                    class="rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-2.5 text-xs font-bold text-rose-400 hover:bg-rose-500/20 transition">
                🏁 Fin de Partido
            </button>
            @if ($match->isLocked())
                <a href="{{ route('matches.report', $match) }}"
                   class="rounded-xl border border-emerald-500/50 bg-emerald-500/20 px-4 py-2.5 text-xs font-black text-emerald-300 hover:bg-emerald-500/30 transition flex items-center gap-1.5">
                    📜 Ver Acta Oficial Firmada
                </a>
            @else
                <a href="{{ route('matches.closure', $match) }}"
                   class="rounded-xl border border-emerald-500/50 bg-emerald-500/20 px-4 py-2.5 text-xs font-black text-emerald-300 hover:bg-emerald-500/30 transition flex items-center gap-1.5">
                    ✍️ Firmar y Cerrar Acta
                </a>
            @endif
        </div>

        <!-- Marcador en Vivo -->
        <div class="mt-6 grid grid-cols-3 items-center border-t border-slate-800/80 pt-6">
            <div>
                <h3 class="text-sm font-bold text-slate-300">{{ $match->homeTeam->name }}</h3>
                <span class="text-[10px] text-slate-500 uppercase">Local</span>
            </div>
            <div class="font-mono text-4xl font-black text-white sm:text-5xl">
                <span id="homeScore">{{ $match->home_score }}</span>
                <span class="text-emerald-500">:</span>
                <span id="awayScore">{{ $match->away_score }}</span>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-300">{{ $match->awayTeam->name }}</h3>
                <span class="text-[10px] text-slate-500 uppercase">Visitante</span>
            </div>
        </div>
    </div>

    <!-- BOTONES DE ACCIÓN RÁPIDA A PIE DE CAMPO (BOTONES GIGANTES) -->
    <div class="space-y-3">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Acciones de Partido a 1 Toque</h2>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
            <!-- GOL -->
            <button type="button" onclick="openActionModal('goal', '⚽ GOL', 'emerald')"
                    class="flex flex-col items-center justify-center rounded-2xl border-2 border-emerald-500/50 bg-emerald-500/15 py-5 text-emerald-400 shadow-lg shadow-emerald-950/40 hover:bg-emerald-500/25 active:scale-95 transition">
                <span class="text-3xl">⚽</span>
                <span class="mt-1 text-base font-black">GOL</span>
            </button>

            <!-- TARJETA AMARILLA -->
            <button type="button" onclick="openActionModal('yellow_card', '🟨 TARJETA AMARILLA', 'amber')"
                    class="flex flex-col items-center justify-center rounded-2xl border-2 border-amber-500/50 bg-amber-500/15 py-5 text-amber-400 shadow-lg shadow-amber-950/40 hover:bg-amber-500/25 active:scale-95 transition">
                <span class="text-3xl">🟨</span>
                <span class="mt-1 text-sm font-black">AMARILLA</span>
            </button>

            <!-- TARJETA ROJA -->
            <button type="button" onclick="openActionModal('red_card', '🟥 TARJETA ROJA', 'rose')"
                    class="flex flex-col items-center justify-center rounded-2xl border-2 border-rose-500/50 bg-rose-500/15 py-5 text-rose-400 shadow-lg shadow-rose-950/40 hover:bg-rose-500/25 active:scale-95 transition">
                <span class="text-3xl">🟥</span>
                <span class="mt-1 text-sm font-black">ROJA</span>
            </button>

            <!-- SUSTITUCIÓN -->
            <button type="button" onclick="openActionModal('substitution', '🔄 CAMBIO', 'blue')"
                    class="flex flex-col items-center justify-center rounded-2xl border-2 border-blue-500/50 bg-blue-500/15 py-5 text-blue-400 shadow-lg shadow-blue-950/40 hover:bg-blue-500/25 active:scale-95 transition">
                <span class="text-3xl">🔄</span>
                <span class="mt-1 text-sm font-black">CAMBIO</span>
            </button>

            <!-- LESIÓN / AUXILIO -->
            <button type="button" onclick="openActionModal('injury', '🩹 ATENCIÓN MÉDICA', 'slate')"
                    class="col-span-2 sm:col-span-1 flex flex-col items-center justify-center rounded-2xl border-2 border-slate-700 bg-slate-800/60 py-5 text-slate-300 hover:bg-slate-700 active:scale-95 transition">
                <span class="text-3xl">🩹</span>
                <span class="mt-1 text-sm font-black">LESIÓN</span>
            </button>
        </div>
    </div>

    <!-- Línea de Tiempo Play-by-play de Incidencias -->
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm space-y-4">
        <h3 class="text-base font-bold text-white uppercase tracking-wider">Incidencias del Encuentro (Minuto a Minuto)</h3>

        <div id="eventsTimeline" class="divide-y divide-slate-800/80">
            @forelse ($match->events as $event)
                <div class="flex items-center justify-between py-2.5 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-emerald-400">{{ $event->formatted_time }}</span>
                        <span class="text-base">{{ $event->icon }}</span>
                        <div>
                            <span class="font-bold text-white">{{ $event->label }}</span>
                            <span class="text-slate-400">• {{ $event->team->name }}: <strong>{{ $event->player?->name ?? 'N/A' }}</strong></span>
                            @if ($event->subInPlayer)
                                <span class="text-emerald-400 font-semibold">(Entra: {{ $event->subInPlayer->name }})</span>
                            @endif
                        </div>
                    </div>
                    @if ($event->notes)
                        <span class="text-[11px] text-slate-500 italic">{{ $event->notes }}</span>
                    @endif
                </div>
            @empty
                <p id="noEventsMsg" class="py-4 text-center text-xs text-slate-400">Aún no se han registrado incidencias en este partido.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Flotante de Registro a 1 Toque -->
<div id="actionModal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 id="modalTitle" class="text-lg font-black text-white">Registrar Evento</h3>
            <button type="button" onclick="closeActionModal()" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>

        <!-- Paso 1: Seleccionar Equipo -->
        <div class="space-y-2">
            <span class="text-xs font-bold uppercase text-slate-400">1. Selecciona el Equipo:</span>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" id="btnTeamHome" onclick="selectTeam({{ $match->home_team_id }}, '{{ $match->homeTeam->name }}')"
                        class="team-btn rounded-xl border-2 border-slate-700 bg-slate-800 p-3 text-center text-xs font-bold text-white hover:border-emerald-500">
                    {{ $match->homeTeam->name }} (Local)
                </button>
                <button type="button" id="btnTeamAway" onclick="selectTeam({{ $match->away_team_id }}, '{{ $match->awayTeam->name }}')"
                        class="team-btn rounded-xl border-2 border-slate-700 bg-slate-800 p-3 text-center text-xs font-bold text-white hover:border-blue-500">
                    {{ $match->awayTeam->name }} (Visitante)
                </button>
            </div>
        </div>

        <!-- Paso 2: Seleccionar Jugador -->
        <div id="playerSelectionSection" class="hidden space-y-2">
            <span id="playerSelectionLabel" class="text-xs font-bold uppercase text-slate-400">2. Selecciona el Jugador:</span>
            <div id="playerGrid" class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
                <!-- Inyectado por JS -->
            </div>
        </div>

        <!-- Paso 3 (Opcional para Cambios): Jugador que ingresa -->
        <div id="subInSelectionSection" class="hidden space-y-2">
            <span class="text-xs font-bold uppercase text-slate-400">3. Jugador que Ingresa a Cancha:</span>
            <div id="subInPlayerGrid" class="grid grid-cols-2 gap-2 max-h-36 overflow-y-auto pr-1">
                <!-- Inyectado por JS -->
            </div>
        </div>

        <!-- Observación opcional -->
        <div>
            <input type="text" id="eventNotes" placeholder="Detalle opcional (ej: autogol, tiro libre)"
                   class="w-full bg-slate-950 px-3.5 py-2 text-xs text-slate-200 border border-slate-800 rounded-xl" />
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
            <button type="button" onclick="closeActionModal()" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-bold text-slate-300">
                Cancelar
            </button>
            <button type="button" id="btnConfirmEvent" onclick="submitEvent()" disabled
                    class="rounded-xl bg-emerald-500 px-5 py-2 text-xs font-black text-slate-950 disabled:opacity-50">
                Confirmar Registro
            </button>
        </div>
    </div>
</div>

<script>
    // Datos de jugadores pasados desde Blade
    const homePlayers = @json($match->homeTeam->players);
    const awayPlayers = @json($match->awayTeam->players);

    let activeEventType = null;
    let selectedTeamId = null;
    let selectedPlayerId = null;
    let selectedSubInPlayerId = null;

    // Cronómetro en cliente
    let elapsedSeconds = {{ $match->getCurrentClockSeconds() }};
    let isTimerRunning = {{ $match->is_timer_running ? 'true' : 'false' }};
    let timerInterval = null;

    function formatTime(totalSec) {
        const min = Math.floor(totalSec / 60);
        const sec = totalSec % 60;
        return String(min).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
    }

    function startClientTimer() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            if (isTimerRunning) {
                elapsedSeconds++;
                document.getElementById('stopwatchDisplay').innerText = formatTime(elapsedSeconds);
            }
        }, 1000);
    }

    if (isTimerRunning) {
        startClientTimer();
    }

    async function handleTimerAction(action) {
        try {
            const res = await fetch("{{ route('matches.timer.update', $match) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: action })
            });
            const data = await res.json();
            if (data.success) {
                elapsedSeconds = data.elapsed_seconds;
                isTimerRunning = data.is_timer_running;
                document.getElementById('stopwatchDisplay').innerText = data.formatted_clock;
                document.getElementById('periodBadge').innerText = data.current_period.toUpperCase();
                if (isTimerRunning) {
                    startClientTimer();
                } else if (timerInterval) {
                    clearInterval(timerInterval);
                }
            }
        } catch (e) {
            console.error('Error al actualizar cronómetro:', e);
        }
    }

    function openActionModal(type, title, color) {
        activeEventType = type;
        selectedTeamId = null;
        selectedPlayerId = null;
        selectedSubInPlayerId = null;

        document.getElementById('modalTitle').innerText = title;
        document.getElementById('playerSelectionSection').classList.add('hidden');
        document.getElementById('subInSelectionSection').classList.add('hidden');
        document.getElementById('eventNotes').value = '';
        document.getElementById('btnConfirmEvent').disabled = true;

        // Reset team button styles
        document.querySelectorAll('.team-btn').forEach(b => b.className = 'team-btn rounded-xl border-2 border-slate-700 bg-slate-800 p-3 text-center text-xs font-bold text-white hover:border-emerald-500');

        document.getElementById('actionModal').classList.remove('hidden');
    }

    function closeActionModal() {
        document.getElementById('actionModal').classList.add('hidden');
    }

    function selectTeam(teamId, teamName) {
        selectedTeamId = teamId;
        selectedPlayerId = null;
        selectedSubInPlayerId = null;

        document.querySelectorAll('.team-btn').forEach(b => b.classList.remove('border-emerald-500', 'bg-emerald-950/40'));
        event.target.classList.add('border-emerald-500', 'bg-emerald-950/40');

        const players = teamId === {{ $match->home_team_id }} ? homePlayers : awayPlayers;
        renderPlayers(players);
    }

    function renderPlayers(players) {
        const grid = document.getElementById('playerGrid');
        grid.innerHTML = '';

        players.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'player-select-btn rounded-lg border border-slate-700 bg-slate-800/80 p-2 text-left text-xs hover:border-emerald-500 text-slate-200';
            btn.innerHTML = `<span class="font-mono font-bold text-emerald-400">#${p.jersey_number}</span> ${p.name}`;
            btn.onclick = () => {
                document.querySelectorAll('.player-select-btn').forEach(b => b.classList.remove('border-emerald-500', 'bg-emerald-500/20'));
                btn.classList.add('border-emerald-500', 'bg-emerald-500/20');
                selectedPlayerId = p.id;

                if (activeEventType === 'substitution') {
                    document.getElementById('subInSelectionSection').classList.remove('hidden');
                    renderSubInPlayers(players.filter(sub => sub.id !== p.id));
                } else {
                    document.getElementById('btnConfirmEvent').disabled = false;
                }
            };
            grid.appendChild(btn);
        });

        document.getElementById('playerSelectionSection').classList.remove('hidden');
    }

    function renderSubInPlayers(players) {
        const grid = document.getElementById('subInPlayerGrid');
        grid.innerHTML = '';

        players.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'sub-select-btn rounded-lg border border-slate-700 bg-slate-800/80 p-2 text-left text-xs hover:border-blue-500 text-slate-200';
            btn.innerHTML = `<span class="font-mono font-bold text-blue-400">#${p.jersey_number}</span> ${p.name}`;
            btn.onclick = () => {
                document.querySelectorAll('.sub-select-btn').forEach(b => b.classList.remove('border-blue-500', 'bg-blue-500/20'));
                btn.classList.add('border-blue-500', 'bg-blue-500/20');
                selectedSubInPlayerId = p.id;
                document.getElementById('btnConfirmEvent').disabled = false;
            };
            grid.appendChild(btn);
        });
    }

    async function submitEvent() {
        if (!selectedTeamId || !activeEventType) return;

        const notes = document.getElementById('eventNotes').value;
        const btn = document.getElementById('btnConfirmEvent');
        btn.disabled = true;
        btn.innerText = 'Guardando...';

        try {
            const res = await fetch("{{ route('matches.events.store', $match) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    team_id: selectedTeamId,
                    event_type: activeEventType,
                    player_id: selectedPlayerId,
                    sub_in_player_id: selectedSubInPlayerId,
                    notes: notes
                })
            });

            const data = await res.json();
            if (data.success) {
                // Actualizar marcador
                document.getElementById('homeScore').innerText = data.home_score;
                document.getElementById('awayScore').innerText = data.away_score;

                // Añadir evento al timeline
                const noMsg = document.getElementById('noEventsMsg');
                if (noMsg) noMsg.remove();

                const timeline = document.getElementById('eventsTimeline');
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between py-2.5 text-xs border-b border-slate-800/80 animate-fade-in';
                row.innerHTML = `
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-emerald-400">${data.event.formatted_time}</span>
                        <span class="text-base">${data.event.icon}</span>
                        <div>
                            <span class="font-bold text-white">${data.event.label}</span>
                            <span class="text-slate-400">• ${data.event.player_name}</span>
                            ${data.event.sub_in_player_name ? `<span class="text-emerald-400 font-semibold">(Entra: ${data.event.sub_in_player_name})</span>` : ''}
                        </div>
                    </div>
                    ${data.event.notes ? `<span class="text-[11px] text-slate-500 italic">${data.event.notes}</span>` : ''}
                `;
                timeline.insertBefore(row, timeline.firstChild);

                closeActionModal();
            }
        } catch (e) {
            alert('Error al registrar incidencia: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerText = 'Confirmar Registro';
        }
    }
</script>
@endsection
