@extends('v1.layouts.app')

@section('title', 'Editar Equipo: ' . $team->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('teams.show', $team) }}" class="text-xs font-semibold uppercase tracking-wider text-[#057a55] hover:underline flex items-center gap-1">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al Equipo
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Editar Información del Equipo</h1>
            <p class="text-xs text-slate-500">Actualiza los datos institucionales de {{ $team->name }}.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-2xs">
        <form method="POST" action="{{ route('teams.update', $team) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre del Club / Equipo *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $team->name) }}" 
                       required 
                       class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
            </div>

            <div>
                <label for="logo_path" class="block text-xs font-semibold text-slate-700 mb-1.5">URL o Ruta del Escudo (Opcional)</label>
                <input type="text" 
                       name="logo_path" 
                       id="logo_path" 
                       value="{{ old('logo_path', $team->logo_path) }}" 
                       class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
            </div>

            <div class="border-t border-slate-100 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('teams.show', $team) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#057a55] px-6 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#046c4b] transition flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Cambios</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
