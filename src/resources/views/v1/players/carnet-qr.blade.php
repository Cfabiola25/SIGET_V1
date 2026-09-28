@extends('v1.layouts.app')

@section('title', 'Carnet Digital QR: ' . $player->name)

@section('content')
<div class="mx-auto max-w-sm py-4">
    <div class="mb-4 text-center">
        <a href="{{ route('players.cromo', $player) }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
            ← Volver a Ficha Deportiva
        </a>
    </div>

    <!-- Carnet Digital Móvil -->
    <div class="relative overflow-hidden rounded-3xl border-2 border-emerald-500/50 bg-gradient-to-b from-slate-900 via-slate-950 to-emerald-950/80 p-6 shadow-2xl backdrop-blur-xl text-center">
        <!-- Banda superior de la liga -->
        <div class="flex items-center justify-between border-b border-emerald-500/20 pb-3">
            <div class="flex items-center gap-2">
                <span class="grid size-6 place-items-center rounded bg-emerald-500 text-[10px] font-black text-slate-950">S</span>
                <span class="text-xs font-black tracking-widest text-white uppercase">SIGET CARNET</span>
            </div>
            <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400 border border-emerald-500/30">
                HABILITADO
            </span>
        </div>

        <!-- Identidad del Jugador -->
        <div class="mt-4">
            <div class="flex items-center justify-center gap-3">
                <span class="font-mono text-3xl font-black text-emerald-400">#{{ $player->jersey_number }}</span>
                <div class="text-left">
                    <h2 class="text-lg font-black text-white leading-tight">{{ $player->name }}</h2>
                    <p class="text-xs font-semibold text-emerald-300">{{ $player->team->name }}</p>
                </div>
            </div>
            <p class="mt-1 text-[11px] text-slate-400 font-mono">Doc: {{ $player->identification_document }} • Sangre: {{ $player->medicalRecord?->blood_type ?? 'O+' }}</p>
        </div>

        <!-- Código QR Central Vectorial -->
        <div class="my-6 flex justify-center">
            <div class="p-3 bg-white rounded-2xl shadow-xl border-4 border-emerald-500/30 inline-block">
                {!! $qrSvg !!}
            </div>
        </div>

        <!-- Reloj Dinámico Anti-Fraude (Evita capturas estáticas caducadas) -->
        <div class="rounded-xl border border-slate-800 bg-slate-900/80 p-2.5">
            <span class="text-[10px] uppercase tracking-wider text-slate-400 block">Sello Dinámico de Seguridad</span>
            <div id="liveClock" class="font-mono text-xs font-bold text-emerald-400">--:--:--</div>
        </div>

        <p class="mt-4 text-[11px] text-slate-400">
            Presenta este código al árbitro en la línea de cal para registrar tu ingreso en menos de 1 segundo.
        </p>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const el = document.getElementById('liveClock');
        if (el) {
            el.innerText = now.toLocaleDateString('es-CO') + ' • ' + now.toLocaleTimeString('es-CO');
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection
