<!DOCTYPE html>
<html lang="es" class="h-full bg-[#f4f6fa]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGET') | Gestión Deportiva y Torneos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <meta name="theme-color" content="#2b4e72">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --font-display: 'Outfit', -apple-system, sans-serif;
            --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        body {
            font-family: var(--font-sans);
        }
        .font-display {
            font-family: var(--font-display);
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
    </style>
    <!-- Immediate Theme & Energy-Saving Initialization (Zero Flash) -->
    <script>
        (function() {
            const THEME_KEY = 'siget_athletix_theme';
            const saved = localStorage.getItem(THEME_KEY);
            let isDark = false;
            if (saved === 'dark') {
                isDark = true;
            } else if (saved === 'light') {
                isDark = false;
            } else {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    isDark = true;
                }
            }
            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
</head>
<body class="h-full bg-[#f4f6fa] text-slate-800 antialiased flex flex-col md:flex-row overflow-x-hidden">

    @php
        $authUser = auth()->user();
        $isSuperAdmin = $authUser?->isSuperAdmin() ?? false;
        $isAdmin = $authUser?->isAdmin() ?? false;
        $isDT = ($authUser?->isCaptain() || $authUser?->isCoach()) ?? false;
        $isReferee = $authUser?->isReferee() ?? false;
        $myTeam = $isDT ? $authUser->managedTeams()->first() : null;

        $roleLabel = match(true) {
            $isSuperAdmin => 'Super Administrador',
            $isAdmin => 'Director de Torneo',
            $isDT => 'Director Técnico (DT)',
            $isReferee => 'Árbitro Oficial',
            default => 'Usuario de Sistema',
        };

        $roleBadge = match(true) {
            $isSuperAdmin => 'GLOBAL',
            $isAdmin => 'ORGANIZADOR',
            $isDT => 'DT CLUB',
            $isReferee => 'ÁRBITRO',
            default => 'INVITADO',
        };
    @endphp

    <!-- Mobile Header -->
    <header class="md:hidden flex items-center justify-between bg-[#353432] text-white px-4 py-3 border-b border-slate-700/50 z-50">
        <div class="flex items-center gap-3">
            <div class="size-8 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-white text-sm shadow">
                S
            </div>
            <div>
                <div class="text-sm font-bold leading-none">SIGET</div>
                <div class="text-[10px] text-slate-400">{{ $roleLabel }}</div>
            </div>
        <div class="flex items-center gap-2">
            <x-theme-toggle id="mobile-app-theme-switch" />
            <button type="button" onclick="document.getElementById('siget-sidebar').classList.toggle('-translate-x-full')" class="p-2 text-slate-300 hover:text-white focus:outline-none">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </header>

    <!-- Sidebar Navigation (Dark Slate #353432) -->
    <aside id="siget-sidebar" class="fixed inset-y-0 left-0 z-40 w-64 md:w-68 bg-[#353432] text-slate-300 flex flex-col transition-transform duration-300 ease-in-out md:static md:translate-x-0 -translate-x-full shrink-0 border-r border-[#4e4d4a] shadow-xl md:shadow-none">
        
        <!-- Sidebar Profile Header -->
        <div class="p-5 flex items-center gap-3.5 border-b border-[#4e4d4a]/60">
            <div class="relative">
                <div class="size-11 rounded-full bg-emerald-600 border-2 border-emerald-400/40 flex items-center justify-center text-white font-black text-base shadow-md">
                    @auth
                        {{ strtoupper(substr($authUser->name, 0, 1)) }}
                    @else
                        S
                    @endauth
                </div>
                <span class="absolute bottom-0 right-0 size-3 rounded-full bg-emerald-500 ring-2 ring-[#353432]"></span>
            </div>
            <div class="flex flex-col min-w-0">
                <div class="flex items-center gap-1.5">
                    <h1 class="text-[14px] font-bold text-white tracking-tight truncate leading-tight">
                        @auth
                            {{ $authUser->name }}
                        @else
                            SIGET Admin
                        @endauth
                    </h1>
                </div>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-[10px] uppercase font-extrabold tracking-wider bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-500/30">
                        {{ $roleBadge }}
                    </span>
                    <p class="text-xs text-slate-400 font-medium truncate">
                        {{ $roleLabel }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Role-based Call to Action Button -->
        <div class="px-5 pt-4 pb-2">
            @if($isSuperAdmin || $isAdmin)
                <a href="{{ route('tournaments.create') }}" class="w-full flex items-center justify-center gap-2 bg-[#2b4e72] hover:bg-[#2790b0] active:bg-[#1e3852] text-white text-xs font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-all duration-150 transform active:scale-[0.99] group">
                    <svg class="size-4 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo Torneo</span>
                </a>
            @elseif($isDT && $myTeam)
                <a href="{{ route('dt.roster', $myTeam) }}" class="w-full flex items-center justify-center gap-2 bg-[#2b4e72] hover:bg-[#2790b0] active:bg-[#1e3852] text-white text-xs font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-all duration-150 transform active:scale-[0.99] group">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Gestionar Mi Plantilla</span>
                </a>
            @elseif($isReferee)
                <a href="{{ route('referees.portal') }}" class="w-full flex items-center justify-center gap-2 bg-[#2b4e72] hover:bg-[#2790b0] active:bg-[#1e3852] text-white text-xs font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-all duration-150">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Consola & Escáner QR</span>
                </a>
            @endif
        </div>

        <!-- Sidebar Navigation Menu Items -->
        <div class="flex-1 overflow-y-auto sidebar-scroll px-3.5 py-2 space-y-1">
            
            <!-- Link to Public Portal -->
            <a href="{{ url('/') }}" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-all duration-150 text-emerald-400 hover:text-white bg-emerald-950/40 border border-emerald-500/30 hover:bg-emerald-900/50 mb-2">
                <svg class="size-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                <span>Ver Portal Público</span>
                <span class="ml-auto text-[10px] bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded">Web</span>
            </a>

            @if($isSuperAdmin || $isAdmin)
                <!-- ================= VISTA ADMIN / SUPER ADMIN ================= -->
                <!-- Dashboard -->
                <a href="{{ $isSuperAdmin ? route('super-admin.dashboard') : route('admin.dashboard') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('super-admin.dashboard') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>{{ $isSuperAdmin ? 'Panel Super Admin' : 'Panel de mi Torneo' }}</span>
                </a>

                <!-- Tournaments -->
                <a href="{{ route('tournaments.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('tournaments.*') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>{{ $isSuperAdmin ? 'Torneos (Todos)' : 'Mis Torneos' }}</span>
                </a>

                <!-- Matches -->
                <div class="space-y-0.5">
                    <a href="{{ route('admin.matches.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.matches.*') || request()->routeIs('matches.create') || request()->routeIs('matches.edit') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                        <div class="flex items-center gap-3.5">
                            <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Gestión de Partidos</span>
                        </div>
                    </a>

                    <!-- Sub-item: Schedule Matches -->
                    <div class="pl-4 pt-0.5 space-y-0.5">
                        <a href="{{ route('matches.schedule') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors duration-150 {{ request()->routeIs('matches.schedule') ? 'bg-[#2b4e72] text-white font-bold shadow-xs' : 'text-slate-400 hover:text-slate-200 hover:bg-[#4e4d4a]/50' }}">
                            <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Programar Partidos</span>
                        </a>
                    </div>
                </div>

                <!-- Teams -->
                <a href="{{ route('admin.teams.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.teams.*') || request()->routeIs('teams.create') || request()->routeIs('teams.edit') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Equipos</span>
                </a>

                <!-- Players -->
                <a href="{{ route('players.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('players.*') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Jugadores</span>
                </a>

                <!-- Referees -->
                <div class="space-y-0.5">
                    <a href="{{ route('referees.index') }}" 
                       class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('referees.index') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                        <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span>Árbitros</span>
                    </a>
                    <div class="pl-4 pt-0.5 space-y-0.5">
                        <a href="{{ route('referees.evaluations.index') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors duration-150 {{ request()->routeIs('referees.evaluations.*') ? 'bg-[#2b4e72] text-white font-bold shadow-xs' : 'text-slate-400 hover:text-slate-200 hover:bg-[#4e4d4a]/50' }}">
                            <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <span>Evaluaciones Arbitrales</span>
                        </a>
                    </div>
                </div>

                <!-- Venues -->
                <a href="{{ route('venues.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('venues.*') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Sedes Deportivas</span>
                </a>

                <!-- Standings -->
                <a href="{{ route('admin.standings.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.standings.*') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                    <span>Tabla de Posiciones</span>
                </a>

                <!-- Scouting & Recruitment -->
                <div class="space-y-0.5">
                    <a href="{{ route('admin.scouting.index') }}" 
                       class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.scouting.*') || request()->routeIs('scouting.index') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                        <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Radar de Scouting</span>
                    </a>
                    <div class="pl-4 pt-0.5 space-y-0.5">
                        <a href="{{ route('scouting.requests.index') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors duration-150 {{ request()->routeIs('scouting.requests.*') ? 'bg-[#2b4e72] text-white font-bold shadow-xs' : 'text-slate-400 hover:text-slate-200 hover:bg-[#4e4d4a]/50' }}">
                            <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Solicitudes de Fichaje</span>
                        </a>
                    </div>
                </div>

                <!-- Auditing -->
                <a href="{{ route('auditing.index') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('auditing.*') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <div class="flex items-center gap-3.5">
                        <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Auditoría</span>
                    </div>
                </a>

            @elseif($isDT)
                <!-- ================= VISTA DIRECTOR TÉCNICO (DT) ================= -->
                @if($myTeam)
                    <!-- Panel DT -->
                    <a href="{{ route('dt.dashboard', $myTeam) }}" 
                       class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('dt.dashboard') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                        <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Panel de {{ $myTeam->name }}</span>
                    </a>

                    <!-- Mi Plantilla y Perfiles Médicos/Deportivos -->
                    <a href="{{ route('dt.roster', $myTeam) }}" 
                       class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('dt.roster') || request()->routeIs('players.medical.*') || request()->routeIs('players.profile.*') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                        <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Mi Plantilla de Jugadores</span>
                    </a>
                @endif

                <!-- Calendario de Partidos -->
                <a href="{{ route('matches.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('matches.index') || request()->routeIs('matches.show') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Calendario de Partidos</span>
                </a>

                <!-- Tabla de Posiciones -->
                <a href="{{ route('standings.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('standings.index') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                    <span>Tabla de Posiciones</span>
                </a>

                <!-- Radar de Scouting & Mercado Libre -->
                <a href="{{ route('scouting.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('scouting.index') || request()->routeIs('scouting.show') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Radar de Talentos Libres</span>
                </a>

                <!-- Mis Solicitudes de Fichaje -->
                <a href="{{ route('scouting.requests.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('scouting.requests.*') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Mis Solicitudes de Fichaje</span>
                </a>

            @elseif($isReferee)
                <!-- ================= VISTA ÁRBITRO OFICIAL ================= -->
                <!-- Panel del Árbitro / Mis Asignaciones -->
                <a href="{{ route('referees.portal') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('referees.portal') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Mis Asignaciones</span>
                </a>

                <!-- Calendario de Partidos -->
                <a href="{{ route('matches.index') }}" 
                   class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('matches.*') ? 'bg-[#2b4e72] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70' }}">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Calendario General</span>
                </a>
            @else
                <!-- Vista por defecto para usuarios estándar / visitantes -->
                <a href="{{ route('standings.index') }}" class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                    <span>Tabla de Posiciones</span>
                </a>
                <a href="{{ route('matches.index') }}" class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-[#4e4d4a]/70">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Calendario de Partidos</span>
                </a>
            @endif
        </div>

        <!-- Sidebar Footer / Bottom Actions -->
        <div class="p-3.5 border-t border-[#4e4d4a]/80 space-y-1">
            <button type="button" onclick="document.getElementById('support-modal').showModal()" class="w-full flex items-center gap-3.5 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-[#4e4d4a]/60 transition-colors">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Soporte Técnico</span>
            </button>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3.5 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-rose-400 hover:bg-[#4e4d4a]/60 transition-colors cursor-pointer">
                        <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="w-full flex items-center gap-3.5 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-emerald-400 hover:bg-[#4e4d4a]/60 transition-colors">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Iniciar Sesión</span>
                </a>
            @endauth
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        
        <!-- Top App Bar -->
        <header class="bg-white border-b border-slate-200/80 px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
            
            <!-- Left: Dynamic View Title -->
            <div class="flex items-center gap-3">
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                    @yield('header_title', View::yieldContent('title', 'Panel Principal'))
                </h1>
                @yield('header_badge')
            </div>

            <!-- Right: Search, Notifications & User Dropdown -->
            <div class="flex items-center gap-4">
                
                @yield('header_search')

                <!-- Button to Public Portal -->
                <a href="{{ url('/') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition shadow-2xs">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    <span>Portal Público</span>
                </a>

                <!-- Notification Bell (Role-aware) -->
                @if($isSuperAdmin || $isAdmin)
                    <a href="{{ route('auditing.index') }}" class="relative p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-full transition-colors cursor-pointer" title="Auditorías y Alertas">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-1 right-1 size-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    </a>
                @elseif($isDT)
                    <a href="{{ route('scouting.requests.index') }}" class="relative p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-full transition-colors cursor-pointer" title="Solicitudes de Fichaje">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </a>
                @endif

                <!-- Theme Switcher (Dark / Light / Battery Saver) -->
                <x-theme-toggle id="topbar-theme-switch" class="mr-1" />

                <!-- User Profile Dropdown Pill -->
                <div class="flex items-center gap-2.5 pl-2 border-l border-slate-200">
                    <div class="size-8 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center ring-2 ring-slate-100 shadow-2xs">
                        @auth
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @else
                            A
                        @endauth
                    </div>
                    <div class="hidden sm:flex flex-col text-left">
                        <span class="text-xs font-bold text-slate-800 leading-tight">
                            @auth
                                {{ auth()->user()->name }}
                            @else
                                Usuario Invitado
                            @endauth
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">
                            {{ $roleLabel }}
                        </span>
                    </div>

                    @auth
                        <form action="{{ route('logout') }}" method="POST" class="inline ml-1">
                            @csrf
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Cerrar Sesión">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('status') || session('success'))
            <div class="mx-6 mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <svg class="size-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('status') ?? session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-6 mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <svg class="size-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900">&times;</button>
            </div>
        @endif

        <!-- Main View Content -->
        <main class="flex-1 p-6 md:p-8 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>

        <!-- Subtle Footer -->
        <footer class="mt-auto border-t border-slate-200/80 bg-white py-4 px-6 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                <strong class="font-bold text-slate-700">SIGET</strong> · Sistema Integral de Gestión Deportiva de Torneos
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Versión 2.4 Formal</span>
                <span>•</span>
                <a href="{{ route('standings.index') }}" class="hover:text-emerald-700">Tabla General</a>
                <span>•</span>
                <a href="{{ route('matches.schedule') }}" class="hover:text-emerald-700">Calendario</a>
            </div>
        </footer>
    </div>

    <!-- Support Modal -->
    <x-modal id="support-modal" title="Centro de Soporte y Ayuda SIGET">
        <div class="space-y-4 text-sm text-slate-600">
            <p>
                Bienvenido al soporte técnico para directores y comités de torneos. Si requieres asistencia con la programación de partidos, actas arbitrales o configuración de llaves, contáctanos:
            </p>
            <div class="grid gap-3 pt-2">
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center gap-3">
                    <x-reicon name="envelope" class="size-5 text-emerald-600 shrink-0" />
                    <div>
                        <div class="font-bold text-slate-800">Correo Electrónico</div>
                        <div class="text-xs text-slate-500">soporte@siget.com</div>
                    </div>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center gap-3">
                    <x-reicon name="mobile" class="size-5 text-emerald-600 shrink-0" />
                    <div>
                        <div class="font-bold text-slate-800">Línea Directa / WhatsApp</div>
                        <div class="text-xs text-slate-500">+57 (300) 890-4521</div>
                    </div>
                </div>
            </div>
            <div class="pt-4 flex justify-end">
                <x-button variant="secondary" onclick="document.getElementById('support-modal').close()">Cerrar</x-button>
            </div>
        </div>
    </x-modal>

</body>
</html>
