@extends('v1.layouts.app')

@section('title', 'Cargar Marcador: ' . $match->homeTeam->name . ' vs ' . $match->awayTeam->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('matches.show', $match) }}" class="text-xs font-semibold uppercase tracking-wider text-[#057a55] hover:underline flex items-center gap-1">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al Partido
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Registrar Marcador Oficial</h1>
            <p class="text-xs text-slate-500">Torneo: <span class="font-semibold text-slate-700">{{ $match->tournament->name }}</span> • Fecha: {{ $match->match_date->format('d/m/Y H:i') }}</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 border border-slate-200">
            Fase Regular
        </span>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-medium text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-2xs">
        <form method="POST" action="{{ route('matches.update', $match) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Teams & Scores Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                <!-- Home Team Box -->
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 text-center space-y-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Equipo Local</span>
                    <h3 class="text-base font-bold text-slate-900 truncate">{{ $match->homeTeam->name }}</h3>
                    <div class="flex justify-center">
                        <input type="number" 
                               name="home_score" 
                               min="0" 
                               value="{{ old('home_score', $match->home_score ?? 0) }}" 
                               required 
                               class="w-24 text-center text-3xl font-black font-mono rounded-xl border border-slate-300 bg-white py-2 text-slate-900 shadow-inner focus:border-[#057a55] focus:ring-1 focus:ring-[#057a55] focus:outline-none">
                    </div>
                </div>

                <!-- Away Team Box -->
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 text-center space-y-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Equipo Visitante</span>
                    <h3 class="text-base font-bold text-slate-900 truncate">{{ $match->awayTeam->name }}</h3>
                    <div class="flex justify-center">
                        <input type="number" 
                               name="away_score" 
                               min="0" 
                               value="{{ old('away_score', $match->away_score ?? 0) }}" 
                               required 
                               class="w-24 text-center text-3xl font-black font-mono rounded-xl border border-slate-300 bg-white py-2 text-slate-900 shadow-inner focus:border-[#057a55] focus:ring-1 focus:ring-[#057a55] focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Match Status Selector -->
            <div>
                <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">Estado del Encuentro</label>
                <select name="status" id="status" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 font-medium focus:border-[#057a55] focus:outline-none">
                    <option value="played" {{ old('status', $match->status) === 'played' ? 'selected' : '' }}>Finalizado (Jugado con resultado oficial)</option>
                    <option value="scheduled" {{ old('status', $match->status) === 'scheduled' ? 'selected' : '' }}>Programado</option>
                    <option value="in_progress" {{ old('status', $match->status) === 'in_progress' ? 'selected' : '' }}>En Progreso / Disputándose</option>
                    <option value="suspended" {{ old('status', $match->status) === 'suspended' ? 'selected' : '' }}>Suspendido</option>
                </select>
            </div>

            <div class="border-t border-slate-100 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('matches.show', $match) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#057a55] px-6 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-[#046c4b] transition flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Marcador</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
