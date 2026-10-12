@extends('v1.layouts.app')

@section('title', 'Solicitudes de Fichaje y Contacto')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.scouting.index') }}" class="text-xs font-semibold uppercase tracking-wider text-[#1e3a8a] hover:underline flex items-center gap-1">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Volver al Radar de Scouting
                </a>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Solicitudes de Fichaje y Contacto</h1>
            <p class="text-xs text-slate-500 mt-0.5">Gestión de propuestas deportivas, acuerdos de fichaje y transferencias de agentes libres.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.scouting.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#1e3a8a] px-4 py-2.5 text-xs font-semibold text-white hover:bg-[#0284c7] transition shadow-2xs">
                <x-reicon name="search" class="size-4" />
                <span>Explorar Agentes Libres</span>
            </a>
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
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Tabla Principal de Solicitudes -->
    <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <x-reicon name="file-text" class="size-4 text-emerald-600" />
                <span>Historial y Solicitudes en Proceso</span>
            </h2>
            <span class="text-xs text-slate-500">Total: {{ $requests->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-semibold">
                    <tr>
                        <th class="py-3 px-4">Jugador / Agente Libre</th>
                        <th class="py-3 px-4">Club Solicitante</th>
                        <th class="py-3 px-4">Contacto y Mensaje del DT</th>
                        <th class="py-3 px-4 text-center">Dorsal Propuesto</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($requests as $req)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 flex items-center gap-2">
                                    <span class="size-7 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs border border-slate-200">
                                        {{ strtoupper(substr($req->player->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <a href="{{ route('scouting.show', $req->player) }}" class="hover:text-[#1e3a8a] hover:underline">
                                            {{ $req->player->name }}
                                        </a>
                                        <div class="text-[10px] text-slate-400 font-normal">
                                            Doc: {{ $req->player->identification_document }} • {{ ucfirst($req->player->profile?->position ?? 'Jugador') }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-900 block">{{ $req->team->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $req->team->tournament?->name ?? 'Torneo' }}</span>
                            </td>

                            <td class="py-3 px-4 max-w-xs">
                                <div class="text-slate-700 italic bg-slate-50 p-2 rounded-lg border border-slate-100 text-[11px]">
                                    "{{ $req->message }}"
                                </div>
                                <div class="mt-1 flex items-center gap-2 text-[10px] text-slate-500">
                                    <span>DT: <strong>{{ $req->requester?->name ?? 'Director Técnico' }}</strong></span>
                                    @if ($req->contact_phone)
                                        <span>• Tel: {{ $req->contact_phone }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                {{ $req->proposed_jersey_number ? '#' . $req->proposed_jersey_number : 'A convenir' }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if ($req->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Pendiente
                                    </span>
                                @elseif ($req->status === 'accepted')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        ✓ Fichaje Aceptado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Rechazada
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-right">
                                @if ($req->status === 'pending' && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || $req->player->user_id === auth()->id()))
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('scouting.requests.accept', $req) }}" onsubmit="return confirm('¿Aceptar propuesta y oficializar el fichaje de {{ $req->player->name }} por el club {{ $req->team->name }}?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-[#1e3a8a] hover:bg-[#0284c7] text-white rounded-lg text-xs font-bold transition shadow-2xs">
                                                Aceptar Fichaje
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('scouting.requests.reject', $req) }}" onsubmit="return confirm('¿Declinar esta propuesta de fichaje?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-semibold transition">
                                                Declinar
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($req->status === 'pending')
                                    <span class="text-[11px] text-slate-400 italic">Esperando respuesta</span>
                                @else
                                    <span class="text-[11px] text-slate-400">Procesado el {{ $req->responded_at?->format('d/m/Y') ?? 'Reciente' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <x-reicon name="clipboard-list" class="size-8 text-slate-300 mx-auto block mb-2" />
                                No hay solicitudes de fichaje registradas actualmente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($requests->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
