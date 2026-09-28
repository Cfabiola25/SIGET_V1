@extends('v1.layouts.app')

@section('title', 'Registrar Sede Deportiva')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <a href="{{ route('venues.index') }}" class="text-xs font-semibold uppercase tracking-wider text-emerald-400 hover:underline">
            ← Volver a Sedes
        </a>
        <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">Registrar Nueva Sede / Cancha</h1>
        <p class="text-sm text-slate-400">Introduce los datos del campo y sus coordenadas para que los planteles y árbitros puedan llegar con GPS.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-400">
            Revisa los campos requeridos antes de guardar.
        </div>
    @endif

    <form method="POST" action="{{ route('venues.store') }}" class="space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
        @csrf

        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-300">Nombre de la Sede / Cancha *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Ej: Complejo Deportivo La Bombonera" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                @error('name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="address" class="block text-sm font-medium text-slate-300">Dirección</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}" placeholder="Ej: Calle 45 # 12-34" class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    @error('address') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-slate-300">Ciudad / Municipio</label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="Ej: Bogotá" class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    @error('city') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="field_count" class="block text-sm font-medium text-slate-300">Número de Canchas Disponibles *</label>
                    <input type="number" id="field_count" name="field_count" value="{{ old('field_count', 1) }}" min="1" max="50" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    @error('field_count') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="surface_type" class="block text-sm font-medium text-slate-300">Superficie del Terreno *</label>
                    <select id="surface_type" name="surface_type" required class="mt-1.5 w-full px-3.5 py-2.5 text-sm">
                        <option value="grass" {{ old('surface_type') === 'grass' ? 'selected' : '' }}>Césped Natural</option>
                        <option value="synthetic" {{ old('surface_type') === 'synthetic' ? 'selected' : '' }}>Césped Sintético</option>
                        <option value="hybrid" {{ old('surface_type') === 'hybrid' ? 'selected' : '' }}>Híbrido</option>
                        <option value="indoor" {{ old('surface_type') === 'indoor' ? 'selected' : '' }}>Parquet / Coliseo</option>
                        <option value="dirt" {{ old('surface_type') === 'dirt' ? 'selected' : '' }}>Tierra / Arena</option>
                    </select>
                    @error('surface_type') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="border-t border-slate-800 pt-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-emerald-400">Geolocalización GPS (Opcional)</h2>
                <p class="text-xs text-slate-400">Permite a los jugadores abrir la ruta directamente en su app de navegación.</p>

                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-slate-300">Latitud</label>
                        <input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="Ej: 4.6097" class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                        @error('latitude') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="longitude" class="block text-sm font-medium text-slate-300">Longitud</label>
                        <input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="Ej: -74.0817" class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                        @error('longitude') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label for="maps_url" class="block text-sm font-medium text-slate-300">O Enlace Directo de Google Maps / Waze</label>
                    <input type="url" id="maps_url" name="maps_url" value="{{ old('maps_url') }}" placeholder="https://maps.app.goo.gl/..." class="mt-1.5 w-full px-3.5 py-2.5 text-sm" />
                    @error('maps_url') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="border-t border-slate-800 pt-4">
                <label for="notes" class="block text-sm font-medium text-slate-300">Observaciones o Instrucciones de Ingreso</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Ej: Entrada por el portón norte. Se prohíbe el uso de tapones metálicos." class="mt-1.5 w-full px-3.5 py-2 text-sm">{{ old('notes') }}</textarea>
                @error('notes') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3">
            <a href="{{ route('venues.index') }}" class="rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-700 transition">Cancelar</a>
            <button type="submit" class="rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-bold text-slate-950 hover:bg-emerald-400 transition shadow-md">Guardar Sede</button>
        </div>
    </form>
</div>
@endsection
