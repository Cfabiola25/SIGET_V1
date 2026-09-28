@extends('v1.layouts.app')

@section('title', $team->name)

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <div class="rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900/90 to-emerald-950/40 p-6 md:p-8 backdrop-blur-xl">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <span class="grid size-16 place-items-center rounded-2xl bg-emerald-500/20 text-3xl font-black text-emerald-400 shadow-inner">
                    🛡️
                </span>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-400">
                        <a href="{{ route('tournaments.show', $team->tournament) }}" class="hover:underline">← {{ $team->tournament->name }}</a>
                        <span>•</span>
                        <span>{{ ucfirst($team->status) }}</span>
                    </div>
                    <h1 class="mt-1 text-2xl font-black text-white md:text-3xl">{{ $team->name }}</h1>
                    <p class="text-sm text-slate-400">
                        Director Técnico:
                        @if ($team->captain)
                            <span class="font-bold text-slate-200">{{ $team->captain->name }}</span>
                        @else
                            <span class="rounded bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-400">Sin DT asignado</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if (auth()->check() && (auth()->id() === $team->captain_id || auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $team->tournament->admin_id === auth()->id())))
                    <a href="{{ route('dt.dashboard', $team) }}" class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400">
                        📋 Entrar al Panel de DT
                    </a>
                @endif
                <a href="{{ route('teams.index') }}" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm font-bold text-slate-200 hover:bg-slate-700 transition">
                    Volver a Equipos
                </a>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-400">
            {{ session('status') }}
        </div>
    @endif

    <!-- Banner de Magic Link de Invitación al DT recién creado -->
    @if (session('invitation_created'))
        @php $invite = session('invitation_created'); @endphp
        <div class="rounded-2xl border border-emerald-500/40 bg-emerald-950/30 p-6 backdrop-blur-sm">
            <div class="flex items-center gap-3 border-b border-emerald-500/20 pb-4">
                <span class="text-2xl">🔗</span>
                <div>
                    <h2 class="text-base font-black text-white">¡Magic Link de Invitación Generado!</h2>
                    <p class="text-xs text-emerald-300">Comparte este enlace con el Director Técnico para que tome el control del equipo sin crearle usuario manualmente.</p>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <input type="text" readonly value="{{ $invite['claim_url'] }}" id="magicLinkInput"
                       class="w-full bg-slate-950 px-3.5 py-2.5 font-mono text-xs text-emerald-400 border border-slate-800 rounded-xl" />
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('magicLinkInput').value); alert('¡Enlace copiado al portapapeles!');"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-700 transition shrink-0">
                    📋 Copiar Enlace
                </button>
                <a href="{{ $invite['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer"
                   class="rounded-xl bg-emerald-500 px-5 py-2.5 text-xs font-bold text-slate-950 hover:bg-emerald-400 transition shrink-0 shadow-md">
                    💬 Enviar por WhatsApp
                </a>
            </div>
        </div>
    @endif

    <!-- Generación de Magic Link si no hay DT asignado o para invitar nuevo DT -->
    @if (auth()->check() && (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $team->tournament->admin_id === auth()->id())))
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🪄</span>
                    <h2 class="text-base font-bold text-white">Generar Magic Link para Director Técnico (DT)</h2>
                </div>
                <span class="text-xs text-slate-400">Onboarding de Equipos</span>
            </div>

            <form method="POST" action="{{ route('teams.invitations.store', $team) }}" class="mt-4 grid gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label for="recipient_name" class="block text-xs font-semibold uppercase text-slate-400">Nombre del DT</label>
                    <input type="text" id="recipient_name" name="recipient_name" placeholder="Ej: Profe Juan Gómez" class="mt-1 w-full px-3 py-2 text-xs" />
                </div>
                <div>
                    <label for="recipient_phone" class="block text-xs font-semibold uppercase text-slate-400">Teléfono / WhatsApp</label>
                    <input type="text" id="recipient_phone" name="recipient_phone" placeholder="Ej: 3101234567" class="mt-1 w-full px-3 py-2 text-xs" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-xl bg-emerald-500 py-2.5 text-xs font-bold text-slate-950 hover:bg-emerald-400 transition shadow-md">
                        Generar Magic Link
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Nómina del Equipo -->
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-lg font-bold text-white">Plantilla de Jugadores ({{ $team->players->count() }})</h2>
                <p class="text-xs text-slate-400">Jugadores registrados en este equipo.</p>
            </div>
            @if (auth()->check() && (auth()->id() === $team->captain_id || auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $team->tournament->admin_id === auth()->id())))
                <a href="{{ route('dt.roster', $team) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-1.5 text-xs font-semibold text-slate-200 hover:bg-slate-700">
                    Gestionar Nómina / CSV →
                </a>
            @endif
        </div>

        @if ($team->players->isEmpty())
            <div class="py-8 text-center text-sm text-slate-400">
                <span class="text-3xl">👥</span>
                <p class="mt-2">Aún no hay jugadores registrados en este equipo.</p>
            </div>
        @else
            <div class="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                @foreach ($team->players as $player)
                    <div class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/60 p-3.5">
                        <span class="grid size-10 place-items-center rounded-lg bg-emerald-500/10 font-mono text-base font-black text-emerald-400">
                            #{{ $player->jersey_number }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-bold text-white">{{ $player->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $player->identification_document }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
