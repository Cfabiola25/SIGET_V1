<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGET') | SIGET</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="siget-shell min-h-screen">
    <header class="sticky top-0 z-30 border-b border-slate-800 bg-slate-950/90 backdrop-blur-md">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8" aria-label="Navegación principal">
            <a href="{{ url('/') }}" class="flex items-center gap-3 font-semibold tracking-tight">
                <span class="grid size-9 place-items-center rounded-lg bg-emerald-500 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/10">S</span>
                <span class="text-lg text-white">SIGET<span class="text-emerald-400">.</span></span>
            </a>
            <div class="hidden items-center gap-7 text-sm font-medium md:flex">
                <a class="{{ request()->is('/') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200' }} border-b-2 px-1 py-2 transition" href="{{ url('/') }}">Inicio</a>
                <a class="{{ request()->routeIs('tournaments.*') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200' }} border-b-2 px-1 py-2 transition" href="{{ route('tournaments.index') }}">Torneos</a>
                <a class="{{ request()->routeIs('teams.*') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200' }} border-b-2 px-1 py-2 transition" href="{{ route('teams.index') }}">Equipos</a>
                <a class="{{ request()->routeIs('matches.*') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200' }} border-b-2 px-1 py-2 transition" href="{{ route('matches.index') }}">Partidos</a>
                <a class="{{ request()->routeIs('scouting.*') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200' }} border-b-2 px-1 py-2 transition" href="{{ route('scouting.index') }}">Scouting</a>
                <a class="{{ request()->routeIs('venues.*') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200' }} border-b-2 px-1 py-2 transition" href="{{ route('venues.index') }}">Sedes</a>
                @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isAdmin())
                    <a class="{{ request()->routeIs('referees.*') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200' }} border-b-2 px-1 py-2 transition" href="{{ route('referees.evaluations.index') }}">Arbitraje</a>
                @endif
                @auth <a class="{{ request()->routeIs('dashboard') || request()->routeIs('*.dashboard') ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-200' }} border-b-2 px-1 py-2 transition" href="{{ route('dashboard') }}">Panel</a> @endauth
            </div>
            <div class="flex items-center gap-3">
                @auth
                    <span class="hidden text-xs font-semibold uppercase tracking-wider text-slate-400 sm:inline">{{ auth()->user()->role }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf <button class="rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-slate-700" type="submit">Salir</button></form>
                @else
                    <a class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-bold text-slate-950 shadow-sm transition hover:bg-emerald-400 hover:shadow-emerald-500/25" href="{{ route('login') }}">Entrar</a>
                @endauth
            </div>
        </nav>
    </header>
    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-8 lg:py-12">
        @yield('content')
    </main>
</body>
</html>
