@extends('v1.layouts.app')

@section('title', $tournament->name)
@section('header_title', $tournament->name)

@section('header_badge')
<span class="inline-block bg-[#dcfce7] text-[#15803d] text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
    {{ $tournament->status === 'active' ? 'Activo' : ($tournament->status === 'completed' ? 'Finalizado' : 'Pendiente') }}
</span>
@endsection

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 flex items-center gap-2 shadow-2xs">
            <svg class="size-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Tarjeta de Resumen del Torneo -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-800">
                    <a href="{{ route('tournaments.index') }}" class="hover:underline">&larr; Torneos</a>
                    <span>•</span>
                    <span>{{ $tournament->sport_type }}</span>
                </div>
                <h2 class="mt-1.5 text-2xl md:text-3xl font-black text-slate-900 tracking-tight">{{ $tournament->name }}</h2>
                <p class="mt-1.5 text-xs md:text-sm text-slate-500">
                    Fechas: {{ $tournament->start_date?->format('d M Y') }} — {{ $tournament->end_date?->format('d M Y') }}
                    @if ($tournament->admin) • Director de Torneo: <strong class="text-slate-700 font-semibold">{{ $tournament->admin->name }}</strong> @endif
                </p>
            </div>

            <!-- Botones de Acción Rápida -->
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" onclick="document.getElementById('add-team-tournament-modal').showModal()" class="px-4 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Inscribir Equipo</span>
                </button>
                <a href="{{ route('matches.schedule') }}" class="px-4 py-2 rounded-lg bg-[#1e3a8a] hover:bg-[#0284c7] active:bg-[#172554] text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Programar Partidos</span>
                </a>
                <a href="{{ route('tournaments.brackets', $tournament) }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5"><x-reicon name="trophy" class="size-3.5 text-slate-500" /><span>Llaves</span></span>
                </a>
                <a href="{{ route('tournaments.disciplinary', $tournament) }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5"><x-reicon name="shield" class="size-3.5 text-slate-500" /><span>Disciplinario</span></span>
                </a>
                <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-xs font-semibold hover:bg-slate-50 shadow-2xs transition flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5"><x-reicon name="setting" class="size-3.5 text-slate-500" /><span>Reglamento</span></span>
                </a>
                <a href="{{ route('standings.index', ['tournament' => $tournament->id]) }}" class="px-4 py-2 rounded-lg bg-sky-100 text-sky-900 text-xs font-bold hover:bg-sky-200 transition">
                    Posiciones
                </a>
            </div>
        </div>
    </div>

    <!-- Widget de Reglamento Activo del Torneo -->
    @if ($tournament->rules)
        @php $r = $tournament->rules; @endphp
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <x-reicon name="shield" class="size-4 text-slate-500" />
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Reglamento Técnico del Torneo</h3>
                </div>
                <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 transition">
                    Editar Reglamento &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Límite de Tarjetas Amarillas</span>
                    <div class="mt-1 text-lg font-bold text-amber-700">{{ $r->yellow_card_limit_for_suspension }} tarjetas</div>
                    <span class="text-[11px] text-slate-400">= 1 partido de suspensión</span>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Sistema de Puntuación</span>
                    <div class="mt-1 text-lg font-bold text-emerald-800">{{ $r->points_for_win }}PG / {{ $r->points_for_draw }}PE / {{ $r->points_for_loss }}PP</div>
                    <span class="text-[11px] text-slate-400">Clasificación estándar</span>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Duración del Partido</span>
                    <div class="mt-1 text-lg font-bold text-slate-900">{{ $r->match_duration_minutes }} minutos</div>
                    <span class="text-[11px] text-slate-400">Tiempo reglamentario regular</span>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <span class="text-slate-500 font-medium">Cambios Permitidos</span>
                    <div class="mt-1 text-lg font-bold text-sky-800">{{ $r->max_substitutions }} cambios</div>
                    <span class="text-[11px] text-slate-400">Por equipo por partido</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Cuadrícula: Equipos Inscritos y Partidos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Equipos en el Torneo -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Equipos Inscritos ({{ $tournament->teams->count() }})</h3>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('add-team-tournament-modal').showModal()" class="px-2.5 py-1 rounded-md bg-[#1e3a8a] hover:bg-[#0284c7] text-white text-xs font-bold transition flex items-center gap-1 cursor-pointer shadow-2xs">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Inscribir</span>
                    </button>
                    <a href="{{ route('teams.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">
                        Gestionar &rarr;
                    </a>
                </div>
            </div>

            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($tournament->teams as $team)
                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center border border-emerald-200">
                                {{ strtoupper(substr($team->name, 0, 2)) }}
                            </div>
                            <div>
                                <a href="{{ route('teams.show', $team) }}" class="font-bold text-sm text-slate-900 hover:text-emerald-700 transition">
                                    {{ $team->name }}
                                </a>
                                <p class="text-xs text-slate-500">
                                    Director Técnico: {{ $team->coach_name }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('teams.show', $team) }}" class="px-3 py-1 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Ver Equipo
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center space-y-2">
                        <p class="text-xs text-slate-500">No hay equipos inscritos todavía en este torneo.</p>
                        <button type="button" onclick="document.getElementById('add-team-tournament-modal').showModal()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-800 hover:bg-emerald-100 text-xs font-bold transition cursor-pointer">
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Inscribir el Primer Equipo</span>
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Matches & Fixtures -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Partidos y Calendario ({{ $tournament->matches->count() }})</h3>
                <a href="{{ route('matches.schedule') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">
                    Ver Calendario &rarr;
                </a>
            </div>

            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($tournament->matches->take(6) as $match)
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-900">
                                {{ $match->homeTeam?->name ?? 'Por definir' }}
                                @if ($match->status === 'played')
                                    <span class="text-emerald-700 font-black">({{ $match->home_score }} - {{ $match->away_score }})</span>
                                @else
                                    <span class="text-slate-400 font-normal">vs</span>
                                @endif
                                {{ $match->awayTeam?->name ?? 'Por definir' }}
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $match->match_date?->format('d M Y · H:i') ?? 'Fecha por definir' }}
                                @if ($match->venue) • <x-reicon name="pin" class="size-3 text-slate-400 inline-block" /> {{ $match->venue->name }} @endif
                            </p>
                        </div>
                        <a href="{{ route('matches.show', $match) }}" class="px-3 py-1 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                            Detalles
                        </a>
                    </div>
                @empty
                    <p class="py-6 text-xs text-slate-500 text-center">No hay partidos programados todavía.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>

