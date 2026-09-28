@extends('v1.layouts.app')

@section('title', 'Editar torneo')

@section('content')
    <div class="mx-auto max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Administración</p><h1 class="siget-display mt-2 text-4xl">Editar torneo</h1>
        <form class="mt-8 space-y-5 rounded-xl border border-slate-800 bg-slate-900/60 p-6" method="POST" action="{{ route('tournaments.update', $tournament) }}">
            @csrf @method('PUT')
            <label class="block text-sm font-semibold">Nombre<input class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-white" name="name" value="{{ $tournament->name }}" required></label>
            <label class="block text-sm font-semibold">Deporte<select class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-white" name="sport_type" required>@foreach ($sports as $sport)<option value="{{ $sport->name }}" @selected($tournament->sport_type === $sport->name)>{{ $sport->name }}</option>@endforeach</select></label>
            <div class="grid gap-4 sm:grid-cols-2"><label class="block text-sm font-semibold">Fecha de inicio<input class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-white" name="start_date" type="date" value="{{ $tournament->start_date->format('Y-m-d') }}" required></label><label class="block text-sm font-semibold">Fecha de fin<input class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-white" name="end_date" type="date" value="{{ $tournament->end_date->format('Y-m-d') }}" required></label></div>
            <label class="block text-sm font-semibold">Estado<select class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-white" name="status"><option value="pending" @selected($tournament->status === 'pending')>Pendiente</option><option value="active" @selected($tournament->status === 'active')>Activo</option><option value="completed" @selected($tournament->status === 'completed')>Completado</option></select></label>
            <div class="flex items-center gap-4"><button class="rounded-lg bg-siget-mint px-5 py-3 text-sm font-bold text-siget-ink" type="submit">Guardar cambios</button><a class="text-sm font-semibold text-slate-300 underline underline-offset-4" href="{{ route('super-admin.dashboard') }}">Cancelar</a></div>
        </form>
    </div>
@endsection
