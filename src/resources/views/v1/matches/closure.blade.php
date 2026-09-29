@extends('v1.layouts.app')

@section('title', 'Cierre y Firma de Acta Digital')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Back Navigation & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('matches.console', $match) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[#057a55] hover:underline transition">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a Consola Arbitral
            </a>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Cierre de Encuentro y Firma Tripartita</h1>
            <p class="text-xs text-slate-500">Validación y certificación reglamentaria (Árbitro, DT Local, DT Visitante) conforme a IFAB / FIFA.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 border border-amber-200 text-amber-800">
                <span class="size-2 rounded-full bg-amber-500 animate-pulse"></span>
                Pendiente de Firmas
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            <p class="font-bold mb-1">Por favor verifica los siguientes campos requeridos:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Match Result Card -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 py-2">
            <!-- Home Team -->
            <div class="flex-1 text-center md:text-right">
                <h2 class="text-xl font-bold text-slate-900">{{ $match->homeTeam->name }}</h2>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Equipo Local</span>
            </div>

            <!-- Final Score Display -->
            <div class="flex flex-col items-center px-8 py-3 bg-slate-50 rounded-2xl border border-slate-200">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Marcador Final</span>
                <div class="flex items-center gap-3 text-4xl sm:text-5xl font-black font-mono text-slate-900">
                    <span>{{ $match->home_score }}</span>
                    <span class="text-slate-300">:</span>
                    <span>{{ $match->away_score }}</span>
                </div>
                <span class="text-[11px] text-emerald-700 font-medium mt-1">Tiempo reglamentario cumplido</span>
            </div>

            <!-- Away Team -->
            <div class="flex-1 text-center md:text-left">
                <h2 class="text-xl font-bold text-slate-900">{{ $match->awayTeam->name }}</h2>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Equipo Visitante</span>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs text-slate-500 text-center">
            <div>
                <span class="block text-slate-400 text-[11px]">Torneo</span>
                <strong class="text-slate-800">{{ $match->tournament->name }}</strong>
            </div>
            <div>
                <span class="block text-slate-400 text-[11px]">Sede</span>
                <strong class="text-slate-800">{{ $match->venue?->name ?? 'Por definir' }}</strong>
            </div>
            <div>
                <span class="block text-slate-400 text-[11px]">Fecha</span>
                <strong class="text-slate-800">{{ $match->match_date->format('d/m/Y H:i') }}</strong>
            </div>
            <div>
                <span class="block text-slate-400 text-[11px]">Árbitro Designado</span>
                <strong class="text-slate-800">{{ $match->referee?->name ?? 'Colegiado Oficial' }}</strong>
            </div>
        </div>
    </div>

    <!-- Incidents and Events Summary -->
    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-3">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <svg class="size-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Resumen de Incidencias Registradas ({{ $match->events->count() }})
        </h3>
        @if ($match->events->isEmpty())
            <p class="text-xs text-slate-400 py-4 italic text-center">No se registraron incidencias durante el encuentro.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-56 overflow-y-auto pr-1">
                @foreach ($match->events as $event)
                    <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-slate-50 border border-slate-200/80 text-xs">
                        <span class="text-base">{{ $event->icon }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-900 truncate">{{ $event->player?->name ?? $event->team->name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $event->label }} &bull; {{ $event->formatted_time }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Signature Form -->
    <form id="closure-form" action="{{ route('matches.closure.submit', $match) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Tripartite Signatures Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 1. Referee Signature -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="size-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold">1</span>
                            Árbitro Principal
                        </h4>
                        <button type="button" onclick="clearCanvas('referee')" class="text-[11px] text-slate-400 hover:text-rose-600 transition font-medium">Limpiar</button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Firma en pantalla certificando el resultado.</p>

                    <div class="mt-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre y Apellido</label>
                        <input type="text" name="referee_name" id="referee_name" value="{{ old('referee_name', $match->referee?->name ?? auth()->user()->name) }}" required class="w-full rounded-lg bg-white border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-[#057a55] focus:outline-none">
                    </div>

                    <div class="mt-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Firma Digital</label>
                        <div class="relative w-full h-36 bg-slate-50 rounded-xl border border-slate-300 border-dashed overflow-hidden touch-none cursor-crosshair">
                            <canvas id="canvas-referee" class="w-full h-full"></canvas>
                            <span class="absolute bottom-2 right-2 text-[10px] text-slate-400 pointer-events-none select-none">Panel Táctil Árbitro</span>
                        </div>
                        <input type="hidden" name="referee_signature" id="input-referee">
                    </div>
                </div>
            </div>

            <!-- 2. Home Coach / Captain Signature -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="size-6 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center text-xs font-bold">2</span>
                            DT / Delegado Local
                        </h4>
                        <button type="button" onclick="clearCanvas('home_coach')" class="text-[11px] text-slate-400 hover:text-rose-600 transition font-medium">Limpiar</button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Representante de: <strong class="text-slate-800">{{ $match->homeTeam->name }}</strong></p>

                    <div class="mt-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del DT / Capitán</label>
                        <input type="text" name="home_coach_name" id="home_coach_name" value="{{ old('home_coach_name', $match->homeTeam->coach_name) }}" required class="w-full rounded-lg bg-white border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-[#057a55] focus:outline-none">
                    </div>

                    <div class="mt-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Firma Digital</label>
                        <div class="relative w-full h-36 bg-slate-50 rounded-xl border border-slate-300 border-dashed overflow-hidden touch-none cursor-crosshair">
                            <canvas id="canvas-home_coach" class="w-full h-full"></canvas>
                            <span class="absolute bottom-2 right-2 text-[10px] text-slate-400 pointer-events-none select-none">Firma DT Local</span>
                        </div>
                        <input type="hidden" name="home_coach_signature" id="input-home_coach">
                    </div>
                </div>
            </div>

            <!-- 3. Away Coach / Captain Signature -->
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="size-6 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center text-xs font-bold">3</span>
                            DT / Delegado Visitante
                        </h4>
                        <button type="button" onclick="clearCanvas('away_coach')" class="text-[11px] text-slate-400 hover:text-rose-600 transition font-medium">Limpiar</button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Representante de: <strong class="text-slate-800">{{ $match->awayTeam->name }}</strong></p>

                    <div class="mt-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del DT / Capitán</label>
                        <input type="text" name="away_coach_name" id="away_coach_name" value="{{ old('away_coach_name', $match->awayTeam->coach_name) }}" required class="w-full rounded-lg bg-white border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-[#057a55] focus:outline-none">
                    </div>

                    <div class="mt-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Firma Digital</label>
                        <div class="relative w-full h-36 bg-slate-50 rounded-xl border border-slate-300 border-dashed overflow-hidden touch-none cursor-crosshair">
                            <canvas id="canvas-away_coach" class="w-full h-full"></canvas>
                            <span class="absolute bottom-2 right-2 text-[10px] text-slate-400 pointer-events-none select-none">Firma DT Visitante</span>
                        </div>
                        <input type="hidden" name="away_coach_signature" id="input-away_coach">
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes & Observations -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-3">
            <label for="match_sheet_notes" class="block text-sm font-bold text-slate-900">Observaciones e Informe Arbitral Adicional</label>
            <p class="text-xs text-slate-500">Describe conducta antideportiva, reclamaciones formales, lesiones de gravedad o anomalías ocurridas durante o después del cotejo.</p>
            <textarea name="match_sheet_notes" id="match_sheet_notes" rows="3" class="w-full rounded-lg bg-white border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#057a55] focus:outline-none" placeholder="Sin observaciones extraordinarias. El encuentro transcurrió bajo las reglas de juego IFAB.">{{ old('match_sheet_notes', $match->match_sheet_notes) }}</textarea>
        </div>

        <!-- Legal Warning & Submit Button -->
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3">
            <svg class="size-5 shrink-0 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="leading-relaxed">
                <strong class="font-bold">Efecto Inmutable del Cierre:</strong> Al certificar y guardar el acta, el partido pasará a estado <em>Cerrado (Locked)</em> de forma definitiva. Inmediatamente se disparará el motor de cálculo de posiciones y las sanciones disciplinarias reglamentarias.
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('matches.console', $match) }}" class="px-5 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                Cancelar
            </a>
            <button type="submit" id="btn-submit-closure" class="px-6 py-2.5 rounded-lg bg-[#057a55] hover:bg-[#046c4b] text-white font-semibold text-xs transition shadow-2xs flex items-center gap-2">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Certificar Acta y Bloquear Partido
            </button>
        </div>
    </form>
