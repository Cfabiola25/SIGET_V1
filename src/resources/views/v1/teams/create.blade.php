@extends('v1.layouts.app')

@section('title', 'Inscribir Nuevo Equipo')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('teams.index') }}" class="text-xs font-semibold uppercase tracking-wider text-[#057a55] hover:underline flex items-center gap-1">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a Equipos
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Inscribir Nuevo Club / Equipo</h1>
            <p class="text-xs text-slate-500">Registra un club participante para habilitar su nómina y fixture.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-2xs">
        <form method="POST" action="{{ route('teams.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="tournament_id" class="block text-xs font-semibold text-slate-700 mb-1.5">Torneo Oficial *</label>
                <select name="tournament_id" id="tournament_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                    @foreach ($tournaments as $tournament)
                        <option value="{{ $tournament->id }}" {{ old('tournament_id') == $tournament->id ? 'selected' : '' }}>
                            {{ $tournament->name }} ({{ $tournament->season ?? '2024' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre del Club / Equipo *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}" 
                       placeholder="Ej: Metro City Strikers" 
                       required 
                       class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none placeholder:text-slate-400">
            </div>

            <div>
                <label for="logo_path" class="block text-xs font-semibold text-slate-700 mb-1.5">URL o Ruta del Escudo / Logo (Opcional)</label>
                <input type="text" 
                       name="logo_path" 
                       id="logo_path" 
                       value="{{ old('logo_path') }}" 
                       placeholder="https://... o /logos/team.png" 
                       class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none placeholder:text-slate-400">
            </div>

            <div class="border-t border-slate-100 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('teams.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#057a55] px-6 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#046c4b] transition flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Inscribir Equipo</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
