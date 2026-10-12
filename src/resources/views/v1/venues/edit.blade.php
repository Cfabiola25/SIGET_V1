@extends('v1.layouts.app')

@section('title', 'Editar Sede: ' . $venue->name)

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <a href="{{ route('venues.show', $venue) }}" class="text-xs font-semibold uppercase tracking-wider text-[#2b4e72] hover:underline flex items-center gap-1">
            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a {{ $venue->name }}
        </a>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">Editar Sede Deportiva</h1>
        <p class="text-xs text-slate-500">Actualiza la información del complejo deportivo, canchas disponibles y datos de geolocalización.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            Revisa los campos requeridos antes de guardar los cambios.
        </div>
    @endif

    <form method="POST" action="{{ route('venues.update', $venue) }}" class="space-y-6 rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-2xs">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nombre de la Sede / Cancha *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $venue->name) }}" placeholder="Ej: Complejo Deportivo Principal" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none" />
                @error('name') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="address" class="block text-xs font-semibold text-slate-700 mb-1">Dirección</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $venue->address) }}" placeholder="Ej: Calle 45 # 12-34" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none" />
                    @error('address') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="city" class="block text-xs font-semibold text-slate-700 mb-1">Ciudad / Municipio</label>
                    <input type="text" id="city" name="city" value="{{ old('city', $venue->city) }}" placeholder="Ej: Bogotá" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none" />
                    @error('city') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="field_count" class="block text-xs font-semibold text-slate-700 mb-1">Número de Canchas Disponibles *</label>
                    <input type="number" id="field_count" name="field_count" value="{{ old('field_count', $venue->field_count) }}" min="1" max="50" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none" />
                    @error('field_count') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="surface_type" class="block text-xs font-semibold text-slate-700 mb-1">Superficie del Terreno *</label>
                    <select id="surface_type" name="surface_type" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                        <option value="grass" {{ old('surface_type', $venue->surface_type) === 'grass' ? 'selected' : '' }}>Césped Natural</option>
                        <option value="synthetic" {{ old('surface_type', $venue->surface_type) === 'synthetic' ? 'selected' : '' }}>Césped Sintético</option>
                        <option value="hybrid" {{ old('surface_type', $venue->surface_type) === 'hybrid' ? 'selected' : '' }}>Híbrido</option>
                        <option value="indoor" {{ old('surface_type', $venue->surface_type) === 'indoor' ? 'selected' : '' }}>Parquet / Coliseo</option>
                        <option value="dirt" {{ old('surface_type', $venue->surface_type) === 'dirt' ? 'selected' : '' }}>Tierra / Arena</option>
                    </select>
                    @error('surface_type') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Geolocalización GPS (Opcional)</h2>
                <p class="text-[11px] text-slate-500">Permite a los jugadores y árbitros abrir la ruta directamente en su app de navegación.</p>

                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="latitude" class="block text-xs font-semibold text-slate-700 mb-1">Latitud</label>
                        <input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude', $venue->latitude) }}" placeholder="Ej: 4.6097" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none" />
                        @error('latitude') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="longitude" class="block text-xs font-semibold text-slate-700 mb-1">Longitud</label>
                        <input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude', $venue->longitude) }}" placeholder="Ej: -74.0817" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none" />
                        @error('longitude') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label for="maps_url" class="block text-xs font-semibold text-slate-700 mb-1">O Enlace Directo de Google Maps / Waze</label>
                    <input type="url" id="maps_url" name="maps_url" value="{{ old('maps_url', $venue->maps_url) }}" placeholder="https://maps.app.goo.gl/..." class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none" />
                    @error('maps_url') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1">Observaciones o Instrucciones de Acceso</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Ej: Ingreso por puerta 3. Se prohíbe el uso de tapones de aluminio." class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">{{ old('notes', $venue->notes) }}</textarea>
                @error('notes') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('venues.show', $venue) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Cancelar</a>
            <button type="submit" class="rounded-lg bg-[#2b4e72] px-5 py-2 text-xs font-semibold text-white hover:bg-[#2790b0] transition shadow-2xs">Actualizar Sede</button>
        </div>
    </form>
</div>
@endsection