</div>

<!-- Canvas Drawing Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roles = ['referee', 'home_coach', 'away_coach'];
        const canvases = {};
        const contexts = {};
        const drawingStates = {};

        roles.forEach(role => {
            const canvas = document.getElementById(`canvas-${role}`);
            if (!canvas) return;

            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * 2;
            canvas.height = rect.height * 2;

            const ctx = canvas.getContext('2d');
            ctx.scale(2, 2);
            ctx.strokeStyle = '#057a55';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            canvases[role] = canvas;
            contexts[role] = ctx;
            drawingStates[role] = { isDrawing: false, hasDrawn: false };

            function getCoords(e) {
                const r = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - r.left,
                    y: clientY - r.top
                };
            }

            function startDraw(e) {
                e.preventDefault();
                drawingStates[role].isDrawing = true;
                drawingStates[role].hasDrawn = true;
                const coords = getCoords(e);
                ctx.beginPath();
                ctx.moveTo(coords.x, coords.y);
            }

            function draw(e) {
                if (!drawingStates[role].isDrawing) return;
                e.preventDefault();
                const coords = getCoords(e);
                ctx.lineTo(coords.x, coords.y);
                ctx.stroke();
            }

            function stopDraw() {
                drawingStates[role].isDrawing = false;
            }

            canvas.addEventListener('mousedown', startDraw);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', stopDraw);

            canvas.addEventListener('touchstart', startDraw, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', stopDraw);
        });

        window.clearCanvas = function(role) {
            const canvas = canvases[role];
            const ctx = contexts[role];
            if (!canvas || !ctx) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawingStates[role].hasDrawn = false;
            const hiddenInput = document.getElementById(`input-${role}`);
            if (hiddenInput) hiddenInput.value = '';
        };

        const form = document.getElementById('closure-form');
        form.addEventListener('submit', function (e) {
            roles.forEach(role => {
                const canvas = canvases[role];
                const input = document.getElementById(`input-${role}`);
                if (canvas && input) {
                    input.value = canvas.toDataURL('image/png');
                }
            });
        });
    });
</script>
@endsection
