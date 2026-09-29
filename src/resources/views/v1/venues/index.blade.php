@extends('v1.layouts.app')

@section('title', 'Sedes Deportivas y Canchas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sedes Deportivas & Canchas</h1>
            <p class="text-xs text-slate-500 mt-0.5">Campos de juego habilitados con geolocalización GPS para planteles, árbitros y autoridades.</p>
        </div>
        @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
            <a href="{{ route('venues.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#057a55] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#046c4b]">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Registrar Nueva Sede</span>
            </a>
        @endif
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($venues->isEmpty())
        <div class="rounded-2xl border border-slate-200/90 bg-white p-12 text-center shadow-2xs">
            <span class="text-4xl block">📍</span>
            <h2 class="mt-3 text-base font-bold text-slate-900">No hay sedes registradas</h2>
            <p class="mt-1 text-xs text-slate-500">Registra las canchas con coordenadas GPS para asignarlas en el programador de partidos.</p>
            @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
                <a href="{{ route('venues.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#057a55] px-4 py-2 text-xs font-semibold text-white hover:bg-[#046c4b] transition shadow-2xs">
                    Registrar Sede
                </a>
            @endif
        </div>
    @else
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($venues as $venue)
                <div class="flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs hover:shadow-xs transition">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="inline-block rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-700">
                                    {{ ucfirst($venue->surface_type) }}
                                </span>
                                <h2 class="mt-2 text-lg font-bold text-slate-900 hover:text-[#057a55] transition">
                                    <a href="{{ route('venues.show', $venue) }}">{{ $venue->name }}</a>
                                </h2>
                            </div>
                            <span class="grid size-9 place-items-center rounded-lg bg-emerald-50 text-xs font-bold text-emerald-800 border border-emerald-100">
                                {{ $venue->field_count }} <span class="text-[9px] -mt-1 font-normal text-emerald-600">can.</span>
                            </span>
                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            {{ $venue->address ?? 'Dirección no especificada' }}
                            @if($venue->city) • <span class="text-slate-700 font-semibold">{{ $venue->city }}</span> @endif
                        </p>

                        @if ($venue->notes)
                            <p class="mt-3 text-[11px] italic text-slate-400 line-clamp-2">{{ $venue->notes }}</p>
                        @endif
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-4 flex items-center justify-between gap-2">
                        @if ($venue->navigation_url)
                            <a href="{{ $venue->navigation_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#057a55] hover:underline transition">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span>Abrir en GPS</span>
                            </a>
                        @else
                            <span class="text-[11px] text-slate-400">Sin geolocalización</span>
                        @endif

                        <div class="flex items-center gap-2">
                            <a href="{{ route('venues.show', $venue) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                Ver Detalle
                            </a>
                            @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
                                <a href="{{ route('venues.edit', $venue) }}" class="text-xs text-slate-400 hover:text-slate-700 transition">Editar</a>
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
