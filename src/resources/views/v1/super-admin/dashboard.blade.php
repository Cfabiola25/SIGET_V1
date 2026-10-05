@extends('v1.layouts.app')

@section('title', 'Panel Super Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Panel de Super Administrador</h1>
            <p class="text-xs text-slate-500 mt-0.5">Control de plataforma SaaS, asignación de administradores y catálogo de disciplinas.</p>
        </div>
        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-800">
            {{ auth()->user()->role === 'super_admin' ? 'Super Administrador' : ucfirst(auth()->user()->role) }}
        </span>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Total Torneos</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $tournaments->count() }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Competiciones bajo la cuenta SaaS</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500">Administradores Activos</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">{{ $admins->where('is_active', true)->count() }}</div>
            <p class="text-[11px] text-slate-400 mt-1">{{ $admins->where('is_active', false)->count() }} cuentas desactivadas</p>
        </div>
    </div>

    <!-- Administradores -->
    <section class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Directores & Administradores de Torneo</h2>
                <p class="text-xs text-slate-500">Gestión de accesos y credenciales</p>
            </div>
            <span class="text-xs text-slate-400 font-medium">{{ $admins->count() }} registrados</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="py-3 px-5">Nombre</th>
                        <th class="py-3 px-5">Correo</th>
                        <th class="py-3 px-5 text-center">Torneos</th>
                        <th class="py-3 px-5 text-center">Estado</th>
                        <th class="py-3 px-5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($admins as $admin)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-5 font-bold text-slate-900">{{ $admin->name }}</td>
                            <td class="py-3.5 px-5 text-slate-500 font-mono">{{ $admin->email }}</td>
                            <td class="py-3.5 px-5 text-center font-bold text-slate-800">{{ $admin->managed_tournaments_count }}</td>
                            <td class="py-3.5 px-5 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $admin->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $admin->is_active ? 'Activo' : 'Desactivado' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <form method="POST" action="{{ route('super-admin.admins.status', $admin) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $admin->is_active ? 0 : 1 }}">
                                        <button class="text-xs font-semibold {{ $admin->is_active ? 'text-rose-600 hover:underline' : 'text-[#057a55] hover:underline' }}" type="submit">
                                            {{ $admin->is_active ? 'Desactivar' : 'Reactivar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-8 text-center text-slate-400" colspan="5">Todavía no hay administradores.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @php
        $featuredTournament = $tournaments->firstWhere('is_featured', true) ?? $tournaments->where('status', 'active')->first();
    @endphp
    <!-- Banner de Torneo Principal Configurado por Super Admin -->
    <div class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-500/10 via-amber-100/40 to-emerald-500/10 p-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <span class="size-11 rounded-2xl bg-amber-400 text-amber-950 flex items-center justify-center shadow-xs shrink-0">
                    <x-reicon name="star" variant="filled" class="size-6 text-amber-950" />
                </span>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-300">
                        TORNEO PRINCIPAL DEL PORTAL PÚBLICO
                    </span>
                    <h3 class="font-display font-black text-slate-900 text-lg mt-1">
                        {{ $featuredTournament ? $featuredTournament->name : 'Ningún torneo fijado aún' }}
                    </h3>
                    <p class="text-xs text-slate-600">
                        @if($featuredTournament)
                            Este torneo es el que se presenta automáticamente como protagonista en la página de inicio, tabla de posiciones, calendario y noticias para todo el público visitante.
                        @else
                            Selecciona en la tabla de abajo cuál torneo debe destacarse como Principal para que sea el que el público vea por defecto.
                        @endif
                    </p>
                </div>
            </div>
            @if($featuredTournament)
                <a href="{{ url('/?tournament_id=' . $featuredTournament->id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-white border border-amber-300 text-amber-900 hover:bg-amber-50 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs shrink-0 self-start sm:self-auto">
                    <span>Ver en Portal Público</span>
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif
        </div>
    </div>

    <!-- Torneos & Nuevo Admin Grid -->
    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Catálogo de Torneos</h2>
                    <p class="text-xs text-slate-500">Historial de competiciones SaaS</p>
                </div>
                <span class="text-xs text-slate-400 font-medium">{{ $tournaments->count() }} torneos</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="py-3 px-5">Torneo</th>
                            <th class="py-3 px-4">Deporte</th>
                            <th class="py-3 px-4">Admin Asignado</th>
                            <th class="py-3 px-4 text-center">Destacado Público</th>
                            <th class="py-3 px-5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($tournaments as $tournament)
                            <tr class="hover:bg-slate-50/60 transition {{ $tournament->is_featured ? 'bg-amber-50/40' : '' }}">
                                <td class="py-3.5 px-5 font-bold text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        @if($tournament->is_featured)
                                            <x-reicon name="star" variant="filled" class="size-3.5 text-amber-500 shrink-0" title="Torneo Principal" />
                                        @endif
                                        <span>{{ $tournament->name }}</span>
                                    </div>
                                    <span class="mt-0.5 block text-[11px] font-normal text-slate-400">{{ $tournament->start_date->format('d/m/Y') }} &bull; {{ $tournament->status === 'active' ? 'Activo' : ($tournament->status === 'completed' ? 'Finalizado' : 'Pendiente') }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">{{ $tournament->sport_type }}</td>
                                <td class="py-3.5 px-4 text-slate-600 font-semibold">{{ $tournament->admin?->name ?? 'Sin asignar' }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($tournament->is_featured)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                            <x-reicon name="star" variant="filled" class="size-3 text-amber-600 shrink-0" />
                                            <span>Principal</span>
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('super-admin.tournaments.feature', $tournament) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-slate-600 bg-slate-100 hover:bg-amber-100 hover:text-amber-900 hover:border-amber-300 transition border border-slate-200" title="Definir este torneo como el principal visible en el portal público">
                                                Fijar Principal
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a class="text-xs font-semibold text-[#057a55] hover:underline" href="{{ route('tournaments.edit', $tournament) }}">Editar</a>
                                        <form method="POST" action="{{ route('tournaments.destroy', $tournament) }}" onsubmit="return confirm('¿Eliminar este torneo y sus datos asociados?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-xs font-semibold text-rose-600 hover:underline" type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-8 text-center text-slate-400" colspan="5">Todavía no hay torneos creados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Crear Admin y Torneo -->
        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <h2 class="text-sm font-bold text-slate-900">Crear Nuevo Administrador</h2>
                <form class="mt-4 space-y-3" method="POST" action="{{ route('super-admin.admins.store') }}">
                    @csrf 
                    <input class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[#057a55] focus:outline-none" name="name" placeholder="Nombre completo" required>
                    <input class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[#057a55] focus:outline-none" name="email" type="email" placeholder="Correo electrónico" required>
                    <input class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[#057a55] focus:outline-none" name="password" type="password" placeholder="Contraseña (mín. 8 caracteres)" required>
                    <input class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[#057a55] focus:outline-none" name="password_confirmation" type="password" placeholder="Confirmar contraseña" required>
                    <button class="w-full rounded-lg bg-[#057a55] py-2 text-xs font-semibold text-white transition hover:bg-[#046c4b] shadow-2xs" type="submit">Crear Administrador</button>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <h2 class="text-sm font-bold text-slate-900">Crear Torneo Directo</h2>
                <form class="mt-4 space-y-3" method="POST" action="{{ route('super-admin.tournaments.store') }}">
                    @csrf 
                    <input class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[#057a55] focus:outline-none" name="name" placeholder="Nombre del torneo" required>
                    <select class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" name="sport_type" required>
                        <option value="">Selecciona disciplina</option>
                        @foreach ($sports as $sport)
                            <option value="{{ $sport->name }}">{{ $sport->name }}</option>
                        @endforeach
                    </select>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <input class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" name="start_date" type="date" required>
                        <input class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" name="end_date" type="date" required>
                    </div>
                    <select class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" name="admin_id" required>
                        <option value="">Selecciona admin responsable</option>
                        @foreach ($activeAdmins as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                        @endforeach
                    </select>
                    <button class="w-full rounded-lg bg-[#057a55] py-2 text-xs font-semibold text-white transition hover:bg-[#046c4b] shadow-2xs" type="submit">Crear Torneo</button>
                </form>
            </section>
        </div>
    </div>

    <!-- Catálogo de modalidades de fútbol -->
    <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Modalidades Oficiales de Fútbol</h2>
                <p class="text-xs text-slate-500">Catálogo oficial: Fútbol 11, Fútbol 8 (Sintética), Fútbol 5 (Sintética) y Microfútbol</p>
            </div>
            <form class="flex w-full gap-2 sm:max-w-md" method="POST" action="{{ route('super-admin.sports.store') }}">
                @csrf 
                <input class="min-w-0 flex-1 rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[#057a55] focus:outline-none" name="name" placeholder="Nueva modalidad de fútbol (ej: Fútbol 7)" maxlength="100" required>
                <button class="rounded-lg bg-[#057a55] px-4 py-2 text-xs font-semibold text-white hover:bg-[#046c4b] shadow-2xs" type="submit">Agregar</button>
            </form>
        </div>

        <ul class="mt-4 divide-y divide-slate-100 text-xs">
            @forelse ($sports as $sport)
                <li class="flex items-center justify-between py-2.5">
                    <span class="font-semibold text-slate-800">{{ $sport->name }}</span>
                    <form method="POST" action="{{ route('super-admin.sports.destroy', $sport) }}" onsubmit="return confirm('¿Quitar {{ $sport->name }} del catálogo?')">
                        @csrf 
                        @method('DELETE')
                        <button class="text-xs font-semibold text-rose-600 hover:underline" type="submit">Quitar</button>
                    </form>
                </li>
            @empty
                <li class="py-4 text-center text-slate-400">No hay deportes configurados.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
