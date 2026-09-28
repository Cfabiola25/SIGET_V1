@extends('v1.layouts.app')

@section('title', 'Control de Acceso y Carnet QR')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('matches.show', $match) }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
                ← Volver al Partido
            </a>
            <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">Control de Acceso QR en Cancha</h1>
            <p class="text-sm text-slate-400">{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }}</p>
        </div>

        <div>
            <span class="rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-1.5 text-xs font-bold text-slate-200">
                Mesa de Control / Árbitro
            </span>
        </div>
    </div>

    <!-- Escáner Activo de Carnet QR -->
    <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 backdrop-blur-xl shadow-2xl">
        <div class="text-center">
            <span class="text-3xl">📷</span>
            <h2 class="mt-1 text-lg font-black text-white">Escaneo Rápido de Carnet</h2>
            <p class="text-xs text-slate-400">Pasa el lector o cámara sobre el carnet QR del jugador (o introduce el código).</p>
        </div>

        <div class="mt-4 flex gap-2">
            <input type="text" id="qrInput" autofocus placeholder="siget:player:1:abc..."
                   class="w-full bg-slate-950 px-4 py-3 font-mono text-sm text-emerald-400 border border-slate-700 rounded-xl focus:border-emerald-500 focus:outline-none" />
            <button type="button" id="btnScan" class="rounded-xl bg-emerald-500 px-6 py-3 text-sm font-black text-slate-950 hover:bg-emerald-400 transition shrink-0">
                Verificar
            </button>
        </div>

        <!-- Banner de Resultado Instantáneo -->
        <div id="scanResult" class="hidden mt-6 rounded-2xl p-5 border text-left transition-all duration-300">
            <!-- Inyectado por JS -->
        </div>
    </div>

    <!-- Lista de Verificación en Nómina -->
    <div class="grid gap-6 md:grid-cols-2">
        <!-- Local -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-400 border-b border-slate-800 pb-2">
                {{ $match->homeTeam->name }}
            </h3>
            <div class="mt-3 divide-y divide-slate-800" id="homeLineupList">
                @foreach ($match->lineups->where('team_id', $match->home_team_id) as $lineup)
                    <div class="flex items-center justify-between py-2 text-xs" id="player-row-{{ $lineup->player_id }}">
                        <div>
                            <span class="font-mono font-bold text-slate-200">#{{ $lineup->jersey_number }}</span>
                            <span class="text-white font-medium ml-1">{{ $lineup->player->name }}</span>
                            <span class="text-slate-500 ml-1">({{ $lineup->is_starter ? 'Titular' : 'Suplente' }})</span>
                        </div>
                        <span class="qr-status-badge rounded px-2 py-0.5 text-[10px] font-bold {{ $lineup->verified_by_qr ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                            {{ $lineup->verified_by_qr ? '✅ Verificado' : 'Pendiente' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Visitante -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-blue-400 border-b border-slate-800 pb-2">
                {{ $match->awayTeam->name }}
            </h3>
            <div class="mt-3 divide-y divide-slate-800" id="awayLineupList">
                @foreach ($match->lineups->where('team_id', $match->away_team_id) as $lineup)
                    <div class="flex items-center justify-between py-2 text-xs" id="player-row-{{ $lineup->player_id }}">
                        <div>
                            <span class="font-mono font-bold text-slate-200">#{{ $lineup->jersey_number }}</span>
                            <span class="text-white font-medium ml-1">{{ $lineup->player->name }}</span>
                            <span class="text-slate-500 ml-1">({{ $lineup->is_starter ? 'Titular' : 'Suplente' }})</span>
                        </div>
                        <span class="qr-status-badge rounded px-2 py-0.5 text-[10px] font-bold {{ $lineup->verified_by_qr ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                            {{ $lineup->verified_by_qr ? '✅ Verificado' : 'Pendiente' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    const qrInput = document.getElementById('qrInput');
    const btnScan = document.getElementById('btnScan');
    const scanResult = document.getElementById('scanResult');

    async function processScan() {
        const payload = qrInput.value.trim();
        if (!payload) return;

        btnScan.disabled = true;
        btnScan.innerText = 'Verificando...';

        try {
            const res = await fetch("{{ route('referees.matches.scan.verify', $match) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ qr_payload: payload })
            });

            const data = await res.json();
            scanResult.classList.remove('hidden');

            if (data.success) {
                scanResult.className = 'mt-6 rounded-2xl p-5 border text-left border-emerald-500/40 bg-emerald-950/40 text-emerald-300';
                scanResult.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-black text-white">✅ HABILITADO EN CANCHA</span>
                        <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-xs font-bold text-emerald-400">${data.player.lineup_status}</span>
                    </div>
                    <div class="mt-2 text-xl font-black text-white">#${data.player.jersey_number} ${data.player.name}</div>
                    <p class="text-xs text-slate-300">Equipo: <strong>${data.player.team}</strong> • Doc: ${data.player.document} • Sangre: <strong>${data.player.blood_type}</strong></p>
                `;

                // Actualizar badge en la lista
                const row = document.getElementById('player-row-' + data.player.id);
                if (row) {
                    const badge = row.querySelector('.qr-status-badge');
                    if (badge) {
                        badge.className = 'qr-status-badge rounded px-2 py-0.5 text-[10px] font-bold bg-emerald-500/20 text-emerald-400';
                        badge.innerText = '✅ Verificado';
                    }
                }
            } else {
                scanResult.className = 'mt-6 rounded-2xl p-5 border text-left border-rose-500/40 bg-rose-950/40 text-rose-300';
                scanResult.innerHTML = `
                    <div class="font-black text-white text-lg">⛔ JUGADOR INHABILITADO</div>
                    <p class="mt-1 text-sm font-semibold">${data.message}</p>
                    ${data.player ? `<div class="mt-2 text-xs text-slate-300">${data.player.name} (${data.player.team})</div>` : ''}
                `;
            }

            qrInput.value = '';
            qrInput.focus();
        } catch (e) {
            scanResult.classList.remove('hidden');
            scanResult.className = 'mt-6 rounded-2xl p-5 border text-left border-rose-500/40 bg-rose-950/40 text-rose-300';
            scanResult.innerHTML = '<div class="font-bold">Error de comunicación con el servidor.</div>';
        } finally {
            btnScan.disabled = false;
            btnScan.innerText = 'Verificar';
        }
    }

    btnScan.addEventListener('click', processScan);
    qrInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            processScan();
        }
    });
</script>
@endsection
