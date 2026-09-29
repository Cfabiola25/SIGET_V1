@extends('v1.layouts.app')

@section('title', 'Gestión de Plantilla - ' . $team->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('dt.dashboard', $team) }}" class="text-xs font-semibold uppercase tracking-wider text-[#057a55] hover:underline flex items-center gap-1">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al Panel de DT
            </a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Plantilla Oficial: {{ $team->name }}</h1>
            <p class="text-xs text-slate-500">Inscribe a los jugadores de forma manual o sube la nómina masiva en archivo CSV.</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1 text-xs font-bold text-emerald-800">
                Total Jugadores: {{ $players->count() }}
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Formulario Rápido de Registro Manual -->
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <h2 class="text-sm font-bold text-slate-900">+ Agregar Jugador</h2>
                <p class="text-[11px] text-slate-500">Inscripción directa a la nómina.</p>

                <form method="POST" action="{{ route('dt.players.store', $team) }}" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nombre Completo *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Ej: Lionel Messi" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                    </div>

                    <div>
                        <label for="identification_document" class="block text-xs font-semibold text-slate-700 mb-1">Documento de Identidad *</label>
                        <input type="text" id="identification_document" name="identification_document" value="{{ old('identification_document') }}" placeholder="Ej: 1020304050" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                    </div>

                    <div>
                        <label for="jersey_number" class="block text-xs font-semibold text-slate-700 mb-1">Dorsal (1 - 99) *</label>
                        <input type="number" id="jersey_number" name="jersey_number" value="{{ old('jersey_number') }}" min="1" max="99" placeholder="Ej: 10" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-[#057a55] py-2 text-xs font-semibold text-white hover:bg-[#046c4b] transition shadow-2xs">
                        Guardar en Plantilla
                    </button>
                </form>
            </div>

            <!-- Carga Masiva CSV -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <h2 class="text-sm font-bold text-slate-900">📁 Importar CSV</h2>
                <p class="text-[11px] text-slate-500">Carga masiva para nóminas completas.</p>

                <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-[11px] text-slate-600">
                    <p class="font-bold text-slate-800">Columnas requeridas:</p>
                    <p class="font-mono text-emerald-700 mt-0.5">Nombre, Documento, Dorsal</p>
                </div>

                <form method="POST" action="{{ route('dt.roster.import', $team) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <input type="file" name="roster_file" accept=".csv,.txt" required class="w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />
                    </div>

                    <button type="submit" class="w-full rounded-lg border border-slate-200 bg-white py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                        Importar Archivo CSV
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabla de Jugadores Registrados -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs lg:col-span-2">
            <h2 class="text-sm font-bold text-slate-900">Nómina Registrada</h2>
            <p class="text-xs text-slate-500">Jugadores habilitados para la selección de alineaciones.</p>

            @if ($players->isEmpty())
                <div class="mt-8 rounded-xl border border-dashed border-slate-200 p-8 text-center text-slate-400">
                    <span class="text-3xl block">👥</span>
                    <p class="mt-2 text-xs font-medium">Aún no hay jugadores registrados en este equipo.</p>
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 border-collapse">
                        <thead class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] uppercase tracking-wider text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-center">Dorsal</th>
                                <th class="px-4 py-3">Nombre</th>
                                <th class="px-4 py-3">Documento</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($players as $player)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-4 py-3 text-center font-mono font-bold text-emerald-700">
                                        #{{ $player->jersey_number }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">
                                        <a href="{{ route('players.cromo', $player) }}" class="hover:text-[#057a55] hover:underline">
                                            {{ $player->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 font-mono text-xs">
                                        {{ $player->identification_document }}
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <a href="{{ route('players.carnet', $player) }}" class="text-xs font-semibold text-[#057a55] hover:underline">
                                            📱 Carnet
                                        </a>
                                        <a href="{{ route('players.cromo', $player) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                                            Ficha
                                        </a>
                                        <form method="POST" action="{{ route('dt.players.destroy', [$team, $player]) }}" onsubmit="return confirm('¿Quitar a este jugador del plantel?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
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
