@extends('v1.layouts.app')

@section('title', 'Gestión de Plantilla - ' . $team->name)

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('dt.dashboard', $team) }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
                ← Volver al Panel de DT
            </a>
            <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">Plantilla Oficial: {{ $team->name }}</h1>
            <p class="text-sm text-slate-400">Inscribe a los jugadores de forma manual o sube la nómina masiva en archivo CSV.</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-sm font-bold text-emerald-400">
                Total Jugadores: {{ $players->count() }}
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-400">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-400">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-3">
        <!-- Formulario Rápido de Registro Manual -->
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                <h2 class="text-base font-bold text-white">+ Agregar Jugador Manualmente</h2>
                <p class="text-xs text-slate-400">Inscripción rápida a pie de campo.</p>

                <form method="POST" action="{{ route('dt.players.store', $team) }}" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nombre Completo *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Ej: Lionel Messi" required class="mt-1 w-full px-3.5 py-2 text-sm" />
                    </div>

                    <div>
                        <label for="identification_document" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Documento de Identidad (Cédula/TI) *</label>
                        <input type="text" id="identification_document" name="identification_document" value="{{ old('identification_document') }}" placeholder="Ej: 1020304050" required class="mt-1 w-full px-3.5 py-2 text-sm" />
                    </div>

                    <div>
                        <label for="jersey_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Número de Dorsal (1 - 99) *</label>
                        <input type="number" id="jersey_number" name="jersey_number" value="{{ old('jersey_number') }}" min="1" max="99" placeholder="Ej: 10" required class="mt-1 w-full px-3.5 py-2 text-sm" />
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-emerald-500 py-2.5 text-sm font-bold text-slate-950 hover:bg-emerald-400 transition shadow-md">
                        Guardar en Plantilla
                    </button>
                </form>
            </div>

            <!-- Carga Masiva CSV -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                <h2 class="text-base font-bold text-white">📁 Carga Masiva (CSV / Excel)</h2>
                <p class="text-xs text-slate-400">Sube la lista de jugadores de una sola vez.</p>

                <div class="mt-3 rounded-lg border border-slate-800 bg-slate-950 p-3 text-[11px] text-slate-400">
                    <p class="font-bold text-slate-300">Formato del CSV (sin comillas):</p>
                    <p class="font-mono text-emerald-400 mt-1">Nombre, Documento, Dorsal</p>
                    <p class="text-slate-500 mt-1">Ej: Juan Pérez, 10203040, 7</p>
                </div>

                <form method="POST" action="{{ route('dt.roster.import', $team) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <input type="file" name="roster_file" accept=".csv,.txt" required class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-200 hover:file:bg-slate-700" />
                    </div>

                    <button type="submit" class="w-full rounded-xl border border-slate-700 bg-slate-800 py-2.5 text-sm font-bold text-slate-200 hover:bg-slate-700 transition">
                        Importar Archivo CSV
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabla de Jugadores Registrados -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm lg:col-span-2">
            <h2 class="text-lg font-bold text-white">Nómina Registrada</h2>
            <p class="text-xs text-slate-400">Jugadores habilitados para la selección de alineaciones.</p>

            @if ($players->isEmpty())
                <div class="mt-8 rounded-xl border border-dashed border-slate-800 p-8 text-center text-slate-400">
                    <span class="text-3xl">👥</span>
                    <p class="mt-2 text-sm">Aún no hay jugadores registrados en este equipo.</p>
                    <p class="text-xs text-slate-500">Usa el formulario a la izquierda para agregar tu primer jugador.</p>
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="border-b border-slate-800 text-xs uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Dorsal</th>
                                <th class="px-4 py-3">Nombre</th>
                                <th class="px-4 py-3">Documento</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach ($players as $player)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="px-4 py-3 font-mono font-black text-emerald-400">
                                        #{{ $player->jersey_number }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-white">
                                        <a href="{{ route('players.cromo', $player) }}" class="hover:text-emerald-400 hover:underline">
                                            {{ $player->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400 font-mono text-xs">
                                        {{ $player->identification_document }}
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <a href="{{ route('players.carnet', $player) }}" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 transition">
                                            📱 Carnet QR
                                        </a>
                                        <a href="{{ route('players.cromo', $player) }}" class="text-xs font-semibold text-slate-300 hover:text-white transition">
                                            Ficha
                                        </a>
                                        <form method="POST" action="{{ route('dt.players.destroy', [$team, $player]) }}" onsubmit="return confirm('¿Quitar a este jugador del plantel?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-rose-400 hover:text-rose-300 transition">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
