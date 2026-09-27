@extends('v1.layouts.app')

@section('title', 'Equipos')

@section('content')
    <div class="flex items-end justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Participantes</p><h1 class="siget-display mt-2 text-5xl">Equipos</h1></div>@auth @if (auth()->user()->role === 'captain') <x-button href="{{ route('teams.create') }}">+ Inscribir equipo</x-button> @endif @endauth</div>
    <x-card class="mt-8 overflow-hidden p-0"><div class="overflow-x-auto"><table class="w-full min-w-[38rem] text-left text-sm"><thead class="bg-slate-950/80 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-4">Equipo</th><th class="px-5 py-4">Torneo</th><th class="px-5 py-4">Capitán</th><th class="px-5 py-4">Estado</th></tr></thead><tbody class="divide-y divide-slate-800/60">@forelse ($teams as $team)<tr class="transition-colors hover:bg-slate-800/40"><td class="px-5 py-4 font-semibold"><a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a></td><td class="px-5 py-4 text-slate-400">{{ $team->tournament->name }}</td><td class="px-5 py-4 text-slate-400">{{ $team->captain->name }}</td><td class="px-5 py-4"><span class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">{{ $team->status }}</span></td></tr>@empty<tr><td class="px-5 py-8 text-slate-400" colspan="4">No hay equipos inscritos.</td></tr>@endforelse</tbody></table></div></x-card>
@endsection
