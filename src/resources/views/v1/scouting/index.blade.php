@extends('v1.layouts.app')

@section('title', 'Radar de Talentos & Bolsa de Fichajes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-50 px-2.5 py-0.5 text-xs font-bold text-cyan-800 border border-cyan-200">
                    <span class="size-1.5 rounded-full bg-cyan-600"></span>
                    MERCADO DE PASES LOCAL
                </span>
                <span class="text-xs text-slate-400 font-medium">Bolsa de Talentos & Agentes Libres</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Radar de Talentos</h1>
            <p class="text-xs text-slate-500">Explora jugadores disponibles, revisa su calificación algorítmica y envía propuestas formales de fichaje.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('scouting.requests.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                <x-reicon name="clipboard-list" class="size-4 text-emerald-600" />
                <span>Solicitudes de Fichaje</span>
                @php
                    $pendingCount = \App\Models\v1\PlayerRecruitmentRequest::where('status', 'pending')->count();
                @endphp
                @if ($pendingCount > 0)
                    <span class="rounded-full bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5">{{ $pendingCount }}</span>
                @endif
            </a>

            @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isAdmin())
                <button type="button" onclick="document.getElementById('modal-add-free-agent').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-lg bg-[#2b4e72] px-4 py-2 text-xs font-semibold text-white hover:bg-[#2790b0] transition shadow-2xs">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Registrar Jugador Libre</span>
                </button>
            @endif
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Barra de Filtros -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
        <form method="GET" action="{{ route('scouting.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Posición</label>
                <select name="position" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                    <option value="">Todas las posiciones</option>
                    <option value="goalkeeper" {{ ($filters['position'] ?? '') === 'goalkeeper' ? 'selected' : '' }}>Portero</option>
                    <option value="defender" {{ ($filters['position'] ?? '') === 'defender' ? 'selected' : '' }}>Defensa</option>
                    <option value="midfielder" {{ ($filters['position'] ?? '') === 'midfielder' ? 'selected' : '' }}>Mediocampista</option>
                    <option value="forward" {{ ($filters['position'] ?? '') === 'forward' ? 'selected' : '' }}>Delantero</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pie Hábil</label>
                <select name="preferred_foot" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                    <option value="">Cualquier pie</option>
                    <option value="right" {{ ($filters['preferred_foot'] ?? '') === 'right' ? 'selected' : '' }}>Diestro</option>
                    <option value="left" {{ ($filters['preferred_foot'] ?? '') === 'left' ? 'selected' : '' }}>Zurdo</option>
                    <option value="ambidextrous" {{ ($filters['preferred_foot'] ?? '') === 'ambidextrous' ? 'selected' : '' }}>Ambidiestro</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Rating Mínimo</label>
                <select name="min_rating" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                    <option value="">Cualquier calificación</option>
                    <option value="6.0" {{ ($filters['min_rating'] ?? '') === '6.0' ? 'selected' : '' }}>6.0 o superior</option>
                    <option value="7.0" {{ ($filters['min_rating'] ?? '') === '7.0' ? 'selected' : '' }}>7.0 o superior</option>
                    <option value="8.0" {{ ($filters['min_rating'] ?? '') === '8.0' ? 'selected' : '' }}>8.0 o superior</option>
                    <option value="9.0" {{ ($filters['min_rating'] ?? '') === '9.0' ? 'selected' : '' }}>9.0 Élite</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Buscar</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre o documento..." class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none placeholder:text-slate-400">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-lg bg-[#2b4e72] py-2 text-xs font-semibold text-white transition hover:bg-[#2790b0] shadow-2xs">
                    Filtrar Radar
                </button>
                <a href="{{ route('scouting.index') }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Cuadrícula de Agentes Libres -->
    @if($freeAgents->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center shadow-2xs">
            <div class="mx-auto size-12 rounded-xl bg-cyan-50 text-cyan-700 grid place-items-center mb-3">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">No se encontraron agentes libres</h3>
            <p class="mt-1 text-xs text-slate-500">Prueba ajustando los filtros del radar o libera jugadores desde tu plantel.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($freeAgents as $player)
                @php
                    $profile = $player->profile;
                    $rating = $profile?->performance_rating ?? 7.0;
                    $pendingReq = $player->pendingRecruitmentRequest;
                @endphp
                <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs hover:shadow-xs transition">
                    <div>
                        <!-- Badges Superiores -->
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-cyan-50 px-2.5 py-0.5 text-[10px] font-bold text-cyan-800 border border-cyan-200">
                                Agente Libre
                            </span>
                            <div class="flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 border border-amber-200">
                                <span>{{ number_format($rating, 1) }}</span>
                                <x-reicon name="star" variant="filled" class="size-3 text-amber-400" />
                            </div>
                        </div>

                        <!-- Información del Jugador -->
                        <div class="mt-4 flex items-center gap-3">
                            <div class="grid size-11 place-items-center rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700">
                                {{ mb_substr($player->name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 hover:text-[#2b4e72] transition">
                                    <a href="{{ route('scouting.show', $player) }}">{{ $player->name }}</a>
                                </h3>
                                <p class="text-[11px] text-slate-500">
                                    @php
                                        $posMap = ['goalkeeper' => 'Portero', 'defender' => 'Defensa', 'midfielder' => 'Mediocampista', 'forward' => 'Delantero'];
                                        $footMap = ['right' => 'Diestro', 'left' => 'Zurdo', 'ambidextrous' => 'Ambidiestro'];
                                    @endphp
                                    {{ $posMap[$profile?->position] ?? 'Mediocampista' }} • {{ $footMap[$profile?->preferred_foot] ?? 'Diestro' }}
                                </p>
                            </div>
                        </div>

                        <!-- Resumen Estadístico -->
                        <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-2.5 text-center border border-slate-100">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">{{ $player->goalsCount() }}</span>
                                <span class="text-[10px] text-slate-400">Goles</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">{{ $player->lineups()->count() }}</span>
                                <span class="text-[10px] text-slate-400">PJ</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800">{{ $profile?->height_cm ? $profile->height_cm . ' cm' : '-' }}</span>
                                <span class="text-[10px] text-slate-400">Estatura</span>
                            </div>
                        </div>

                        @if ($pendingReq)
                            <div class="mt-3 p-2 bg-amber-50 border border-amber-200 rounded-lg text-[10px] text-amber-800 font-semibold text-center">
                                Propuesta en proceso con {{ $pendingReq->team?->name }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <a href="{{ route('scouting.show', $player) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                            Ver Radar
                        </a>

                        @if(auth()->check() && (auth()->user()->isCoach() || auth()->user()->isCaptain() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()))
                            <button type="button" 
                                    onclick="openRecruitModal({{ $player->id }}, '{{ addslashes($player->name) }}', '{{ $posMap[$profile?->position] ?? 'Jugador' }}')"
                                    class="rounded-lg bg-[#2b4e72] px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-[#2790b0] transition shadow-2xs">
                                Contactar y Fichar
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg bg-[#2b4e72] px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-[#2790b0] transition shadow-2xs">
                                Iniciar Sesión para Fichar
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $freeAgents->links() }}
        </div>
    @endif
</div>

<!-- Modal: Contactar y Enviar Solicitud de Fichaje -->
<div id="modal-recruit" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Contactar y Solicitar Fichaje</h3>
                <p class="text-xs text-slate-500" id="modal-player-subtitle">Propuesta formal para el jugador</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-recruit').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="recruit-form" method="POST" action="" class="space-y-4">
            @csrf

            <div>
                <label for="recruit_team_id" class="block text-xs font-semibold text-slate-700 mb-1">Club que realiza la propuesta *</label>
                <select name="team_id" id="recruit_team_id" required class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-800 focus:bg-white focus:border-[#2b4e72] focus:outline-none">
                    @auth
                        @php
                            $user = auth()->user();
                            $teams = $user->isSuperAdmin() || $user->isAdmin() 
                                ? \App\Models\v1\Team::orderBy('name')->get() 
                                : $user->managedTeams()->get();
                        @endphp
                        @foreach ($teams as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->tournament?->name ?? 'Torneo' }})</option>
                        @endforeach
                    @endauth
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="proposed_jersey_number" class="block text-xs font-semibold text-slate-700 mb-1">Dorsal Propuesto (1 - 99)</label>
                    <input type="number" name="proposed_jersey_number" id="proposed_jersey_number" min="1" max="99" placeholder="Ej: 9" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                </div>
                <div>
                    <label for="contact_phone" class="block text-xs font-semibold text-slate-700 mb-1">Teléfono de Contacto (DT)</label>
                    <input type="text" name="contact_phone" id="contact_phone" placeholder="+57 300 123 4567" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                </div>
            </div>

            <div>
                <label for="contact_email" class="block text-xs font-semibold text-slate-700 mb-1">Correo Electrónico de Contacto</label>
                <input type="email" name="contact_email" id="contact_email" value="{{ auth()->user()?->email }}" placeholder="dt@miclub.com" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">
            </div>

            <div>
                <label for="message" class="block text-xs font-semibold text-slate-700 mb-1">Mensaje y Propuesta Deportiva para el Jugador *</label>
                <textarea name="message" id="message" rows="3" required placeholder="Hola, te vimos en el radar de talentos de SIGET y queremos invitarte a sumarte a nuestro plantel como titular. Te garantizamos minutos y uniforme oficial..." class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none"></textarea>
                <p class="text-[10px] text-slate-400 mt-1">El jugador o la comisión deportiva revisará tu mensaje y contactará contigo antes de formalizar la incorporación.</p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-recruit').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#2b4e72] hover:bg-[#2790b0] text-xs font-bold text-white transition shadow-2xs">
                    Enviar Solicitud de Fichaje
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Registrar Jugador Libre (Super Admin / Admin) -->
<div id="modal-add-free-agent" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Registrar Jugador en Bolsa de Talentos</h3>
                <p class="text-xs text-slate-500">Agrega un jugador a la Agencia Libre para que los clubes puedan ficharlo.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-add-free-agent').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('players.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="team_id" value="">

            <div>
                <label for="fa_name" class="block text-xs font-semibold text-slate-700 mb-1">Nombre Completo *</label>
                <input type="text" name="name" id="fa_name" required placeholder="Ej: Santiago Arias" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="fa_doc" class="block text-xs font-semibold text-slate-700 mb-1">Documento de Identidad *</label>
                    <input type="text" name="identification_document" id="fa_doc" required placeholder="CC-12345678" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                </div>
                <div>
                    <label for="fa_pos" class="block text-xs font-semibold text-slate-700 mb-1">Posición Principal *</label>
                    <select name="position" id="fa_pos" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                        <option value="forward">Delantero</option>
                        <option value="midfielder">Mediocampista</option>
                        <option value="defender">Defensa</option>
                        <option value="goalkeeper">Portero</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="fa_foot" class="block text-xs font-semibold text-slate-700 mb-1">Pie Hábil</label>
                    <select name="preferred_foot" id="fa_foot" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                        <option value="right">Diestro</option>
                        <option value="left">Zurdo</option>
                        <option value="ambidextrous">Ambidiestro</option>
                    </select>
                </div>
                <div>
                    <label for="fa_phone" class="block text-xs font-semibold text-slate-700 mb-1">Teléfono de Contacto</label>
                    <input type="text" name="contact_phone" id="fa_phone" placeholder="+57 310 987 6543" class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none">
                </div>
            </div>

            <div>
                <label for="fa_notes" class="block text-xs font-semibold text-slate-700 mb-1">Notas de Scouting / Trayectoria</label>
                <textarea name="scouting_notes" id="fa_notes" rows="2" placeholder="Experiencia en torneos departamentales, velocidad en desmarque y buen juego aéreo..." class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-xs text-slate-800 focus:border-[#2b4e72] focus:outline-none"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-free-agent').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#2b4e72] hover:bg-[#2790b0] text-xs font-bold text-white transition shadow-2xs">
                    Registrar en Bolsa de Talentos
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRecruitModal(playerId, playerName, position) {
        const form = document.getElementById('recruit-form');
        form.action = '/scouting/' + playerId + '/request-recruitment';
        document.getElementById('modal-player-subtitle').textContent = 'Propuesta de fichaje para ' + playerName + ' (' + position + ')';
        document.getElementById('modal-recruit').classList.remove('hidden');
    }
</script>
@endsection
