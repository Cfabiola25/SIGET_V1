@extends('v1.layouts.app')

@section('title', 'Panel Super Admin')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Administracion SaaS</p>
            <h1 class="siget-display mt-2 text-5xl">Panel Super Admin</h1>
        </div>
        <span class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-400">{{ auth()->user()->role }}</span>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-siget-mint bg-siget-mint/40 px-4 py-3 text-sm font-semibold">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-siget-coral/40 bg-siget-coral/10 px-4 py-3 text-sm text-siget-ink">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mt-10 grid gap-4 sm:grid-cols-2">
        <x-card eyebrow="Total torneos" title="{{ $tournaments->count() }}"><p class="text-sm text-siget-muted">Competiciones bajo la cuenta SaaS</p></x-card>
        <x-card eyebrow="Total admins" title="{{ $admins->count() }}"><p class="text-sm text-siget-muted">Administradores disponibles</p></x-card>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md">
            <div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Inventario</p><h2 class="mt-2 text-2xl font-semibold">Torneos</h2></div><span class="text-sm text-siget-muted">{{ $tournaments->count() }} registrados</span></div>
            <div class="mt-6 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="border-b border-siget-ink/10 text-xs uppercase tracking-wider text-siget-muted"><tr><th class="pb-3 pr-4">Nombre</th><th class="pb-3">Admin asignado</th><th class="pb-3"></th></tr></thead><tbody class="divide-y divide-siget-ink/10">@forelse ($tournaments as $tournament)<tr><td class="py-4 pr-4 font-semibold">{{ $tournament->name }}</td><td class="py-4 text-siget-muted">{{ $tournament->admin?->name ?? 'Sin asignar' }}</td><td class="py-4 text-right"><a class="font-semibold text-siget-coral underline decoration-2 underline-offset-4" href="{{ route('tournaments.show', $tournament) }}">Ver</a></td></tr>@empty<tr><td class="py-5 text-siget-muted" colspan="3">Todavia no hay torneos.</td></tr>@endforelse</tbody></table></div>
        </section>

        <div class="space-y-6">
            <section class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Nuevo admin</p><h2 class="mt-2 text-2xl font-semibold">Crear administrador</h2>
                <form class="mt-5 space-y-4" method="POST" action="{{ route('super-admin.admins.store') }}">@csrf <input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="name" placeholder="Nombre" required><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="email" type="email" placeholder="Correo" required><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="password" type="password" placeholder="Contrasena" required><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="password_confirmation" type="password" placeholder="Confirmar contrasena" required><button class="w-full rounded-lg bg-emerald-500 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-emerald-400" type="submit">Crear admin</button></form>
            </section>
            <section class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Nuevo torneo</p><h2 class="mt-2 text-2xl font-semibold">Asignar responsable</h2>
                <form class="mt-5 space-y-4" method="POST" action="{{ route('super-admin.tournaments.store') }}">@csrf <input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="name" placeholder="Nombre" required><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="sport_type" placeholder="Deporte" required><div class="grid gap-4 sm:grid-cols-2"><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="start_date" type="date" required><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="end_date" type="date" required></div><select class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="admin_id" required><option value="">Selecciona un admin</option>@foreach ($admins as $admin)<option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>@endforeach</select><button class="w-full rounded-lg bg-emerald-500 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-emerald-400" type="submit">Crear torneo</button></form>
            </section>
        </div>
    </div>
@endsection
