@extends('v1.layouts.app')

@section('title', 'Director Técnico - Esperando Asignación')
@section('header_title', 'Portal del Director Técnico')

@section('header_badge')
<div class="flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-xs font-semibold text-amber-600 dark:text-amber-400">
    <span class="size-2 rounded-full bg-amber-500 animate-pulse"></span>
    <span>Sin Equipo Asignado</span>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    @if(session('warning'))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center gap-3">
            <x-reicon name="alert-triangle" class="size-5 shrink-0 text-amber-600" />
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <!-- Hero Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-[#0b132b] to-[#1e293b] text-white p-8 md:p-10 shadow-xl border border-slate-800">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -top-12 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-400/30 text-amber-300 text-xs font-bold tracking-wider uppercase">
                    <x-reicon name="shield-check" class="size-3.5" />
                    <span>Cuenta de DT Activa</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                    Bienvenido, Profe {{ $user->name }}
                </h2>
                <p class="text-slate-300 text-sm leading-relaxed">
                    Tu cuenta está registrada oficialmente con el rol de <strong class="text-white">Director Técnico</strong>. En este momento no estás vinculado a ningún club o equipo participante. Para comenzar a gestionar una nómina oficial, debes ser asignado por el Administrador del torneo o unirte a tu equipo mediante su enlace oficial de invitación.
                </p>
            </div>

            <div class="shrink-0 flex flex-col items-center justify-center p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                <div class="size-16 rounded-2xl bg-amber-500/20 border border-amber-400/40 flex items-center justify-center text-amber-300 mb-3 shadow-inner">
                    <x-reicon name="lock" class="size-8" />
                </div>
                <span class="text-xs font-bold text-slate-200">Gestión de Plantilla</span>
                <span class="text-[11px] font-extrabold text-amber-400 uppercase tracking-wider mt-0.5">En Espera de Equipo</span>
            </div>
        </div>
    </div>

    <!-- 3 Steps Explained -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
            <div class="size-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-sm">
                1
            </div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base">
                Solicita tu Asignación
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Pide al Administrador del torneo que te asigne como Director Técnico responsable de tu club participante.
            </p>
        </div>

        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
            <div class="size-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-sm">
                2
            </div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base">
                O Usa el Enlace de Invitación
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Si la organización te proporcionó un enlace de invitación oficial para el equipo, ábrelo en el navegador para reclamar tu club automáticamente.
            </p>
        </div>

        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
            <div class="size-10 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center font-black text-sm">
                3
            </div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base">
                Control Total de Plantilla
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Una vez vinculado a tu equipo, tendrás acceso a registrar futbolistas, importar nóminas CSV y armar tus alineaciones tácticas oficiales.
            </p>
        </div>

    </div>

    <!-- User Information & Actions Card -->
    <div class="p-6 md:p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <h4 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                <x-reicon name="info" class="size-4 text-slate-500" />
                <span>Detalles de tu Perfil Técnico</span>
            </h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs pt-2">
                <div>
                    <span class="text-slate-400 block font-medium">Nombre:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $user->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Rol Oficial:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">Director Técnico (DT)</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Equipos Asignados:</span>
                    <span class="font-bold text-amber-600 dark:text-amber-400">0 Equipos</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Estado Operativo:</span>
                    <span class="font-bold text-slate-500 dark:text-slate-400">Restringido (En Espera)</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-md transition">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Comprobar Asignación</span>
            </a>
        </div>
    </div>

</div>
@endsection
