@extends('v1.layouts.app')

@section('title', $team->name)

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <!-- Header del Equipo (Formal White Card) -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 md:p-8 shadow-2xs">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <span class="grid size-16 place-items-center rounded-2xl bg-emerald-50 text-3xl font-black text-[#057a55] border border-emerald-100 shadow-2xs">
                    🛡️
                </span>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#057a55]">
                        <a href="{{ route('tournaments.show', $team->tournament) }}" class="hover:underline">← {{ $team->tournament->name }}</a>
                        <span class="text-slate-300">•</span>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-200">{{ strtoupper($team->status) }}</span>
                    </div>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900 md:text-3xl">{{ $team->name }}</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Director Técnico:
                        @if ($team->captain)
                            <span class="font-bold text-slate-800">{{ $team->captain->name }}</span>
                        @else
                            <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800 border border-amber-200">Sin DT asignado</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @if (auth()->check() && (auth()->id() === $team->captain_id || auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $team->tournament->admin_id === auth()->id())))
                    <a href="{{ route('dt.dashboard', $team) }}" class="rounded-lg bg-[#057a55] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#046c4b] flex items-center gap-1.5">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Panel de DT</span>
                    </a>
                @endif
                <a href="{{ route('teams.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Volver a Equipos
                </a>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <!-- Banner de Magic Link de Invitación al DT recién creado -->
    @if (session('invitation_created'))
        @php $invite = session('invitation_created'); @endphp
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-6 shadow-2xs">
            <div class="flex items-center gap-3 border-b border-emerald-200/60 pb-3">
                <span class="text-2xl">🔗</span>
                <div>
                    <h2 class="text-sm font-bold text-emerald-950">¡Magic Link de Invitación Generado!</h2>
                    <p class="text-xs text-emerald-800">Comparte este enlace con el Director Técnico para que tome el control del equipo sin crearle usuario manualmente.</p>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <input type="text" readonly value="{{ $invite['claim_url'] }}" id="magicLinkInput"
                       class="w-full bg-white px-3.5 py-2 font-mono text-xs text-emerald-800 border border-emerald-300 rounded-lg" />
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('magicLinkInput').value); alert('¡Enlace copiado al portapapeles!');"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shrink-0 shadow-2xs">
                    📋 Copiar Enlace
                </button>
                <a href="{{ $invite['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer"
                   class="rounded-lg bg-[#057a55] px-4 py-2 text-xs font-semibold text-white hover:bg-[#046c4b] transition shrink-0 shadow-2xs">
                    💬 Enviar por WhatsApp
                </a>
            </div>
        </div>
    @endif

    <!-- Generación de Magic Link si no hay DT asignado o para invitar nuevo DT -->
    @if (auth()->check() && (auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $team->tournament->admin_id === auth()->id())))
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🪄</span>
                    <h2 class="text-sm font-bold text-slate-900">Generar Magic Link para Director Técnico (DT)</h2>
                </div>
                <span class="text-xs text-slate-400 font-medium">Onboarding Digital</span>
            </div>

            <form method="POST" action="{{ route('teams.invitations.store', $team) }}" class="mt-4 grid gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label for="recipient_name" class="block text-xs font-semibold text-slate-700 mb-1">Nombre del DT</label>
                    <input type="text" id="recipient_name" name="recipient_name" placeholder="Ej: Profe Juan Gómez" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                </div>
                <div>
                    <label for="recipient_phone" class="block text-xs font-semibold text-slate-700 mb-1">Teléfono / WhatsApp</label>
                    <input type="text" id="recipient_phone" name="recipient_phone" placeholder="Ej: 3101234567" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-lg bg-[#057a55] py-2 text-xs font-semibold text-white hover:bg-[#046c4b] transition shadow-2xs">
                        Generar Magic Link
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Nómina del Equipo -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Plantilla de Jugadores ({{ $team->players->count() }})</h2>
                <p class="text-xs text-slate-500">Deportistas inscritos y habilitados en este plantel.</p>
            </div>
            @if (auth()->check() && (auth()->id() === $team->captain_id || auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && $team->tournament->admin_id === auth()->id())))
                <a href="{{ route('dt.roster', $team) }}" class="rounded-lg border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    Gestionar Nómina / CSV →
                </a>
            @endif
        </div>

        @if ($team->players->isEmpty())
            <div class="py-10 text-center text-xs text-slate-400">
                <span class="text-3xl block">👥</span>
                <p class="mt-2 font-medium">Aún no hay jugadores registrados en este equipo.</p>
            </div>
        @else
            <div class="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                @foreach ($team->players as $player)
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200/80 bg-slate-50/50 p-3.5">
                        <span class="grid size-10 place-items-center rounded-lg bg-emerald-50 font-mono text-sm font-bold text-emerald-700 border border-emerald-100">
                            #{{ $player->jersey_number }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-bold text-slate-900">{{ $player->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $player->identification_document }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
