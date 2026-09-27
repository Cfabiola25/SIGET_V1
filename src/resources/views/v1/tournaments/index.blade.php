@extends('v1.layouts.app')

@section('title', 'Torneos')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Competencias</p><h1 class="siget-display mt-2 text-5xl">Torneos</h1></div>@auth @if (auth()->user()->role === 'organizer') <x-button href="{{ route('tournaments.create') }}" variant="coral">+ Nuevo torneo</x-button> @endif @endauth</div>
    <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($tournaments as $tournament)
            <x-card><div class="flex justify-between"><span class="rounded-full bg-siget-mint px-3 py-1 text-xs font-bold uppercase tracking-wider">{{ $tournament->status }}</span><span class="text-siget-coral">{{ $tournament->sport_type }}</span></div><h2 class="mt-8 text-xl font-semibold">{{ $tournament->name }}</h2><p class="mt-2 text-sm text-siget-muted">{{ $tournament->start_date->format('d M Y') }} — {{ $tournament->end_date->format('d M Y') }}</p><a class="mt-6 inline-block text-sm font-semibold underline decoration-siget-coral decoration-2 underline-offset-4" href="{{ route('tournaments.show', $tournament) }}">Ver torneo →</a></x-card>
        @empty <x-card class="md:col-span-2 lg:col-span-3"><p class="text-siget-muted">No hay torneos disponibles.</p></x-card> @endforelse
    </div>
@endsection
