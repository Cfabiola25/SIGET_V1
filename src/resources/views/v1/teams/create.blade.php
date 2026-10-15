@extends('v1.layouts.app')

@section('title', 'Inscribir Nuevo Equipo')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('teams.index') }}" class="text-xs font-semibold uppercase tracking-wider text-[#1e3a8a] hover:underline flex items-center gap-1">
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a Equipos
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Inscribir Nuevo Club / Equipo</h1>
            <p class="text-xs text-slate-500">Registra un club participante para habilitar su nómina, Director Técnico y fixture.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-2xs">
        <form method="POST" action="{{ route('teams.store') }}" class="space-y-5">
            @csrf

            @if(request('redirect_to'))
                <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
            @endif

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="tournament_id" class="block text-xs font-semibold text-slate-700">Torneo Oficial *</label>
                    @if(auth()->user()?->isAdmin())
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Tus Torneos Asignados</span>
                    @else
                        <span class="text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full border border-sky-200">Todos los Torneos (Super Admin)</span>
                    @endif
                </div>
                <select name="tournament_id" id="tournament_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#1e3a8a] focus:outline-none">
                    @php
                        $targetId = old('tournament_id', $selectedTournamentId ?? request('tournament_id'));
                    @endphp
                    @forelse ($tournaments as $tournament)
                        <option value="{{ $tournament->id }}" {{ (string)$targetId === (string)$tournament->id ? 'selected' : '' }}>
                            {{ $tournament->name }} ({{ $tournament->sport_type }})
                        </option>
                    @empty
                        <option value="" disabled>No tienes torneos activos asignados</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre del Club / Equipo *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}" 
                       placeholder="Ej: Real Metropolitan FC" 
                       required 
                       class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#1e3a8a] focus:outline-none placeholder:text-slate-400">
            </div>

            <div>
                <label for="logo_path" class="block text-xs font-semibold text-slate-700 mb-1.5">URL o Ruta del Escudo / Logo (Opcional)</label>
                <input type="text" 
                       name="logo_path" 
                       id="logo_path" 
                       value="{{ old('logo_path') }}" 
                       placeholder="https://... o /logos/team.png" 
                       class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#1e3a8a] focus:outline-none placeholder:text-slate-400">
            </div>

            <!-- Sección Director Técnico -->
            <div class="space-y-3 p-4.5 bg-slate-50/80 border border-slate-200/90 rounded-xl">
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
                        <input type="radio" name="dt_mode" value="new" checked class="sr-only" onchange="toggleCreatePageDtMode(this.value)">
                        <span>+ Crear DT</span>
                    </label>
                    <label class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-md cursor-pointer transition has-[:checked]:bg-white has-[:checked]:text-[#1e3a8a] has-[:checked]:shadow-2xs">
                        <input type="radio" name="dt_mode" value="existing" class="sr-only" onchange="toggleCreatePageDtMode(this.value)">
                        <span>Existente</span>
                    </label>
                    <label class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-md cursor-pointer transition has-[:checked]:bg-white has-[:checked]:text-[#1e3a8a] has-[:checked]:shadow-2xs">
                        <input type="radio" name="dt_mode" value="none" class="sr-only" onchange="toggleCreatePageDtMode(this.value)">
                        <span>Sin DT</span>
                    </label>
                </div>

                <!-- Modo 1: Registrar Nuevo DT Aquí Mismo -->
                <div id="create-page-dt-new-fields" class="space-y-3 pt-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre Completo del DT *</label>
                            <input type="text" name="coach_name" value="{{ old('coach_name') }}" placeholder="Ej: Marcelo Gallardo" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email del DT (Login) *</label>
                            <input type="email" name="coach_email" value="{{ old('coach_email') }}" placeholder="dt.gallardo@siget.com" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Contraseña de Acceso</label>
                            <input type="text" name="coach_password" value="{{ old('coach_password', 'password') }}" placeholder="Mínimo 6 caracteres" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                            <span class="text-[10px] text-slate-500">Predeterminada: password</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Teléfono / WhatsApp (Opcional)</label>
                            <input type="text" name="coach_phone" value="{{ old('coach_phone') }}" placeholder="+57 300 123 4567" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                        </div>
                    </div>

                    <div class="rounded-lg bg-blue-50/80 border border-blue-200/80 p-2.5 text-[11px] text-blue-900 flex items-start gap-2">
                        <svg class="size-4 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong>Seguridad & Permisos:</strong> La cuenta se creará con rol de Director Técnico. Tendrá acceso <em>únicamente</em> a la gestión de este equipo (plantel y alineaciones) y al contacto/fichaje de agentes libres en el Radar de Talentos.</span>
                    </div>
                </div>

                <!-- Modo 2: Seleccionar DT Existente -->
                <div id="create-page-dt-existing-fields" class="space-y-2 pt-1 hidden">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Seleccionar DT Registrado</label>
                    <select name="existing_dt_id" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:border-[#1e3a8a]">
                        <option value="">-- Elige un Director Técnico registrado --</option>
                        @if(isset($coaches))
                            @foreach($coaches as $coach)
                                <option value="{{ $coach->id }}" {{ old('existing_dt_id') == $coach->id ? 'selected' : '' }}>
                                    {{ $coach->name }} ({{ $coach->email }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <span class="text-[10px] text-slate-500 block">Se le asignará la dirección técnica de este equipo con sus credenciales actuales.</span>
                </div>

                <!-- Modo 3: Sin DT -->
                <div id="create-page-dt-none-fields" class="pt-1 text-[11px] text-slate-500 hidden">
                    El equipo se inscribirá sin DT por ahora. Podrás asignarle un Director Técnico más adelante.
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('teams.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-[#1e3a8a] px-6 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-[#0284c7] transition flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Inscribir Equipo</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCreatePageDtMode(mode) {
    const newFields = document.getElementById('create-page-dt-new-fields');
    const existingFields = document.getElementById('create-page-dt-existing-fields');
    const noneFields = document.getElementById('create-page-dt-none-fields');
    if (!newFields || !existingFields || !noneFields) return;

    newFields.classList.toggle('hidden', mode !== 'new');
    existingFields.classList.toggle('hidden', mode !== 'existing');
    noneFields.classList.toggle('hidden', mode !== 'none');
}
</script>
@endsection