<!-- Modal Centrada: Inscribir Equipo en Este Torneo -->
<x-modal id="add-team-tournament-modal" title="Inscribir Equipo en {{ $tournament->name }}" maxWidth="max-w-xl">
    <form method="POST" action="{{ route('teams.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="tournament_id" value="{{ $tournament->id }}">
        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

        <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl text-xs flex items-center justify-between">
            <span class="text-slate-600 font-medium">Torneo: <strong class="text-slate-900 font-bold">{{ $tournament->name }}</strong> ({{ $tournament->sport_type }})</span>
            <span class="text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Oficial</span>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre del Club / Equipo *</label>
            <input type="text" name="name" required placeholder="Ej: Real Metropolitan FC" class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Escudo / Logo (Opcional)</label>
            <input type="text" name="logo_path" placeholder="https://... o /logos/team.png" class="w-full px-3 py-2 text-xs sm:text-sm bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
        </div>

        <!-- Sección Director Técnico -->
        <div class="space-y-3 p-4 bg-slate-50/80 border border-slate-200/90 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <svg class="size-4 text-[#1e3a8a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Director Técnico (DT)</span>
                </div>
                <span class="text-[10px] bg-sky-100 text-sky-900 border border-sky-300 font-extrabold px-2 py-0.5 rounded-full">Acceso Exclusivo</span>
            </div>

            <!-- Selector de Modo de DT -->
            <div class="grid grid-cols-3 gap-1 bg-slate-200/70 p-1 rounded-lg text-xs font-semibold text-slate-600">
                <label class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-md cursor-pointer transition has-[:checked]:bg-white has-[:checked]:text-[#1e3a8a] has-[:checked]:shadow-2xs">
                    <input type="radio" name="dt_mode" value="new" checked class="sr-only" onchange="toggleTournModalDtMode(this.value)">
                    <span>+ Crear DT</span>
                </label>
                <label class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-md cursor-pointer transition has-[:checked]:bg-white has-[:checked]:text-[#1e3a8a] has-[:checked]:shadow-2xs">
                    <input type="radio" name="dt_mode" value="existing" class="sr-only" onchange="toggleTournModalDtMode(this.value)">
                    <span>Existente</span>
                </label>
                <label class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-md cursor-pointer transition has-[:checked]:bg-white has-[:checked]:text-[#1e3a8a] has-[:checked]:shadow-2xs">
                    <input type="radio" name="dt_mode" value="none" class="sr-only" onchange="toggleTournModalDtMode(this.value)">
                    <span>Sin DT</span>
                </label>
            </div>

            <!-- Modo 1: Registrar Nuevo DT Aquí Mismo -->
            <div id="tourn-modal-dt-new-fields" class="space-y-3 pt-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre Completo del DT *</label>
                        <input type="text" name="coach_name" placeholder="Ej: Marcelo Gallardo" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email del DT (Login) *</label>
                        <input type="email" name="coach_email" placeholder="dt.gallardo@siget.com" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Contraseña de Acceso</label>
                        <input type="text" name="coach_password" value="password" placeholder="Mínimo 6 caracteres" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                        <span class="text-[10px] text-slate-500">Predeterminada: password</span>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Teléfono / WhatsApp (Opcional)</label>
                        <input type="text" name="coach_phone" placeholder="+57 300 123 4567" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                    </div>
                </div>

                <div class="rounded-lg bg-blue-50/80 border border-blue-200/80 p-2.5 text-[11px] text-blue-900 flex items-start gap-2">
                    <svg class="size-4 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong>Acceso & Seguridad:</strong> El DT tendrá acceso exclusivo al panel táctico de este equipo y al radar de búsqueda de jugadores libres.</span>
                </div>
            </div>

            <!-- Modo 2: Seleccionar DT Existente -->
            <div id="tourn-modal-dt-existing-fields" class="space-y-2 pt-1 hidden">
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Seleccionar DT Registrado</label>
                <select name="existing_dt_id" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                    <option value="">-- Elige un Director Técnico registrado --</option>
                    @if(isset($coaches))
                        @foreach($coaches as $coach)
                            <option value="{{ $coach->id }}">{{ $coach->name }} ({{ $coach->email }})</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Modo 3: Sin DT -->
            <div id="tourn-modal-dt-none-fields" class="pt-1 text-[11px] text-slate-500 hidden">
                El equipo se inscribirá sin DT por ahora.
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <x-button variant="secondary" onclick="document.getElementById('add-team-tournament-modal').close()">Cancelar</x-button>
            <x-button type="submit">Inscribir en Torneo</x-button>
        </div>
    </form>
</x-modal>

<script>
function toggleTournModalDtMode(mode) {
    const newFields = document.getElementById('tourn-modal-dt-new-fields');
    const existingFields = document.getElementById('tourn-modal-dt-existing-fields');
    const noneFields = document.getElementById('tourn-modal-dt-none-fields');
    if (!newFields || !existingFields || !noneFields) return;

    newFields.classList.toggle('hidden', mode !== 'new');
    existingFields.classList.toggle('hidden', mode !== 'existing');
    noneFields.classList.toggle('hidden', mode !== 'none');
}
</script>
@endsection
