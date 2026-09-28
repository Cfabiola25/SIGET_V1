@extends('v1.layouts.app')

@section('title', 'Ficha Élite: ' . $player->name)

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('dt.roster', $player->team) }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
                ← Volver a Nómina de {{ $player->team->name }}
            </a>
            <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">Ficha Oficial del Jugador</h1>
            <p class="text-sm text-slate-400">Hoja de vida deportiva pública y estadísticas oficiales en {{ $player->team->tournament->name }}.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('players.carnet', $player) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400">
                <span>📱</span> Ver Carnet Digital QR
            </a>
            @auth
                <a href="{{ route('players.medical.edit', $player) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2 text-xs font-bold text-slate-200 hover:bg-slate-700 transition">
                    🩺 Ficha Médica
                </a>
                <a href="{{ route('players.profile.edit', $player) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2 text-xs font-bold text-slate-200 hover:bg-slate-700 transition">
                    ✏️ Editar Perfil
                </a>
            @endauth
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-400">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-3">
        <!-- El "Cromo" del Jugador (Estilo Panini / FUT Card) -->
        <div class="lg:col-span-1">
            <div class="relative overflow-hidden rounded-3xl border-2 border-emerald-500/40 bg-gradient-to-b from-slate-900 via-slate-950 to-emerald-950/70 p-6 text-center shadow-2xl shadow-emerald-950/50 backdrop-blur-xl">
                <!-- Distintivo de Posición y Dorsal Superior -->
                <div class="flex items-center justify-between border-b border-emerald-500/20 pb-3">
                    <span class="rounded-lg bg-emerald-500/20 px-2.5 py-1 text-xs font-black uppercase tracking-wider text-emerald-300">
                        {{ $player->profile?->position_label ?? 'Mediocampista' }}
                    </span>
                    <span class="font-mono text-2xl font-black text-emerald-400">
                        #{{ $player->jersey_number }}
                    </span>
                </div>

                <!-- Silueta / Avatar del Jugador -->
                <div class="relative mx-auto my-6 size-36 overflow-hidden rounded-2xl border-2 border-slate-700 bg-slate-900 shadow-inner flex items-center justify-center">
                    @if ($player->profile?->photo_path)
                        <img src="{{ asset($player->profile->photo_path) }}" alt="{{ $player->name }}" class="size-full object-cover">
                    @else
                        <span class="text-6xl select-none">🏃</span>
                    @endif

                    @if ($stats['mvps'] > 0)
                        <span class="absolute bottom-1 right-1 rounded-md bg-amber-500 px-1.5 py-0.5 text-[10px] font-black text-slate-950 shadow">
                            ⭐ {{ $stats['mvps'] }} MVP
                        </span>
                    @endif
                </div>

                <!-- Nombre y Equipo -->
                <h2 class="text-xl font-black text-white tracking-tight">{{ $player->name }}</h2>
                <p class="mt-0.5 text-xs font-semibold text-emerald-400">{{ $player->team->name }}</p>
                <p class="text-[11px] text-slate-500 font-mono">{{ $player->team->tournament->name }}</p>

                <!-- Atributos Físicos -->
                <div class="mt-5 grid grid-cols-2 gap-2 border-t border-slate-800 pt-4 text-xs">
                    <div class="rounded-xl bg-slate-900/60 p-2 border border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase">Pierna Hábil</span>
                        <div class="font-bold text-slate-200">{{ $player->profile?->preferred_foot_label ?? 'Diestro' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-900/60 p-2 border border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase">Nacionalidad</span>
                        <div class="font-bold text-slate-200">{{ $player->profile?->nationality ?? 'Colombiana' }}</div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('players.carnet', $player) }}" class="block w-full rounded-xl border border-emerald-500/30 bg-emerald-500/10 py-2.5 text-xs font-black uppercase tracking-wider text-emerald-300 hover:bg-emerald-500/20 transition">
                        Escanear Carnet QR →
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas Acumuladas & Ficha Administrativa -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Estadísticas en el Torneo -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                <h2 class="text-base font-bold text-white uppercase tracking-wider">Estadísticas Oficiales en Competición</h2>
                <p class="text-xs text-slate-400">Rendimiento acumulado play-by-play en la liga actual.</p>

                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4 text-center">
                        <span class="text-xs font-semibold uppercase text-slate-400">Partidos</span>
                        <div class="mt-1 text-3xl font-black text-white">{{ $stats['matches'] }}</div>
                        <span class="text-[10px] text-slate-500">Convocatorias</span>
                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4 text-center">
                        <span class="text-xs font-semibold uppercase text-slate-400">Goles</span>
                        <div class="mt-1 text-3xl font-black text-emerald-400">{{ $stats['goals'] }}</div>
                        <span class="text-[10px] text-slate-500">Anotaciones</span>
                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4 text-center">
                        <span class="text-xs font-semibold uppercase text-slate-400">T. Amarillas</span>
                        <div class="mt-1 text-3xl font-black text-amber-400">{{ $stats['yellow_cards'] }}</div>
                        <span class="text-[10px] text-slate-500">Amonestaciones</span>
                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4 text-center">
                        <span class="text-xs font-semibold uppercase text-slate-400">T. Rojas</span>
                        <div class="mt-1 text-3xl font-black text-rose-400">{{ $stats['red_cards'] }}</div>
                        <span class="text-[10px] text-slate-500">Expulsiones</span>
                    </div>
                </div>
            </div>

            <!-- Ficha Médica y Estado de Exoneración -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-white">Estado Médico & Administrativo</h2>
                        <p class="text-xs text-slate-400">Información confidencial para auxilio médico en campo.</p>
                    </div>
                    @if ($player->medicalRecord?->isClearedForMatch())
                        <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/30">
                            ✅ Habilitado Médicamente
                        </span>
                    @else
                        <span class="rounded-full bg-rose-500/20 px-3 py-1 text-xs font-bold text-rose-400 border border-rose-500/30">
                            ⚠️ Incompleto
                        </span>
                    @endif
                </div>

                <div class="mt-4 grid gap-4 text-xs sm:grid-cols-2">
                    <div>
                        <span class="text-slate-400">Documento de Identidad:</span>
                        <p class="font-mono font-bold text-slate-200">{{ $player->identification_document }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400">Grupo Sanguíneo:</span>
                        <p class="font-bold text-emerald-400">{{ $player->medicalRecord?->blood_type ?? 'O+' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400">Afiliación de Salud (EPS / Seguro):</span>
                        <p class="font-bold text-slate-200">{{ $player->medicalRecord?->health_provider ?? 'No registrado' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400">Contacto de Emergencia:</span>
                        <p class="font-bold text-slate-200">
                            {{ $player->medicalRecord?->emergency_contact_name ?? 'No registrado' }}
                            @if ($player->medicalRecord?->emergency_contact_phone)
                                ({{ $player->medicalRecord->emergency_contact_phone }})
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-4 border-t border-slate-800/80 pt-3 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        Firma de Exoneración:
                        @if ($player->medicalRecord?->waiver_signed)
                            <span class="text-emerald-400 font-bold">Firmada digitalmente</span>
                        @else
                            <span class="text-rose-400 font-bold">Pendiente de firma</span>
                        @endif
                    </span>
                    <a href="{{ route('players.medical.edit', $player) }}" class="text-xs font-bold text-emerald-400 hover:underline">
                        Actualizar ficha médica →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
