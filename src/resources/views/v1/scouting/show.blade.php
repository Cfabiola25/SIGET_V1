@extends('v1.layouts.app')

@section('title', "Radar de {$player->name}")

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('scouting.index') }}" class="text-xs font-semibold text-[#057a55] hover:underline flex items-center gap-1">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Radar de Talentos
                </a>
                <span class="text-slate-300">•</span>
                <span class="text-xs text-slate-500 font-medium">Ficha Técnica Algorítmica</span>
            </div>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $player->name }}</h1>
            <p class="text-xs text-slate-500">{{ $player->profile?->position_label ?? 'Mediocampista' }} • {{ $player->profile?->preferred_foot_label ?? 'Diestro' }} • {{ $player->profile?->nationality ?? 'Nacional' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('players.cromo', $player) }}" target="_blank" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 shadow-2xs">
                Cromo Coleccionable
            </a>
            <a href="{{ route('players.carnet', $player) }}" target="_blank" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 shadow-2xs">
                Carnet QR
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->has('scouting'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Columna 1: Radar Gráfico de Habilidades -->
        <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Pentágono de Rendimiento</h3>
            <div class="relative size-60 sm:size-64">
                @php
                    $cx = 140; $cy = 140; $r = 100;
                    $scores = [
                        $metrics['scoring'] / 10,
                        $metrics['discipline'] / 10,
                        $metrics['leadership'] / 10,
                        $metrics['consistency'] / 10,
                        $metrics['overall'] / 10,
                    ];
                    $points = [];
                    foreach ($scores as $i => $s) {
                        $angle = deg2rad(-90 + ($i * 72));
                        $px = $cx + ($r * $s * cos($angle));
                        $py = $cy + ($r * $s * sin($angle));
                        $points[] = "{$px},{$py}";
                    }
                    $polyPoints = implode(' ', $points);
                @endphp
                <svg viewBox="0 0 280 280" class="size-full">
                    <!-- Concentric Circles -->
                    <circle cx="140" cy="140" r="100" fill="none" stroke="#e2e8f0" stroke-dasharray="4 4" stroke-width="1.5"/>
                    <circle cx="140" cy="140" r="75" fill="none" stroke="#e2e8f0" stroke-dasharray="3 3" stroke-width="1"/>
                    <circle cx="140" cy="140" r="50" fill="none" stroke="#e2e8f0" stroke-dasharray="2 2" stroke-width="1"/>
                    <circle cx="140" cy="140" r="25" fill="none" stroke="#e2e8f0" stroke-dasharray="1 1" stroke-width="1"/>

                    <!-- Axes -->
                    @for($i=0; $i<5; $i++)
                        @php
                            $a = deg2rad(-90 + ($i * 72));
                            $ax = 140 + (100 * cos($a));
                            $ay = 140 + (100 * sin($a));
                        @endphp
                        <line x1="140" y1="140" x2="{{ $ax }}" y2="{{ $ay }}" stroke="#cbd5e1" stroke-width="1.5"/>
                    @endfor

                    <!-- Radar Polygon Area -->
                    <polygon points="{{ $polyPoints }}" fill="rgba(5, 122, 85, 0.2)" stroke="#057a55" stroke-width="2.5"/>

                    <!-- Vertex Dots -->
                    @foreach($points as $pt)
                        @php [$px, $py] = explode(',', $pt); @endphp
                        <circle cx="{{ $px }}" cy="{{ $py }}" r="4.5" fill="#057a55" stroke="#ffffff" stroke-width="2"/>
                    @endforeach
                </svg>
            </div>

            <!-- Labels for the 5 vertices -->
            <div class="mt-4 grid grid-cols-2 gap-2 w-full text-xs font-semibold">
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-2 border border-slate-100">
                    <span class="text-slate-500">Goleador:</span>
                    <span class="text-emerald-700 font-bold">{{ $metrics['scoring'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-2 border border-slate-100">
                    <span class="text-slate-500">Disciplina:</span>
                    <span class="text-emerald-700 font-bold">{{ $metrics['discipline'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-2 border border-slate-100">
                    <span class="text-slate-500">Liderazgo/MVP:</span>
                    <span class="text-amber-600 font-bold">{{ $metrics['leadership'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-2 border border-slate-100">
                    <span class="text-slate-500">Regularidad:</span>
                    <span class="text-blue-700 font-bold">{{ $metrics['consistency'] }}</span>
                </div>
            </div>
        </div>

        <!-- Columna 2: Ficha Técnica & Estadísticas -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Overall Score Card -->
            <div class="flex items-center justify-between rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Calificación Algorítmica SIGET</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($metrics['overall'], 1) }} <x-reicon name="star" variant="filled" class="size-5 text-amber-500 inline-block" /></h2>
                    <p class="text-xs text-slate-500 mt-1">Calculado con base en partidos disputados, goles, tarjetas y premios MVP.</p>
                </div>
                <div class="text-right">
                    @if($player->team)
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                            En Plantel: {{ $player->team->name }} (#{{ $player->jersey_number ?? 'S/N' }})
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-800 border border-cyan-200">
                            Agente Libre Disponible
                        </span>
                    @endif
                </div>
            </div>

            <!-- Detailed Grid of Metrics -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 text-center shadow-2xs">
                    <span class="block text-2xl font-extrabold text-slate-900">{{ $metrics['matches_played'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">Partidos Oficiales</span>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 text-center shadow-2xs">
                    <span class="block text-2xl font-extrabold text-emerald-700">{{ $metrics['goals_scored'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">Goles Marcados</span>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 text-center shadow-2xs">
                    <span class="block text-2xl font-extrabold text-amber-600">{{ $metrics['mvp_awards'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">Premios MVP</span>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 text-center shadow-2xs">
                    <span class="block text-2xl font-extrabold text-rose-600">{{ $player->redCardsCount() }}</span>
                    <span class="text-xs font-semibold text-slate-500">Tarjetas Rojas</span>
                </div>
            </div>

            <!-- Contact Info & Scouting Notes -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Notas de Scouting & Perfil</h3>
                    @if($player->isFreeAgent())
                        <span class="inline-flex items-center gap-1 rounded-full bg-cyan-50 px-2.5 py-0.5 text-[11px] font-bold text-cyan-800 border border-cyan-200">
                            <span class="size-1.5 rounded-full bg-cyan-600 animate-pulse"></span>
                            Bolsa de Talentos
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    {{ $player->profile?->scouting_notes ?? 'Jugador con perfil versátil y disciplina táctica comprobada. Ha demostrado regularidad en las convocatorias y capacidad de adaptación en distintas zonas del terreno.' }}
                </p>

                <!-- Datos Oficiales de Contacto para Fichajes -->
                <div class="pt-3 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-800 mb-2">Canales de Contacto Directo</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="size-7 rounded-lg bg-emerald-100 text-[#057a55] flex items-center justify-center font-bold">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-medium">Teléfono / WhatsApp</div>
                                    <div class="font-bold text-slate-800">{{ $player->profile?->contact_phone ?? 'No registrado' }}</div>
                                </div>
                            </div>
                            @if($player->profile?->contact_phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $player->profile->contact_phone) }}" target="_blank" class="px-2.5 py-1 rounded bg-[#057a55] text-white text-[10px] font-bold hover:bg-[#046c4b] transition">
                                    WhatsApp
                                </a>
                            @endif
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-2.5">
                            <div class="size-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400 font-medium">Correo Electrónico</div>
                                <div class="font-bold text-slate-800 truncate">{{ $player->profile?->contact_email ?? 'No registrado' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $currentUser = auth()->user();
                $isDT = $currentUser && ($currentUser->isCoach() || $currentUser->isCaptain());
                $isSuperAdmin = $currentUser?->isSuperAdmin() ?? false;
                $isAdmin = $currentUser?->isAdmin() ?? false;
                $myPendingRequest = $currentUser ? \App\Models\v1\PlayerRecruitmentRequest::where('player_id', $player->id)->where('requested_by_user_id', $currentUser->id)->where('status', 'pending')->first() : null;
            @endphp

            <!-- Flujo de Fichaje para DTs (Propuesta previa y Solicitud) -->
            @if($player->isFreeAgent() && $isDT)
                @if($myPendingRequest)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-6 shadow-2xs">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-amber-950">Solicitud de Fichaje en Proceso</h3>
                                <p class="text-xs text-amber-800 mt-0.5">
                                    Has enviado una propuesta para fichar a {{ $player->name }} en <strong>{{ $myPendingRequest->team->name }}</strong> (Dorsal propuesto #{{ $myPendingRequest->proposed_jersey_number ?? 'S/N' }}).
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between pt-3 border-t border-amber-200/60 text-xs">
                            <span class="text-amber-800 font-medium">En espera de aceptación formal.</span>
                            <a href="{{ route('scouting.requests.index') }}" class="font-bold text-[#057a55] hover:underline">
                                Ver Mis Solicitudes &rarr;
                            </a>
                        </div>
                    </div>
                @elseif($availableTeams->isNotEmpty())
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-6 shadow-2xs">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-emerald-950">Contactar y Solicitar Fichaje</h3>
                                <p class="text-xs text-emerald-800 mt-0.5">Ponte en contacto con el jugador y remítele una propuesta formal de incorporación.</p>
                            </div>
                            <span class="text-[11px] font-bold bg-[#057a55] text-white px-2.5 py-1 rounded-lg">
                                Protocolo Oficial
                            </span>
                        </div>

                        <form method="POST" action="{{ route('scouting.recruit.request', $player) }}" class="space-y-3 mt-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Club Destino</label>
                                    <select name="team_id" required class="w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                                        @foreach($availableTeams as $t)
                                            <option value="{{ $t->id }}">Fichar en {{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Dorsal Propuesto (1-99)</label>
                                    <input type="number" name="proposed_jersey_number" placeholder="Ej: 10" min="1" max="99" class="w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Teléfono de Contacto del Club / DT</label>
                                    <input type="text" name="contact_phone" value="{{ auth()->user()->phone ?? '' }}" placeholder="+57 300 000 0000" class="w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Correo de Contacto</label>
                                    <input type="email" name="contact_email" value="{{ auth()->user()->email }}" class="w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Mensaje / Propuesta al Jugador</label>
                                <textarea name="message" rows="2" required placeholder="Hola {{ $player->name }}, nos gustaría que te unas a nuestro club para disputar el torneo..." class="w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none"></textarea>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <span class="text-[11px] text-slate-500">Se notificará la propuesta para su debida aceptación.</span>
                                <button type="submit" class="rounded-lg bg-[#057a55] px-5 py-2.5 text-xs font-semibold text-white hover:bg-[#046c4b] transition shadow-2xs">
                                    Enviar Propuesta Formal de Fichaje
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @elseif($player->isFreeAgent() && ($isSuperAdmin || $isAdmin))
                <!-- Reclutamiento Administrativo Directo -->
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-6 shadow-2xs">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-bold text-emerald-950">Asignar Jugador a un Club (Gestión Administrativa)</h3>
                        <a href="{{ route('scouting.requests.index') }}" class="text-xs font-bold text-[#057a55] hover:underline">
                            Ver Solicitudes Pendientes &rarr;
                        </a>
                    </div>
                    <p class="text-xs text-emerald-800 mb-4">Como administrador puedes formalizar el fichaje inmediato en un club del torneo.</p>
                    <form method="POST" action="{{ route('scouting.recruit', $player) }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        @csrf
                        <select name="team_id" required class="flex-1 rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                            @foreach($availableTeams as $t)
                                <option value="{{ $t->id }}">Asignar a {{ $t->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="jersey_number" placeholder="Dorsal (1-99)" min="1" max="99" class="w-32 rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none">
                        <button type="submit" class="rounded-lg bg-[#057a55] px-5 py-2 text-xs font-semibold text-white hover:bg-[#046c4b] transition shadow-2xs">
                            Formalizar Fichaje
                        </button>
                    </form>
                </div>
            @endif

            <!-- Release to free agency button if user manages this team or is admin -->
            @if($player->team && ($availableTeams->pluck('id')->contains($player->team_id) || $isSuperAdmin))
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 flex items-center justify-between shadow-2xs">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Liberar a la Agencia Libre</h4>
                        <p class="text-[11px] text-slate-500">El jugador saldrá del plantel de {{ $player->team->name }} y quedará disponible para que otros clubes lo fichen.</p>
                    </div>
                    <form method="POST" action="{{ route('scouting.release', $player) }}" onsubmit="return confirm('¿Seguro que deseas liberar a este jugador a la agencia libre del torneo?')">
                        @csrf
                        <button type="submit" class="rounded-lg border border-rose-200 bg-white px-3.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition shadow-2xs">
                            Liberar Jugador
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
