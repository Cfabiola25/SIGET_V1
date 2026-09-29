<!DOCTYPE html>
<html lang="es" class="h-full bg-[#f4f6fa]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGET-SF') | Gestión Deportiva y Torneos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
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
</head>
<body class="h-full bg-[#f4f6fa] text-slate-800 antialiased flex flex-col md:flex-row overflow-x-hidden">

    <!-- Mobile Header -->
    <header class="md:hidden flex items-center justify-between bg-[#182232] text-white px-4 py-3 border-b border-slate-700/50 z-50">
        <div class="flex items-center gap-3">
            <div class="size-8 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-white text-sm shadow">
                S
            </div>
            <div>
                <div class="text-sm font-bold leading-none">SIGET-SF</div>
                <div class="text-[10px] text-slate-400">Tournament Director</div>
            </div>
        </div>
        <button type="button" onclick="document.getElementById('siget-sidebar').classList.toggle('-translate-x-full')" class="p-2 text-slate-300 hover:text-white focus:outline-none">
            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </header>

    <!-- Sidebar Navigation (Dark Slate #182232) -->
    <aside id="siget-sidebar" class="fixed inset-y-0 left-0 z-40 w-64 md:w-68 bg-[#182232] text-slate-300 flex flex-col transition-transform duration-300 ease-in-out md:static md:translate-x-0 -translate-x-full shrink-0 border-r border-[#1e2d42] shadow-xl md:shadow-none">
        
        <!-- Sidebar Profile Header -->
        <div class="p-5 flex items-center gap-3.5 border-b border-[#223147]/60">
            <div class="relative">
                <div class="size-11 rounded-full bg-emerald-600 border-2 border-emerald-400/40 flex items-center justify-center text-white font-black text-base shadow-md">
                    @auth
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @else
                        S
                    @endauth
                </div>
                <span class="absolute bottom-0 right-0 size-3 rounded-full bg-emerald-500 ring-2 ring-[#182232]"></span>
            </div>
            <div class="flex flex-col min-w-0">
                <h1 class="text-[15px] font-bold text-white tracking-tight truncate leading-tight">
                    SIGET-SF Admin
                </h1>
                <p class="text-xs text-slate-400 font-medium truncate mt-0.5">
                    @auth
                        {{ auth()->user()->role === 'super_admin' ? 'Super Administrator' : (auth()->user()->role === 'admin' ? 'Tournament Director' : ucfirst(auth()->user()->role)) }}
                    @else
                        Tournament Director
                    @endauth
                </p>
            </div>
        </div>

        <!-- Call to Action Button: New Tournament -->
        <div class="px-5 pt-5 pb-3">
            <a href="{{ route('tournaments.create') }}" class="w-full flex items-center justify-center gap-2 bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-all duration-150 transform active:scale-[0.99] group">
                <svg class="size-4 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>New Tournament</span>
            </a>
        </div>

        <!-- Sidebar Navigation Menu Items -->
        <div class="flex-1 overflow-y-auto sidebar-scroll px-3.5 py-2 space-y-1">
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('super-admin.dashboard') ? 'bg-[#057a55] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#223147]/70' }}">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Dashboard</span>
            </a>

            <!-- Tournaments -->
            <div class="space-y-0.5">
                <a href="{{ route('tournaments.index') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('tournaments.index') || request()->routeIs('tournaments.show') || request()->routeIs('tournaments.create') || request()->routeIs('tournaments.edit') ? 'bg-[#057a55] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#223147]/70' }}">
                    <div class="flex items-center gap-3.5">
                        <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Tournaments</span>
                    </div>
                </a>

                <!-- Sub-item: Schedule Matches -->
                <div class="pl-4 pt-0.5 space-y-0.5">
                    <a href="{{ route('matches.schedule') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors duration-150 {{ request()->routeIs('matches.schedule') ? 'bg-[#057a55] text-white font-bold shadow-xs' : 'text-slate-400 hover:text-slate-200 hover:bg-[#223147]/50' }}">
                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Schedule Matches</span>
                    </a>
                </div>
            </div>

            <!-- Teams -->
            <a href="{{ route('teams.index') }}" 
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('teams.*') && !request()->routeIs('teams.invitations.*') ? 'bg-[#057a55] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#223147]/70' }}">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Teams</span>
            </a>

            <!-- Players -->
            <a href="{{ route('players.index') }}" 
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('players.*') ? 'bg-[#057a55] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#223147]/70' }}">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Players</span>
            </a>

            <!-- Referees -->
            <a href="{{ route('referees.index') }}" 
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('referees.index') ? 'bg-[#057a55] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#223147]/70' }}">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Referees</span>
            </a>

            <!-- Venues -->
            <a href="{{ route('venues.index') }}" 
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('venues.*') ? 'bg-[#057a55] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#223147]/70' }}">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Venues</span>
            </a>

            <!-- Auditing -->
            <a href="{{ route('auditing.index') }}" 
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('auditing.*') || request()->routeIs('referees.evaluations.*') ? 'bg-[#057a55] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#223147]/70' }}">
                <div class="flex items-center gap-3.5">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Auditing</span>
                </div>
                <span class="rounded-full bg-rose-600/90 text-white text-[10px] font-black px-2 py-0.5 leading-none">
                    3
                </span>
            </a>

            <!-- Standings -->
            <a href="{{ route('standings.index') }}" 
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('standings.*') ? 'bg-[#057a55] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#223147]/70' }}">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                <span>Standings</span>
            </a>

            <!-- Settings -->
            <a href="{{ route('tournaments.rules.edit', 1) }}" 
               class="flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('tournaments.rules.*') ? 'bg-[#057a55] text-white font-semibold shadow-sm' : 'text-slate-300 hover:text-white hover:bg-[#223147]/70' }}">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Settings</span>
            </a>
        </div>

        <!-- Sidebar Footer / Bottom Actions -->
        <div class="p-3.5 border-t border-[#223147]/80 space-y-1">
            <button type="button" onclick="document.getElementById('support-modal').showModal()" class="w-full flex items-center gap-3.5 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-[#223147]/60 transition-colors">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Support</span>
            </button>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3.5 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-rose-400 hover:bg-[#223147]/60 transition-colors cursor-pointer">
                        <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Logout</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="w-full flex items-center gap-3.5 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-emerald-400 hover:bg-[#223147]/60 transition-colors">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Login</span>
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
                    @yield('header_title', View::yieldContent('title', 'Overview'))
                </h1>
                @yield('header_badge')
            </div>

            <!-- Right: Search, Notifications & User Dropdown -->
            <div class="flex items-center gap-4">
                
                @yield('header_search')

                <!-- Notification Bell -->
                <a href="{{ route('auditing.index') }}" class="relative p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-full transition-colors cursor-pointer" title="Auditorías y Notificaciones">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="absolute top-1 right-1 size-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                </a>

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
                                Admin User
                            @endauth
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">
                            @auth
                                {{ ucfirst(auth()->user()->role) }}
                            @else
                                Tournament Director
                            @endauth
                        </span>
                    </div>
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
                <strong class="font-bold text-slate-700">SIGET-SF</strong> · Sistema Integral de Gestión Deportiva de Torneos
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
    <x-modal id="support-modal" title="Centro de Soporte y Ayuda SIGET-SF">
        <div class="space-y-4 text-sm text-slate-600">
            <p>
                Bienvenido al soporte técnico para directores y comités de torneos. Si requieres asistencia con la programación de partidos, actas arbitrales o configuración de llaves, contáctanos:
            </p>
            <div class="grid gap-3 pt-2">
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center gap-3">
                    <span class="text-lg">📧</span>
                    <div>
                        <div class="font-bold text-slate-800">Correo Electrónico</div>
                        <div class="text-xs text-slate-500">soporte@siget-sf.com</div>
                    </div>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center gap-3">
                    <span class="text-lg">📱</span>
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
