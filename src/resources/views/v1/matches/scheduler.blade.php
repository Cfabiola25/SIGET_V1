@extends('v1.layouts.app')

@section('title', 'Programación de Partidos')
@section('header_title', 'Programación de Partidos')

@section('header_search')
<div class="relative w-64 md:w-80 hidden sm:block">
    <svg class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    <input type="text" placeholder="Buscar partidos, equipos..." 
           class="w-full pl-9 pr-4 py-2 text-xs md:text-sm bg-slate-50 border border-slate-200 rounded-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition">
</div>
@endsection

@section('content')
@php
    $carbonDate = \Carbon\Carbon::parse($date)->locale('es');
    $prevDate = $carbonDate->copy()->subDay()->format('Y-m-d');
    $nextDate = $carbonDate->copy()->addDay()->format('Y-m-d');
    $displayDate = $carbonDate->isoFormat('D [de] MMMM, YYYY');
@endphp

<div class="space-y-6">

    @if (session('status'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            <p class="font-bold">Por favor corrige los siguientes inconvenientes:</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Barra de Herramientas de Navegación de Fecha y Modos de Vista -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-3.5 px-6 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Selector de Fecha -->
        <div class="flex items-center gap-4">
            <a href="{{ route('matches.schedule', ['date' => $prevDate]) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition" title="Día Anterior">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-base md:text-lg font-bold text-slate-900 min-w-44 text-center capitalize">
                {{ $displayDate }}
            </h2>
            <a href="{{ route('matches.schedule', ['date' => $nextDate]) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition" title="Día Siguiente">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <!-- Botones de Modo: Hoy, Día, Semana -->
        <div class="flex items-center rounded-xl bg-slate-100 p-1 text-xs font-semibold text-slate-600">
            <a href="{{ route('matches.schedule', ['date' => now()->format('Y-m-d'), 'view' => 'today']) }}" 
               class="px-4 py-1.5 rounded-lg transition {{ $viewMode === 'today' || $date === now()->format('Y-m-d') ? 'bg-[#2b4e72] text-white font-bold shadow-2xs' : 'hover:text-slate-900' }}">
                Hoy
            </a>
            <a href="{{ route('matches.schedule', ['date' => $date, 'view' => 'day']) }}" 
               class="px-4 py-1.5 rounded-lg transition {{ $viewMode === 'day' && $date !== now()->format('Y-m-d') ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'hover:text-slate-900' }}">
                Día
            </a>
            <a href="{{ route('matches.schedule', ['date' => $date, 'view' => 'week']) }}" 
               class="px-4 py-1.5 rounded-lg transition {{ $viewMode === 'week' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'hover:text-slate-900' }}">
                Semana
            </a>
        </div>
    </div>

    <!-- Estructura Principal Dividida del Programador -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Izquierda: Cronograma por Canchas (2 Columnas) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            
            <!-- Encabezado de la Tabla de Canchas -->
            <div class="grid grid-cols-12 bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-700 py-3.5 px-4">
                <div class="col-span-2">Horario</div>
                <div class="col-span-5">Cancha 1 (Principal)</div>
                <div class="col-span-5">Cancha 2 (Alterna)</div>
            </div>

            <!-- Franjas Horarias -->
            <div class="divide-y divide-slate-100 text-sm">
                @php
                    $timeSlots = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];
                @endphp

                @foreach ($timeSlots as $slot)
                    @php
                        // Filtrar partidos para Cancha 1 y Cancha 2
                        $matchP1 = $matches->first(function($m) use ($slot) {
                            return ($m->field_number == 1 || $m->field_number == null) && $m->match_date?->format('H:i') === $slot;
                        });
                        $matchP2 = $matches->first(function($m) use ($slot) {
                            return $m->field_number == 2 && $m->match_date?->format('H:i') === $slot;
                        });
                    @endphp

                    <div class="grid grid-cols-12 min-h-20 items-stretch border-b border-slate-100 hover:bg-slate-50/50 transition">
                        
                        <!-- Etiqueta de Hora -->
                        <div class="col-span-2 p-3 text-xs font-mono font-bold text-slate-500 border-r border-slate-100 flex items-start pt-3">
                            {{ $slot }}
                        </div>

                        <!-- Columna Cancha 1 -->
                        <div class="col-span-5 p-2.5 border-r border-slate-100 flex flex-col justify-center">
                            @if ($matchP1)
                                <a href="{{ route('matches.show', $matchP1) }}" class="block bg-emerald-700 hover:bg-emerald-800 text-white p-3 rounded-xl shadow-xs transition cursor-pointer group">
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="font-bold text-xs truncate">
                                            {{ $matchP1->homeTeam?->name ?? 'Local' }} vs {{ $matchP1->awayTeam?->name ?? 'Visita' }}
                                        </div>
                                        <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 uppercase">
                                            {{ $matchP1->status === 'played' ? 'Final' : ($matchP1->status === 'live' ? 'En Vivo' : 'Programado') }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-emerald-100 font-medium">
                                        <span class="truncate">Árb: {{ $matchP1->referee?->name ?? 'Por asignar' }}</span>
                                        <span class="text-[10px] bg-black/20 px-1.5 py-0.5 rounded">{{ $matchP1->venue?->name ?? 'Cancha 1' }}</span>
                                    </div>
                                </a>
                            @else
                                <button type="button" 
                                        onclick="prefillTime('{{ $slot }}', 1)" 
                                        class="w-full h-full min-h-12 border border-dashed border-slate-200 rounded-xl text-slate-400 hover:text-[#2b4e72] hover:border-emerald-300 hover:bg-emerald-50/30 transition text-xs flex items-center justify-center gap-1 font-medium">
                                    <span>+ Disponible</span>
                                </button>
                            @endif
                        </div>

                        <!-- Columna Cancha 2 -->
                        <div class="col-span-5 p-2.5 flex flex-col justify-center">
                            @if ($matchP2)
                                <a href="{{ route('matches.show', $matchP2) }}" class="block bg-sky-700 hover:bg-sky-800 text-white p-3 rounded-xl shadow-xs transition cursor-pointer group">
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="font-bold text-xs truncate">
                                            {{ $matchP2->homeTeam?->name ?? 'Local' }} vs {{ $matchP2->awayTeam?->name ?? 'Visita' }}
                                        </div>
                                        <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 uppercase">
                                            {{ $matchP2->status === 'played' ? 'Final' : ($matchP2->status === 'live' ? 'En Vivo' : 'Programado') }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-sky-100 font-medium">
                                        <span class="truncate">Árb: {{ $matchP2->referee?->name ?? 'Por asignar' }}</span>
                                        <span class="text-[10px] bg-black/20 px-1.5 py-0.5 rounded">{{ $matchP2->venue?->name ?? 'Cancha 2' }}</span>
                                    </div>
                                </a>
                            @else
                                <button type="button" 
                                        onclick="prefillTime('{{ $slot }}', 2)" 
                                        class="w-full h-full min-h-12 border border-dashed border-slate-200 rounded-xl text-slate-400 hover:text-sky-700 hover:border-sky-300 hover:bg-sky-50/30 transition text-xs flex items-center justify-center gap-1 font-medium">
                                    <span>+ Disponible</span>
                                </button>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <!-- Derecha: Tarjeta de Formulario para Programar Nuevo Partido (1 Columna) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden p-6 sticky top-24">
            
            <!-- Encabezado -->
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="size-9 rounded-xl bg-emerald-50 text-[#2b4e72] border border-emerald-200 flex items-center justify-center font-bold text-xl shadow-2xs">
                    +
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 leading-tight">
                        Programar Partido
                    </h3>
                    <p class="text-xs text-slate-500">Asigna equipos, fecha, cancha y colegiado arbitral.</p>
                </div>
            </div>

            <!-- Formulario Oficial -->
            <form method="POST" action="{{ route('matches.schedule.store') }}" class="space-y-4">
                @csrf

                <!-- Fila de Equipos -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Equipo Local *</label>
                    <select name="home_team_id" id="home_team_id" required class="w-full text-xs px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-[#2b4e72] focus:outline-none">
                        <option value="">Seleccionar Equipo Local...</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" {{ old('home_team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }} ({{ $team->tournament?->name ?? 'Torneo' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Equipo Visitante *</label>
                    <select name="away_team_id" id="away_team_id" required class="w-full text-xs px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-[#2b4e72] focus:outline-none">
                        <option value="">Seleccionar Equipo Visitante...</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" {{ old('away_team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }} ({{ $team->tournament?->name ?? 'Torneo' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fila de Fecha y Hora -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha *</label>
                        <input type="date" name="date" id="match_date_input" value="{{ old('date', $date) }}" required 
                               class="w-full text-xs px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-[#2b4e72] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hora *</label>
                        <input type="time" name="time" id="match_time_input" value="{{ old('time', '09:00') }}" required 
                               class="w-full text-xs px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-[#2b4e72] focus:outline-none">
                    </div>
                </div>

                <!-- Sede Deportiva -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sede Deportiva *</label>
                    <select name="venue_id" id="venue_id" class="w-full text-xs px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-[#2b4e72] focus:outline-none">
                        @foreach ($venues as $v)
                            <option value="{{ $v->id }}" {{ old('venue_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }} ({{ $v->city ?? 'Principal' }} - {{ $v->field_count }} canchas)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Número de Cancha -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cancha *</label>
                    <select name="field_number" id="field_number_select" class="w-full text-xs px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-[#2b4e72] focus:outline-none">
                        <option value="1" {{ old('field_number') == '1' ? 'selected' : '' }}>Cancha 1 (Principal)</option>
                        <option value="2" {{ old('field_number') == '2' ? 'selected' : '' }}>Cancha 2 (Alterna)</option>
                        <option value="3" {{ old('field_number') == '3' ? 'selected' : '' }}>Cancha 3</option>
                        <option value="4" {{ old('field_number') == '4' ? 'selected' : '' }}>Cancha 4</option>
                    </select>
                </div>

                <!-- Árbitro Asignado -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Colegiado Arbitral</label>
                    <select name="referee_id" class="w-full text-xs px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-[#2b4e72] focus:outline-none">
                        <option value="auto">Asignación Automática (Sistema de Desempeño)</option>
                        @foreach ($referees as $referee)
                            <option value="{{ $referee->id }}" {{ old('referee_id') == $referee->id ? 'selected' : '' }}>
                                {{ $referee->name }} (Lic: {{ $referee->license_number ?? 'Oficial' }} • {{ number_format($referee->rating_average ?? 5.0, 1) }}★)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Acciones -->
                <div class="grid grid-cols-2 gap-3 pt-3">
                    <button type="reset" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition text-center cursor-pointer">
                        Limpiar
                    </button>
                    <button type="submit" class="w-full py-2.5 px-4 bg-[#2b4e72] hover:bg-[#2790b0] active:bg-[#1e3852] text-white text-xs font-bold rounded-lg shadow-xs transition text-center cursor-pointer">
                        Programar Partido
                    </button>
                </div>
            </form>

        </div>

    </div>

</div>

<script>
    function prefillTime(time, fieldNum) {
        const timeInput = document.getElementById('match_time_input');
        const fieldSelect = document.getElementById('field_number_select');
        if (timeInput) timeInput.value = time;
        if (fieldSelect) fieldSelect.value = fieldNum;
        
        // Efecto visual de foco en el formulario
        const homeSelect = document.getElementById('home_team_id');
        if (homeSelect) {
            homeSelect.focus();
            homeSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
</script>
@endsection
