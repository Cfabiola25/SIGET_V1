@extends('v1.layouts.app')

@section('title', 'Control de Acceso y Carnet QR')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('matches.show', $match) }}" class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-emerald-600 hover:text-emerald-700 hover:underline">
                &larr; Volver al Partido
            </a>
            <h1 class="mt-1 text-2xl font-black text-slate-900 md:text-3xl tracking-tight">Control de Acceso QR en Cancha</h1>
            <p class="text-sm text-slate-500">{{ $match->homeTeam?->name ?? 'Equipo Local' }} vs {{ $match->awayTeam?->name ?? 'Equipo Visitante' }}</p>
        </div>

        <div>
            <span class="rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs">
                Mesa de Control / Árbitro
            </span>
        </div>
    </div>

    <!-- Escáner Activo de Carnet QR -->
    <div class="rounded-3xl border border-slate-800 bg-[#182232] p-6 shadow-xl text-white">
        <div class="text-center">
            <x-reicon name="camera" class="size-8 text-emerald-400 mx-auto" />
            <h2 class="mt-2 text-lg font-black text-white">Escaneo Rápido de Carnet</h2>
            <p class="text-xs text-slate-300">Pasa el lector o cámara sobre el carnet QR del jugador (o introduce el código).</p>
        </div>

        <div class="mt-4 flex gap-2">
            <input type="text" id="qrInput" autofocus placeholder="siget:player:1:abc..."
                   class="w-full bg-slate-900 px-4 py-3 font-mono text-sm text-emerald-400 border border-slate-700 rounded-xl focus:border-emerald-500 focus:outline-none" />
            <button type="button" id="btnScan" class="rounded-xl bg-emerald-500 px-6 py-3 text-sm font-black text-slate-950 hover:bg-emerald-400 transition shrink-0 cursor-pointer">
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
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-700 border-b border-slate-100 pb-2 flex items-center justify-between">
                <span>{{ $match->homeTeam?->name ?? 'Equipo Local' }}</span>
                <span class="text-[10px] text-slate-400 font-normal">Local</span>
            </h3>
            <div class="mt-3 divide-y divide-slate-100" id="homeLineupList">
                @forelse ($match->lineups->where('team_id', $match->home_team_id) as $lineup)
                    <div class="flex items-center justify-between py-2 text-xs" id="player-row-{{ $lineup->player_id }}">
                        <div>
                            <span class="font-mono font-bold text-slate-800">#{{ $lineup->jersey_number }}</span>
                            <span class="text-slate-900 font-medium ml-1">{{ $lineup->player?->name ?? 'Jugador' }}</span>
                            <span class="text-slate-400 ml-1">({{ $lineup->is_starter ? 'Titular' : 'Suplente' }})</span>
                        </div>
                        <span class="qr-status-badge rounded px-2 py-0.5 text-[10px] font-bold {{ $lineup->verified_by_qr ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                            {{ $lineup->verified_by_qr ? 'Verificado' : 'Pendiente' }}
                        </span>
                    </div>
                @empty
                    <p class="py-4 text-center text-xs text-slate-400">Sin jugadores en nómina</p>
                @endforelse
            </div>
        </div>

        <!-- Visitante -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <h3 class="text-sm font-bold uppercase tracking-wider text-sky-700 border-b border-slate-100 pb-2 flex items-center justify-between">
                <span>{{ $match->awayTeam?->name ?? 'Equipo Visitante' }}</span>
                <span class="text-[10px] text-slate-400 font-normal">Visitante</span>
            </h3>
            <div class="mt-3 divide-y divide-slate-100" id="awayLineupList">
                @forelse ($match->lineups->where('team_id', $match->away_team_id) as $lineup)
                    <div class="flex items-center justify-between py-2 text-xs" id="player-row-{{ $lineup->player_id }}">
                        <div>
                            <span class="font-mono font-bold text-slate-800">#{{ $lineup->jersey_number }}</span>
                            <span class="text-slate-900 font-medium ml-1">{{ $lineup->player?->name ?? 'Jugador' }}</span>
                            <span class="text-slate-400 ml-1">({{ $lineup->is_starter ? 'Titular' : 'Suplente' }})</span>
                        </div>
                        <span class="qr-status-badge rounded px-2 py-0.5 text-[10px] font-bold {{ $lineup->verified_by_qr ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                            {{ $lineup->verified_by_qr ? 'Verificado' : 'Pendiente' }}
                        </span>
                    </div>
                @empty
                    <p class="py-4 text-center text-xs text-slate-400">Sin jugadores en nómina</p>
                @endforelse
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
                scanResult.className = 'mt-6 rounded-2xl p-5 border text-left border-emerald-500/40 bg-emerald-950/60 text-emerald-300';
                scanResult.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-black text-white flex items-center justify-center gap-2"><x-reicon name="check" class="size-5 text-white" /><span>HABILITADO EN CANCHA</span></span>
                        <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-xs font-bold text-emerald-400">${data.player.lineup_status}</span>
                    </div>
                    <div class="mt-2 text-xl font-black text-white">#${data.player.jersey_number} ${data.player.name}</div>
                    <p class="text-xs text-slate-300">Equipo: <strong>${data.player.team}</strong> &bull; Doc: ${data.player.document} &bull; Sangre: <strong>${data.player.blood_type}</strong></p>
                `;

                // Actualizar badge en la lista
                const row = document.getElementById('player-row-' + data.player.id);
                if (row) {
                    const badge = row.querySelector('.qr-status-badge');
                    if (badge) {
                        badge.className = 'qr-status-badge rounded px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800';
                        badge.innerText = 'Verificado';
                    }
                }
            } else {
                scanResult.className = 'mt-6 rounded-2xl p-5 border text-left border-rose-500/40 bg-rose-950/60 text-rose-300';
                scanResult.innerHTML = `
                    <div class="font-black text-white text-lg flex items-center justify-center gap-2"><x-reicon name="alert-triangle" class="size-5 text-white" /><span>JUGADOR INHABILITADO</span></div>
                    <p class="mt-1 text-sm font-semibold">${data.message}</p>
                    ${data.player ? `<div class="mt-2 text-xs text-slate-300">${data.player.name} (${data.player.team})</div>` : ''}
                `;
            }

            qrInput.value = '';
            qrInput.focus();
        } catch (e) {
            scanResult.classList.remove('hidden');
            scanResult.className = 'mt-6 rounded-2xl p-5 border text-left border-rose-500/40 bg-rose-950/60 text-rose-300';
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
