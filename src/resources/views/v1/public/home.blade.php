@extends('v1.layouts.app')

@section('title', 'Inicio')

@section('content')
    <section class="siget-grid relative overflow-hidden rounded-[2rem] border border-siget-ink/10 px-6 py-12 sm:px-10 lg:px-16 lg:py-20">
        <div class="relative max-w-3xl siget-rise">
            <p class="mb-5 text-xs font-bold uppercase tracking-[0.24em] text-siget-coral">Competición, sin ruido</p>
            <h1 class="siget-display max-w-3xl text-5xl leading-[0.98] tracking-tight text-siget-ink sm:text-7xl">El pulso de cada torneo, en un solo lugar<span class="text-siget-coral">.</span></h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-siget-muted">Fixture, resultados y posiciones para que equipos, capitanes y aficionados sigan el juego en tiempo real.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button href="{{ route('public.tournament') }}" variant="coral">Ver torneo activo <span aria-hidden="true">↗</span></x-button>
                <x-button href="{{ route('login') }}" variant="secondary">Entrar al panel</x-button>
            </div>
        </div>
        <div class="mt-12 grid max-w-2xl grid-cols-2 gap-3 sm:grid-cols-3 lg:absolute lg:bottom-10 lg:right-12 lg:mt-0 lg:w-[38%]">
            <div class="rounded-xl border border-slate-800 bg-slate-900/80 p-5 text-white"><p class="font-mono text-3xl font-extrabold">24/7</p><p class="mt-8 text-sm text-slate-400">Marcadores al día</p></div>
            <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-5 text-amber-300"><p class="font-mono text-3xl font-extrabold">1</p><p class="mt-8 text-sm text-amber-200/70">Fuente de verdad</p></div>
            <div class="col-span-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-5 text-emerald-400 sm:col-span-1"><p class="font-mono text-3xl font-extrabold">∞</p><p class="mt-8 text-sm text-emerald-300/70">Partidos posibles</p></div>
        </div>
    </section>

    <section class="mt-14">
        <div class="flex items-end justify-between gap-4">
            <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">En cartelera</p><h2 class="mt-2 text-3xl font-semibold tracking-tight">Torneos que están pasando</h2></div>
            <a class="hidden text-sm font-semibold text-siget-coral hover:text-siget-ink sm:block" href="{{ route('tournaments.index') }}">Ver todos →</a>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @forelse ($tournaments as $tournament)
                <x-card class="siget-rise siget-delay-{{ $loop->iteration }}">
                    <div class="flex items-start justify-between"><span class="rounded-full bg-siget-mint px-3 py-1 text-xs font-bold uppercase tracking-wider text-siget-ink">{{ $tournament->status }}</span><span class="text-2xl text-siget-coral">↗</span></div>
                    <h3 class="mt-8 text-xl font-semibold">{{ $tournament->name }}</h3>
                    <p class="mt-2 text-sm text-siget-muted">{{ $tournament->sport_type }} · {{ $tournament->teams_count }} equipos · {{ $tournament->matches_count }} partidos</p>
                    <a class="mt-6 inline-block text-sm font-semibold text-siget-ink underline decoration-siget-coral decoration-2 underline-offset-4" href="{{ route('public.tournament') }}">Abrir torneo</a>
                </x-card>
            @empty
                <x-card class="md:col-span-3"><p class="text-siget-muted">Todavía no hay torneos publicados.</p></x-card>
            @endforelse
        </div>
    </section>
@endsection
