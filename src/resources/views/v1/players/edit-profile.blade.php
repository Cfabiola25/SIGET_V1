@extends('v1.layouts.app')

@section('title', 'Editar Perfil Deportivo: ' . $player->name)

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <a href="{{ route('players.cromo', $player) }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
            ← Volver a Ficha Deportiva
        </a>
        <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">Editar Perfil Deportivo</h1>
        <p class="text-sm text-slate-400">Actualiza la posición, pie hábil y atributos físicos de {{ $player->name }}.</p>
    </div>

    <form method="POST" action="{{ route('players.profile.update', $player) }}" class="space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="position" class="block text-sm font-medium text-slate-300">Posición en el Campo *</label>
                    <select id="position" name="position" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm">
                        <option value="goalkeeper" {{ old('position', $player->profile?->position) === 'goalkeeper' ? 'selected' : '' }}>Portero</option>
                        <option value="defender" {{ old('position', $player->profile?->position) === 'defender' ? 'selected' : '' }}>Defensa</option>
                        <option value="midfielder" {{ old('position', $player->profile?->position) === 'midfielder' ? 'selected' : '' }}>Mediocampista</option>
                        <option value="forward" {{ old('position', $player->profile?->position) === 'forward' ? 'selected' : '' }}>Delantero</option>
                    </select>
                </div>

                <div>
                    <label for="preferred_foot" class="block text-sm font-medium text-slate-300">Pierna Hábil *</label>
                    <select id="preferred_foot" name="preferred_foot" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm">
                        <option value="right" {{ old('preferred_foot', $player->profile?->preferred_foot) === 'right' ? 'selected' : '' }}>Diestro</option>
                        <option value="left" {{ old('preferred_foot', $player->profile?->preferred_foot) === 'left' ? 'selected' : '' }}>Zurdo</option>
                        <option value="ambidextrous" {{ old('preferred_foot', $player->profile?->preferred_foot) === 'ambidextrous' ? 'selected' : '' }}>Ambidiestro</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="birth_date" class="block text-sm font-medium text-slate-300">Fecha de Nacimiento (Categoría)</label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $player->profile?->birth_date?->format('Y-m-d')) }}" class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                </div>

                <div>
                    <label for="nationality" class="block text-sm font-medium text-slate-300">Nacionalidad</label>
                    <input type="text" id="nationality" name="nationality" value="{{ old('nationality', $player->profile?->nationality ?? 'Colombiana') }}" class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="height_cm" class="block text-sm font-medium text-slate-300">Estatura (cm)</label>
                    <input type="number" id="height_cm" name="height_cm" value="{{ old('height_cm', $player->profile?->height_cm) }}" placeholder="Ej: 178" min="100" max="230" class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                </div>

                <div>
                    <label for="weight_kg" class="block text-sm font-medium text-slate-300">Peso (kg)</label>
                    <input type="number" id="weight_kg" name="weight_kg" value="{{ old('weight_kg', $player->profile?->weight_kg) }}" placeholder="Ej: 74" min="30" max="150" class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3">
            <a href="{{ route('players.cromo', $player) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-700 transition">Cancelar</a>
            <button type="submit" class="rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-bold text-slate-950 hover:bg-emerald-400 transition shadow-md">Guardar Perfil</button>
        </div>
    </form>
</div>
@endsection
