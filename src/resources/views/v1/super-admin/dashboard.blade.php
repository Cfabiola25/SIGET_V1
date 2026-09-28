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
        <x-card eyebrow="Admins activos" title="{{ $admins->where('is_active', true)->count() }}"><p class="text-sm text-siget-muted">{{ $admins->where('is_active', false)->count() }} desactivados</p></x-card>
    </div>

    <section class="mt-10 overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md">
        <div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Accesos</p><h2 class="mt-2 text-2xl font-semibold">Administradores</h2></div><span class="text-sm text-siget-muted">{{ $admins->count() }} registrados</span></div>
        <div class="mt-6 overflow-x-auto"><table class="w-full min-w-[64rem] text-left text-sm"><thead class="border-b border-siget-ink/10 text-xs uppercase tracking-wider text-siget-muted"><tr><th class="pb-3 pr-4">Nombre</th><th class="pb-3 pr-4">Correo</th><th class="pb-3 pr-4">Torneos</th><th class="pb-3 pr-4">Estado</th><th class="pb-3">Acciones</th></tr></thead><tbody class="divide-y divide-siget-ink/10">
            @forelse ($admins as $admin)
                <tr><td class="py-4 pr-4 font-semibold">{{ $admin->name }}</td><td class="py-4 pr-4 text-siget-muted">{{ $admin->email }}</td><td class="py-4 pr-4">{{ $admin->managed_tournaments_count }}</td><td class="py-4 pr-4"><span class="rounded-full px-3 py-1 text-xs font-bold uppercase {{ $admin->is_active ? 'bg-siget-mint text-siget-ink' : 'bg-slate-800 text-slate-300' }}">{{ $admin->is_active ? 'Activo' : 'Desactivado' }}</span></td><td class="py-4"><div class="flex items-center gap-3">
                    <details class="relative"><summary class="cursor-pointer font-semibold text-siget-coral underline decoration-2 underline-offset-4">Editar</summary><form class="absolute right-0 z-10 mt-2 w-72 space-y-3 rounded-lg border border-slate-700 bg-slate-950 p-4 shadow-xl" method="POST" action="{{ route('super-admin.admins.update', $admin) }}">@csrf @method('PUT')<label class="block text-xs font-semibold text-slate-300">Nombre<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white" name="name" value="{{ $admin->name }}" required></label><label class="block text-xs font-semibold text-slate-300">Correo<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white" name="email" type="email" value="{{ $admin->email }}" required></label><button class="w-full rounded-lg bg-siget-mint px-3 py-2 text-sm font-bold text-siget-ink" type="submit">Guardar</button></form></details>
                    <details class="relative"><summary class="cursor-pointer font-semibold text-siget-coral underline decoration-2 underline-offset-4">Clave</summary><form class="absolute right-0 z-10 mt-2 w-72 space-y-3 rounded-lg border border-slate-700 bg-slate-950 p-4 shadow-xl" method="POST" action="{{ route('super-admin.admins.password', $admin) }}">@csrf @method('PUT')<label class="block text-xs font-semibold text-slate-300">Nueva contraseña<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white" name="password" type="password" minlength="8" required></label><label class="block text-xs font-semibold text-slate-300">Confirmar contraseña<input class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white" name="password_confirmation" type="password" minlength="8" required></label><button class="w-full rounded-lg bg-siget-mint px-3 py-2 text-sm font-bold text-siget-ink" type="submit">Restablecer</button></form></details>
                    <form method="POST" action="{{ route('super-admin.admins.status', $admin) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $admin->is_active ? 0 : 1 }}"><button class="text-sm font-semibold underline decoration-2 underline-offset-4 {{ $admin->is_active ? 'text-siget-coral' : 'text-siget-mint' }}" type="submit">{{ $admin->is_active ? 'Desactivar' : 'Reactivar' }}</button></form>
                </div></td></tr>
            @empty
                <tr><td class="py-5 text-siget-muted" colspan="5">Todavía no hay administradores.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>

    <div class="mt-10 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md">
            <div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Inventario</p><h2 class="mt-2 text-2xl font-semibold">Torneos</h2></div><span class="text-sm text-siget-muted">{{ $tournaments->count() }} registrados</span></div>
            <div class="mt-6 overflow-x-auto"><table class="w-full min-w-[38rem] text-left text-sm"><thead class="border-b border-siget-ink/10 text-xs uppercase tracking-wider text-siget-muted"><tr><th class="pb-3 pr-4">Torneo</th><th class="pb-3 pr-4">Deporte</th><th class="pb-3 pr-4">Admin asignado</th><th class="pb-3">Acciones</th></tr></thead><tbody class="divide-y divide-siget-ink/10">
                @forelse ($tournaments as $tournament)
                    <tr><td class="py-4 pr-4 font-semibold">{{ $tournament->name }}<span class="mt-1 block text-xs font-normal text-siget-muted">{{ $tournament->start_date->format('d/m/Y') }} · {{ $tournament->status }}</span></td><td class="py-4 pr-4 text-siget-muted">{{ $tournament->sport_type }}</td><td class="py-4 pr-4 text-siget-muted">{{ $tournament->admin?->name ?? 'Sin asignar' }}</td><td class="py-4"><div class="flex items-center gap-3"><a class="font-semibold text-siget-coral underline decoration-2 underline-offset-4" href="{{ route('tournaments.edit', $tournament) }}">Editar</a><form method="POST" action="{{ route('tournaments.destroy', $tournament) }}" onsubmit="return confirm('¿Eliminar este torneo y sus datos asociados?')">@csrf @method('DELETE')<button class="font-semibold text-slate-400 underline decoration-2 underline-offset-4" type="submit">Eliminar</button></form></div></td></tr>
                @empty
                    <tr><td class="py-5 text-siget-muted" colspan="4">Todavía no hay torneos.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>

        <div class="space-y-6">
            <section class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Nuevo admin</p><h2 class="mt-2 text-2xl font-semibold">Crear administrador</h2>
                <form class="mt-5 space-y-4" method="POST" action="{{ route('super-admin.admins.store') }}">@csrf <input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="name" placeholder="Nombre" required><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="email" type="email" placeholder="Correo" required><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="password" type="password" placeholder="Contrasena" required><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="password_confirmation" type="password" placeholder="Confirmar contrasena" required><button class="w-full rounded-lg bg-emerald-500 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-emerald-400" type="submit">Crear admin</button></form>
            </section>
            <section class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Nuevo torneo</p><h2 class="mt-2 text-2xl font-semibold">Asignar responsable</h2>
                <form class="mt-5 space-y-4" method="POST" action="{{ route('super-admin.tournaments.store') }}">@csrf <input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="name" placeholder="Nombre" required><select class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="sport_type" required><option value="">Selecciona un deporte</option>@foreach ($sports as $sport)<option value="{{ $sport->name }}">{{ $sport->name }}</option>@endforeach</select><div class="grid gap-4 sm:grid-cols-2"><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="start_date" type="date" required><input class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="end_date" type="date" required></div><select class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2.5 text-sm text-white focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" name="admin_id" required><option value="">Selecciona un admin</option>@foreach ($activeAdmins as $admin)<option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>@endforeach</select><button class="w-full rounded-lg bg-emerald-500 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-emerald-400" type="submit">Crear torneo</button></form>
            </section>
        </div>
    </div>

    <section class="mt-10 rounded-xl border border-slate-800 bg-slate-900/60 p-5 shadow-lg shadow-black/10 backdrop-blur-md">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-bold uppercase tracking-[0.2em] text-siget-coral">Configuración</p><h2 class="mt-2 text-2xl font-semibold">Deportes permitidos</h2></div><form class="flex w-full gap-2 sm:max-w-md" method="POST" action="{{ route('super-admin.sports.store') }}">@csrf <input class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white placeholder:text-slate-500" name="name" placeholder="Nuevo deporte" maxlength="100" required><button class="rounded-lg bg-siget-mint px-4 py-2.5 text-sm font-bold text-siget-ink" type="submit">Agregar</button></form></div>
        <ul class="mt-5 divide-y divide-slate-800">@forelse ($sports as $sport)<li class="flex items-center justify-between gap-4 py-3"><span class="font-medium">{{ $sport->name }}</span><form method="POST" action="{{ route('super-admin.sports.destroy', $sport) }}" onsubmit="return confirm('¿Quitar {{ $sport->name }} del catálogo?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-siget-coral underline decoration-2 underline-offset-4" type="submit">Quitar</button></form></li>@empty<li class="py-4 text-sm text-siget-muted">No hay deportes configurados.</li>@endforelse</ul>
    </section>
@endsection
