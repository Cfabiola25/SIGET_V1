@extends('v1.layouts.app')

@section('title', 'Panel')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Centro de control</p><h1 class="siget-display mt-2 text-5xl">Hola, {{ auth()->user()->name }}.</h1></div><span class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-400">{{ auth()->user()->role }}</span></div>
    @if (auth()->user()->role === 'admin')
        <div class="mt-10 grid gap-4 sm:grid-cols-2"><x-card eyebrow="Torneos" title="{{ $tournamentsCount }}"><p class="text-sm text-siget-muted">Competiciones registradas</p></x-card><x-card eyebrow="Equipos" title="{{ $teamsCount }}"><p class="text-sm text-siget-muted">Participantes en SIGET</p></x-card></div>
    @elseif (auth()->user()->role === 'organizer')
        <x-card class="mt-10" title="Mis torneos" eyebrow="Organización"><div class="grid gap-3 sm:grid-cols-2">@forelse ($tournaments as $tournament)<a class="rounded-xl bg-siget-paper p-4 transition hover:bg-siget-mint" href="{{ route('tournaments.show', $tournament) }}"><p class="font-semibold">{{ $tournament->name }}</p><p class="mt-1 text-sm text-siget-muted">{{ $tournament->sport_type }} · {{ $tournament->status }}</p></a>@empty <p class="text-siget-muted">Aún no has creado torneos.</p>@endforelse</div></x-card>
    @elseif (auth()->user()->role === 'captain')
        <x-card class="mt-10" title="Mis equipos" eyebrow="Capitanía"><div class="grid gap-3 sm:grid-cols-2">@forelse ($teams as $team)<a class="rounded-xl bg-siget-paper p-4 transition hover:bg-siget-mint" href="{{ route('teams.show', $team) }}"><p class="font-semibold">{{ $team->name }}</p><p class="mt-1 text-sm text-siget-muted">{{ $team->tournament->name }}</p></a>@empty <p class="text-siget-muted">Aún no tienes equipos inscritos.</p>@endforelse</div></x-card>
    @else
        <x-card class="mt-10" title="Mi ficha" eyebrow="Jugador"><div class="space-y-3">@forelse ($players as $player)<div class="flex justify-between rounded-xl bg-siget-paper p-4"><span class="font-semibold">{{ $player->name }}</span><span class="text-siget-muted">{{ $player->team->name }} · #{{ $player->jersey_number }}</span></div>@empty <p class="text-siget-muted">Tu ficha aún no está vinculada a un equipo.</p>@endforelse</div></x-card>
    @endif
@endsection
