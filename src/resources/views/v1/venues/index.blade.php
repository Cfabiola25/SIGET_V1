@extends('v1.layouts.app')

@section('title', 'Sedes Deportivas y Canchas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Infraestructura Deportiva</div>
            <h1 class="text-2xl font-black text-white md:text-3xl">Sedes y Canchas con Geolocalización</h1>
            <p class="text-sm text-slate-400">Campos de juego habilitados con enlace GPS para jugadores, árbitros y aficionados.</p>
        </div>
        @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
            <a href="{{ route('venues.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-md transition hover:bg-emerald-400">
                <span>+</span> Registrar Nueva Sede
            </a>
        @endif
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-400">
            {{ session('status') }}
        </div>
    @endif

    @if ($venues->isEmpty())
        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-12 text-center">
            <span class="text-4xl">📍</span>
            <h2 class="mt-3 text-lg font-bold text-white">No hay sedes registradas</h2>
            <p class="mt-1 text-sm text-slate-400">Registra las canchas con coordenadas GPS para asignarlas a los fixtures.</p>
            @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
                <a href="{{ route('venues.create') }}" class="mt-4 inline-block rounded-xl bg-emerald-500 px-4 py-2 text-sm font-bold text-slate-950 hover:bg-emerald-400">
                    Registrar Sede
                </a>
            @endif
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($venues as $venue)
                <div class="flex flex-col justify-between rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm transition hover:border-slate-700">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="rounded-md border border-slate-700 bg-slate-800 px-2 py-0.5 text-xs font-semibold uppercase text-slate-300">
                                    {{ ucfirst($venue->surface_type) }}
                                </span>
                                <h2 class="mt-2 text-xl font-bold text-white hover:text-emerald-400 transition">
                                    <a href="{{ route('venues.show', $venue) }}">{{ $venue->name }}</a>
                                </h2>
                            </div>
                            <span class="grid size-8 place-items-center rounded-lg bg-emerald-500/10 text-sm font-black text-emerald-400">
                                {{ $venue->field_count }} <span class="text-[9px] block">can.</span>
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-slate-400">
                            {{ $venue->address ?? 'Dirección no especificada' }}
                            @if($venue->city) • <span class="text-slate-300 font-medium">{{ $venue->city }}</span> @endif
                        </p>

                        @if ($venue->notes)
                            <p class="mt-3 text-xs italic text-slate-500 line-clamp-2">{{ $venue->notes }}</p>
                        @endif
                    </div>

                    <div class="mt-6 border-t border-slate-800/80 pt-4 flex items-center justify-between gap-2">
                        @if ($venue->navigation_url)
                            <a href="{{ $venue->navigation_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 hover:text-emerald-300 transition">
                                <span>🗺️</span> Abrir en GPS / Maps
                            </a>
                        @else
                            <span class="text-xs text-slate-500">Sin geolocalización</span>
                        @endif

                        <div class="flex items-center gap-2">
                            <a href="{{ route('venues.show', $venue) }}" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:bg-slate-700 transition">
                                Ver Detalle
                            </a>
                            @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
                                <a href="{{ route('venues.edit', $venue) }}" class="text-xs text-slate-400 hover:text-slate-200 transition">Editar</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $venues->links() }}
        </div>
    @endif
</div>
@endsection
