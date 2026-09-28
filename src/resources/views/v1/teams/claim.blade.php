@extends('v1.layouts.app')

@section('title', 'Invitación DT: ' . $invitation->team->name)

@section('content')
<div class="mx-auto max-w-xl py-6">
    <div class="rounded-3xl border border-emerald-500/30 bg-slate-900/80 p-8 shadow-2xl backdrop-blur-xl">
        <div class="text-center">
            <span class="inline-flex size-16 items-center justify-center rounded-2xl bg-emerald-500/20 text-3xl font-black text-emerald-400 shadow-inner">
                ⚽
            </span>
            <div class="mt-4 text-xs font-bold uppercase tracking-wider text-emerald-400">Invitación de Director Técnico (DT)</div>
            <h1 class="mt-1 text-2xl font-black text-white sm:text-3xl">Toma el mando de {{ $invitation->team->name }}</h1>
            <p class="mt-2 text-sm text-slate-400">
                Torneo: <span class="font-bold text-slate-200">{{ $invitation->team->tournament->name }}</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Invitación generada por el Administrador de la competición. Válida hasta el {{ $invitation->expires_at->format('d/m/Y') }}.
            </p>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mt-8 border-t border-slate-800 pt-6">
            @auth
                <!-- Usuario ya autenticado -->
                <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-5 text-center">
                    <p class="text-sm text-slate-300">
                        Has iniciado sesión como <span class="font-bold text-white">{{ auth()->user()->name }}</span> ({{ auth()->user()->email }}).
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Al hacer clic a continuación, se te asignará el rol de Director Técnico de este equipo.
                    </p>
                    <form method="POST" action="{{ route('teams.invitations.accept', $invitation->token) }}" class="mt-5">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-400">
                            Aceptar y Vincular Equipo a mi Cuenta
                        </button>
                    </form>
                </div>
            @else
                <!-- Registro de nuevo Director Técnico -->
                <div class="mb-4">
                    <h2 class="text-base font-bold text-white">Crea tu cuenta de Director Técnico</h2>
                    <p class="text-xs text-slate-400">Gestionarás la nómina, documentos de jugadores y alineaciones digitales.</p>
                </div>

                <form method="POST" action="{{ route('teams.invitations.accept', $invitation->token) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nombre Completo *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $invitation->recipient_name) }}" required class="mt-1 w-full px-3.5 py-2.5 text-sm" placeholder="Ej: Carlo Ancelotti" />
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Correo Electrónico *</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $invitation->recipient_email) }}" required class="mt-1 w-full px-3.5 py-2.5 text-sm" placeholder="tu@correo.com" />
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Teléfono / WhatsApp</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $invitation->recipient_phone) }}" class="mt-1 w-full px-3.5 py-2.5 text-sm" placeholder="Ej: 3001234567" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Contraseña *</label>
                            <input type="password" id="password" name="password" required class="mt-1 w-full px-3.5 py-2.5 text-sm" placeholder="Mínimo 8 caracteres" />
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Confirmar Contraseña *</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required class="mt-1 w-full px-3.5 py-2.5 text-sm" placeholder="Repite contraseña" />
                        </div>
                    </div>

                    <button type="submit" class="mt-6 w-full rounded-xl bg-emerald-500 py-3 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-400">
                        Reclamar Equipo y Entrar a mi Panel de DT
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <p class="text-xs text-slate-400">
                        ¿Ya tienes cuenta en SIGET?
                        <a href="{{ route('login') }}" class="font-bold text-emerald-400 hover:underline">Inicia sesión</a> y vuelve a este enlace.
                    </p>
                </div>
            @endauth
        </div>
    </div>
</div>
@endsection
