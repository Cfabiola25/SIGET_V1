<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGET') | SIGET</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="siget-shell min-h-screen flex flex-col justify-between bg-slate-950 text-slate-100">
    <header class="sticky top-0 z-40 border-b border-slate-800/80 bg-slate-950/95 backdrop-blur-md">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-3.5 lg:px-8" aria-label="Navegación principal">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 font-bold tracking-tight group">
                <span class="grid size-8 place-items-center rounded-lg bg-emerald-500 text-sm font-black text-slate-950 shadow-md shadow-emerald-500/20 group-hover:scale-105 transition transform">S</span>
                <span class="text-xl font-extrabold text-white tracking-wider">SIGET<span class="text-emerald-400 font-black">-SF</span></span>
            </a>

            <!-- Navigation Links matching mock -->
            <div class="hidden items-center gap-7 text-sm font-medium md:flex">
                <a class="{{ request()->is('/') ? 'border-emerald-500 text-emerald-400 font-bold' : 'border-transparent text-slate-300 hover:text-white' }} border-b-2 px-1 py-1.5 transition" href="{{ url('/') }}">Home</a>
                <a class="{{ request()->routeIs('standings.*') ? 'border-emerald-500 text-emerald-400 font-bold' : 'border-transparent text-slate-300 hover:text-white' }} border-b-2 px-1 py-1.5 transition" href="{{ route('standings.index') }}">Standings</a>
                <a class="{{ request()->routeIs('matches.*') ? 'border-emerald-500 text-emerald-400 font-bold' : 'border-transparent text-slate-300 hover:text-white' }} border-b-2 px-1 py-1.5 transition" href="{{ route('matches.index') }}">Calendar</a>
                <a class="{{ request()->routeIs('teams.*') ? 'border-emerald-500 text-emerald-400 font-bold' : 'border-transparent text-slate-300 hover:text-white' }} border-b-2 px-1 py-1.5 transition" href="{{ route('teams.index') }}">Teams</a>
                <a class="{{ request()->routeIs('scouting.*') ? 'border-emerald-500 text-emerald-400 font-bold' : 'border-transparent text-slate-300 hover:text-white' }} border-b-2 px-1 py-1.5 transition" href="{{ route('scouting.index') }}">Scouting</a>
                @auth
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                        <a class="{{ request()->routeIs('tournaments.*') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200' }} border-b-2 px-1 py-1.5 transition text-xs uppercase" href="{{ route('tournaments.index') }}">Gestión</a>
                    @endif
                    <a class="{{ request()->routeIs('dashboard') || request()->routeIs('*.dashboard') ? 'border-emerald-500 text-emerald-400 font-bold' : 'border-transparent text-slate-300 hover:text-white' }} border-b-2 px-1 py-1.5 transition" href="{{ route('dashboard') }}">Panel</a>
                @endauth
            </div>

            <!-- Action Buttons: Search & Login -->
            <div class="flex items-center gap-3">
                <a href="{{ route('matches.index') }}" class="p-2 text-slate-400 hover:text-emerald-400 transition" title="Buscar partidos">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </a>
                @auth
                    <span class="hidden rounded-full bg-slate-800 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-400 border border-slate-700 sm:inline">{{ auth()->user()->role }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf <button class="rounded-full border border-slate-700 bg-slate-800/80 px-4 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white" type="submit">Salir</button></form>
                @else
                    <a class="rounded-full bg-emerald-600 hover:bg-emerald-500 px-5 py-2 text-sm font-bold text-white shadow-md shadow-emerald-600/30 transition transform hover:-translate-y-0.5" href="{{ route('login') }}">Login</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex-1">
        @yield('content')
    </main>

    <!-- Footer matching mock design -->
    <footer class="mt-16 border-t border-slate-800/80 bg-slate-900/40 py-8 text-sm text-slate-400">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-5 text-center sm:flex-row sm:text-left lg:px-8">
            <div class="flex items-center gap-2">
                <span class="font-extrabold text-white tracking-wider">SIGET<span class="text-emerald-400">-SF</span></span>
            </div>
            <p class="text-xs text-slate-500">© 2026 SIGET-SF Tournament Management. All rights reserved.</p>
            <div class="flex items-center gap-6 text-xs font-medium text-slate-400">
                <a href="#" class="hover:text-emerald-400 transition">Privacy Policy</a>
                <a href="#" class="hover:text-emerald-400 transition">Terms of Service</a>
                <a href="#" class="hover:text-emerald-400 transition">Contact Us</a>
            </div>
        </div>
    </footer>
</body>
</html>
