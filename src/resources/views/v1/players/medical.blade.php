@extends('v1.layouts.app')

@section('title', 'Ficha Médica: ' . $player->name)

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <a href="{{ route('players.cromo', $player) }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
            ← Volver a Ficha Deportiva
        </a>
        <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">Ficha Médica & Exoneración</h1>
        <p class="text-sm text-slate-400">Datos privados y de emergencia de {{ $player->name }} ({{ $player->team->name }}).</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-400">
            Revisa los campos requeridos antes de guardar.
        </div>
    @endif

    <form method="POST" action="{{ route('players.medical.update', $player) }}" class="space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="blood_type" class="block text-sm font-medium text-slate-300">Grupo Sanguíneo y Factor RH *</label>
                    <select id="blood_type" name="blood_type" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm">
                        @foreach(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $type)
                            <option value="{{ $type }}" {{ old('blood_type', $medical->blood_type) === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="health_provider" class="block text-sm font-medium text-slate-300">Afiliación a Salud (EPS / Seguro) *</label>
                    <input type="text" id="health_provider" name="health_provider" value="{{ old('health_provider', $medical->health_provider) }}" placeholder="Ej: Sanitas, Sura, Nueva EPS" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                </div>
            </div>

            <div>
                <label for="allergies" class="block text-sm font-medium text-slate-300">Alergias o Antecedentes Médicos Relevantes</label>
                <textarea id="allergies" name="allergies" rows="2" placeholder="Ej: Alérgico a penicilina, asma leve, etc. (O escribe 'Ninguna')" class="mt-1.5 w-full px-3.5 py-2 text-sm">{{ old('allergies', $medical->allergies) }}</textarea>
            </div>

            <div class="border-t border-slate-800 pt-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Contacto de Emergencia Inmediato</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="emergency_contact_name" class="block text-sm font-medium text-slate-300">Nombre del Contacto *</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $medical->emergency_contact_name) }}" placeholder="Ej: María Gómez (Madre)" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    </div>

                    <div>
                        <label for="emergency_contact_phone" class="block text-sm font-medium text-slate-300">Teléfono de Emergencia *</label>
                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $medical->emergency_contact_phone) }}" placeholder="Ej: 3001234567" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-amber-400">Firma de Exoneración de Responsabilidad</h2>
                <div class="mt-2 rounded-xl border border-slate-800 bg-slate-950 p-4 text-xs text-slate-400 leading-relaxed">
                    Declaro bajo gravedad de juramento que me encuentro en condiciones físicas y de salud óptimas para la práctica competitiva del fútbol en este torneo. Exonero a los organizadores, árbitros y sedes de toda responsabilidad legal derivada de incidentes deportivos imprevistos.
                </div>

                <div class="mt-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="waiver_signed" value="0">
                        <input type="checkbox" name="waiver_signed" value="1"
                               {{ old('waiver_signed', $medical->waiver_signed) ? 'checked' : '' }}
                               required
                               class="size-5 rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500/20">
                        <span class="text-xs font-bold text-white">Acepto y firmo digitalmente la exoneración de responsabilidades.</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3">
            <a href="{{ route('players.cromo', $player) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-700 transition">Cancelar</a>
            <button type="submit" class="rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-bold text-slate-950 hover:bg-emerald-400 transition shadow-md">Guardar Ficha Médica</button>
        </div>
    </form>
</div>
@endsection
