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
            <p class="text-xs text-slate-500">Inscribe a los jugadores, gestiona su perfil deportivo y ficha médica oficial.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('scouting.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                <x-reicon name="search" class="size-4 text-emerald-600" />
                <span>Radar de Talentos / Fichar</span>
            </a>
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1 text-xs font-bold text-emerald-800">
                Total Jugadores: {{ $players->count() }}
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 flex items-center gap-2">
            <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Formulario Rápido de Registro Manual con Perfil Deportivo -->
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <h2 class="text-sm font-bold text-slate-900">+ Inscribir Jugador</h2>
                <p class="text-[11px] text-slate-500">Crea el jugador y genera automáticamente su perfil deportivo y ficha médica.</p>

                <form method="POST" action="{{ route('dt.players.store', $team) }}" class="mt-4 space-y-3.5">
                    @csrf

                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nombre Completo *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Ej: Kevin Mier" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label for="identification_document" class="block text-xs font-semibold text-slate-700 mb-1">Documento *</label>
                            <input type="text" id="identification_document" name="identification_document" value="{{ old('identification_document') }}" placeholder="CC-102030" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                        </div>

                        <div>
                            <label for="jersey_number" class="block text-xs font-semibold text-slate-700 mb-1">Dorsal (1-99) *</label>
                            <input type="number" id="jersey_number" name="jersey_number" value="{{ old('jersey_number') }}" min="1" max="99" placeholder="10" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label for="position" class="block text-xs font-semibold text-slate-700 mb-1">Posición</label>
                            <select id="position" name="position" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                                <option value="forward" {{ old('position') === 'forward' ? 'selected' : '' }}>Delantero</option>
                                <option value="midfielder" {{ old('position', 'midfielder') === 'midfielder' ? 'selected' : '' }}>Mediocampista</option>
                                <option value="defender" {{ old('position') === 'defender' ? 'selected' : '' }}>Defensa</option>
                                <option value="goalkeeper" {{ old('position') === 'goalkeeper' ? 'selected' : '' }}>Portero</option>
                            </select>
                        </div>

                        <div>
                            <label for="preferred_foot" class="block text-xs font-semibold text-slate-700 mb-1">Pie Hábil</label>
                            <select id="preferred_foot" name="preferred_foot" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                                <option value="right">Diestro</option>
                                <option value="left">Zurdo</option>
                                <option value="ambidextrous">Ambidiestro</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label for="height_cm" class="block text-xs font-semibold text-slate-700 mb-1">Estatura (cm)</label>
                            <input type="number" id="height_cm" name="height_cm" value="{{ old('height_cm') }}" min="120" max="230" placeholder="178" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                        </div>

                        <div>
                            <label for="weight_kg" class="block text-xs font-semibold text-slate-700 mb-1">Peso (kg)</label>
                            <input type="number" id="weight_kg" name="weight_kg" value="{{ old('weight_kg') }}" min="40" max="150" placeholder="74" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                        </div>
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-[#057a55] py-2.5 text-xs font-bold text-white hover:bg-[#046c4b] transition shadow-2xs mt-2">
                        Guardar en Plantilla
                    </button>
                </form>
            </div>

            <!-- Carga Masiva CSV -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <h2 class="text-sm font-bold text-slate-900"><x-reicon name="file-text" class="size-4 text-emerald-600 inline-block mr-1" /> Importar Nómina CSV</h2>
                <p class="text-[11px] text-slate-500">Carga masiva para plantillas completas.</p>

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
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Nómina Oficial Registrada</h2>
                    <p class="text-xs text-slate-500">Jugadores habilitados para la selección de alineaciones oficiales.</p>
                </div>
            </div>

            @if ($players->isEmpty())
                <div class="mt-8 rounded-xl border border-dashed border-slate-200 p-8 text-center text-slate-400">
                    <x-reicon name="users" class="size-8 text-slate-300 mx-auto block mb-2" />
                    <p class="mt-2 text-xs font-medium">Aún no hay jugadores registrados en este equipo.</p>
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 border-collapse">
                        <thead class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] uppercase tracking-wider text-slate-600 font-semibold">
                            <tr>
                                <th class="px-3.5 py-3 text-center">Dorsal</th>
                                <th class="px-3.5 py-3">Nombre</th>
                                <th class="px-3.5 py-3">Posición</th>
                                <th class="px-3.5 py-3 text-center">Ficha Médica</th>
                                <th class="px-3.5 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($players as $player)
                                @php
                                    $profile = $player->profile;
                                    $med = $player->medicalRecord;
                                    $posLabels = [
                                        'goalkeeper' => 'Portero',
                                        'defender' => 'Defensa',
                                        'midfielder' => 'Mediocampista',
                                        'forward' => 'Delantero',
                                    ];
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-3.5 py-3 text-center font-mono font-bold text-emerald-700">
                                        #{{ $player->jersey_number }}
                                    </td>
                                    <td class="px-3.5 py-3">
                                        <div class="font-bold text-slate-900">
                                            {{ $player->name }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            Doc: {{ $player->identification_document }}
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-3 font-medium text-slate-700">
                                        {{ $posLabels[$profile?->position] ?? 'Mediocampista' }}
                                    </td>
                                    <td class="px-3.5 py-3 text-center">
                                        @if ($med?->waiver_signed)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                ✓ Firmada ({{ $med->blood_type ?? 'O+' }})
                                            </span>
                                        @else
                                            <a href="{{ route('players.medical.edit', [$player, 'redirect_to' => route('dt.roster', $team)]) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100">
                                                Pendiente
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-3 text-right space-x-2">
                                        <a href="{{ route('players.profile.edit', [$player, 'redirect_to' => route('dt.roster', $team)]) }}" class="text-xs font-semibold text-[#057a55] hover:underline" title="Editar datos físicos y deportivos">
                                            Perfil
                                        </a>
                                        <a href="{{ route('players.medical.edit', [$player, 'redirect_to' => route('dt.roster', $team)]) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900" title="Editar ficha médica y EPS">
                                            Médica
                                        </a>
                                        <a href="{{ route('players.carnet', $player) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900" title="Ver carnet con código QR">
                                            Carnet
                                        </a>
                                        <form method="POST" action="{{ route('dt.players.release', [$team, $player]) }}" onsubmit="return confirm('¿Liberar a {{ $player->name }} a la Agencia Libre? Dejará el equipo y otros clubes podrán solicitar su fichaje.');" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs font-bold text-amber-600 hover:text-amber-800" title="Transferir a la Bolsa de Talentos / Agencia Libre">
                                                Liberar
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
