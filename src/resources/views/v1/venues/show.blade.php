@extends('v1.layouts.app')

@section('title', 'Sede: ' . $venue->name)

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('venues.index') }}" class="text-xs font-semibold uppercase tracking-wider text-[#057a55] hover:underline flex items-center gap-1">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a Sedes
            </a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $venue->name }}</h1>
            <p class="text-xs text-slate-500">{{ $venue->address ?? 'Dirección no registrada' }} @if($venue->city) • {{ $venue->city }} @endif</p>
        </div>

        <div class="flex items-center gap-2.5">
            @if ($venue->navigation_url)
                <a href="{{ $venue->navigation_url }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-lg bg-[#057a55] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#046c4b]">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    <span>Abrir en Google Maps</span>
                </a>
            @endif
            @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
                <form method="POST" action="{{ route('venues.destroy', $venue) }}" onsubmit="return confirm('¿Eliminar esta sede?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-rose-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition shadow-2xs">
                        Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <!-- Ficha técnica de la sede -->
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Canchas Reglamentarias</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $venue->field_count }}</div>
            <span class="text-[11px] text-slate-400">Capacidad para partidos simultáneos</span>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Superficie de Juego</span>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ ucfirst($venue->surface_type) }}</div>
            <span class="text-[11px] text-emerald-700 font-medium">Habilitada oficialmente</span>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Partidos Programados</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $venue->matches->count() }}</div>
            <span class="text-[11px] text-slate-400">Encuentros en fixture</span>
        </div>
    </div>

    @if ($venue->notes)
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Instrucciones y Normas de la Cancha</h2>
            <p class="mt-1.5 text-xs text-slate-600 leading-relaxed">{{ $venue->notes }}</p>
        </div>
    @endif

    <!-- Partidos programados en la sede -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
        <h2 class="text-base font-bold text-slate-900">Partidos Asignados en esta Sede</h2>
        @if ($venue->matches->isEmpty())
            <p class="mt-3 text-xs text-slate-400">Aún no hay partidos programados en este escenario deportivo.</p>
        @else
            <div class="mt-4 divide-y divide-slate-100">
                @foreach ($venue->matches as $match)
                    <div class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-emerald-700">{{ $match->tournament->name }}</span>
                            <div class="font-bold text-slate-900 text-sm">{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }}</div>
                            <span class="text-xs text-slate-400 font-mono">{{ $match->match_date->format('d/m/Y H:i') }} • {{ $match->field_number ? 'Cancha ' . $match->field_number : 'Cancha Principal' }}</span>
                        </div>
                        <a href="{{ route('matches.show', $match) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs self-start sm:self-auto">
                            Ver Partido
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
