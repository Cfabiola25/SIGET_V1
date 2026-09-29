<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGET-SF') | Plataforma de Gestión Deportiva</title>
    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --font-display: 'Outfit', -apple-system, sans-serif;
            --font-sans: 'Plus Jakarta Sans', -apple-system, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }
        body {
            font-family: var(--font-sans);
        }
        .font-display {
            font-family: var(--font-display);
        }
        .font-mono {
            font-family: var(--font-mono);
        }
        /* Glassmorphism Classes */
        .glass-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.01) 100%), rgba(13, 19, 33, 0.72);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }
        .glass-card-hover {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-card-hover:hover {
            transform: translateY(-3px);
            border-color: rgba(16, 185, 129, 0.35);
            box-shadow: 0 20px 40px -15px rgba(16, 185, 129, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        .glass-pill {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-emerald {
            box-shadow: 0 0 30px -5px rgba(16, 185, 129, 0.4);
        }
        .glow-cyan {
            box-shadow: 0 0 30px -5px rgba(6, 182, 212, 0.35);
        }
        .glow-amber {
            box-shadow: 0 0 30px -5px rgba(245, 158, 11, 0.35);
        }
        /* Ambient Background Mesh */
        .ambient-mesh {
            background-color: #070a13;
            background-image: 
                radial-gradient(at 50% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 55%),
                radial-gradient(at 100% 20%, rgba(6, 182, 212, 0.08) 0px, transparent 40%),
                radial-gradient(at 0% 50%, rgba(16, 185, 129, 0.06) 0px, transparent 50%),
                radial-gradient(at 80% 80%, rgba(99, 102, 241, 0.05) 0px, transparent 45%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="ambient-mesh min-h-screen flex flex-col justify-between text-slate-100 antialiased selection:bg-emerald-500 selection:text-slate-950">
    <!-- Navbar Flotante Glassmorphic -->
    <header class="sticky top-0 z-40 border-b border-white/[0.07] bg-slate-950/80 backdrop-blur-2xl transition-all duration-300">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8" aria-label="Navegación principal">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="relative flex size-9 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-600 via-emerald-500 to-teal-400 text-slate-950 font-black shadow-lg shadow-emerald-500/25 group-hover:shadow-emerald-500/40 group-hover:scale-105 transition transform">
                    <span class="text-base tracking-tighter">S</span>
                    <span class="absolute -top-0.5 -right-0.5 size-2 rounded-full bg-teal-300 ring-2 ring-slate-950 animate-pulse"></span>
                </div>
                <div class="flex flex-col">
                    <span class="font-display text-xl font-extrabold tracking-wider text-white leading-none">
                        SIGET<span class="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">-SF</span>
                    </span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-emerald-400/80 leading-none mt-0.5">Torneo Oficial</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <div class="hidden items-center gap-1.5 md:flex rounded-full glass-pill p-1">
                <a class="{{ request()->is('/') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30' : 'text-slate-300 hover:text-white hover:bg-white/[0.04]' }} rounded-full px-4 py-1.5 text-xs font-semibold tracking-wide transition flex items-center gap-1.5" href="{{ url('/') }}">
                    <span>Home</span>
                </a>
                <a class="{{ request()->routeIs('standings.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30' : 'text-slate-300 hover:text-white hover:bg-white/[0.04]' }} rounded-full px-4 py-1.5 text-xs font-semibold tracking-wide transition" href="{{ route('standings.index') }}">
                    Standings
                </a>
                <a class="{{ request()->routeIs('matches.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30' : 'text-slate-300 hover:text-white hover:bg-white/[0.04]' }} rounded-full px-4 py-1.5 text-xs font-semibold tracking-wide transition" href="{{ route('matches.index') }}">
                    Calendar
                </a>
                <a class="{{ request()->routeIs('teams.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30' : 'text-slate-300 hover:text-white hover:bg-white/[0.04]' }} rounded-full px-4 py-1.5 text-xs font-semibold tracking-wide transition" href="{{ route('teams.index') }}">
                    Teams
                </a>
                <a class="{{ request()->routeIs('scouting.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30' : 'text-slate-300 hover:text-white hover:bg-white/[0.04]' }} rounded-full px-4 py-1.5 text-xs font-semibold tracking-wide transition" href="{{ route('scouting.index') }}">
                    Scouting
                </a>
                @auth
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                        <a class="{{ request()->routeIs('tournaments.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.04]' }} rounded-full px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider transition" href="{{ route('tournaments.index') }}">
                            Gestión
                        </a>
                    @endif
                    <a class="{{ request()->routeIs('dashboard') || request()->routeIs('*.dashboard') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30' : 'text-slate-300 hover:text-white hover:bg-white/[0.04]' }} rounded-full px-4 py-1.5 text-xs font-semibold tracking-wide transition" href="{{ route('dashboard') }}">
                        Panel
                    </a>
                @endauth
            </div>

            <!-- Action Buttons: Search & User Session -->
            <div class="flex items-center gap-3">
                <a href="{{ route('matches.index') }}" class="flex size-9 items-center justify-center rounded-xl glass-pill text-slate-400 hover:text-emerald-400 hover:border-emerald-500/30 transition group" title="Buscar partidos y estadísticas">
                    <svg class="size-4 group-hover:scale-110 transition transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </a>
                @auth
                    <span class="hidden rounded-full bg-emerald-500/10 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-emerald-400 border border-emerald-500/25 sm:inline">
                        {{ auth()->user()->role }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf 
                        <button class="rounded-full border border-slate-700 bg-slate-800/80 px-4 py-1.5 text-xs font-bold text-slate-300 transition hover:bg-slate-700 hover:text-white" type="submit">
                            Salir
                        </button>
                    </form>
                @else
                    <a class="relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 px-5 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-950 shadow-md shadow-emerald-500/25 hover:shadow-emerald-500/40 transition transform hover:-translate-y-0.5 active:translate-y-0" href="{{ route('login') }}">
                        <span>Login</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Main Container -->
    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8 flex-1">
        @yield('content')
    </main>

    <!-- Footer Moderno -->
    <footer class="mt-20 border-t border-white/[0.06] bg-slate-950/80 backdrop-blur-xl py-10 text-sm text-slate-400">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 text-center sm:flex-row sm:text-left lg:px-8">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-500 font-black text-xs text-slate-950">S</div>
                <span class="font-display font-extrabold text-white tracking-wider text-base">SIGET<span class="text-emerald-400 font-black">-SF</span></span>
                <span class="text-xs text-slate-600">|</span>
                <span class="text-xs text-slate-500">Torneos Oficiales de Fútbol</span>
            </div>
            <p class="text-xs text-slate-500">© 2026 SIGET-SF Tournament Management. Todos los derechos reservados.</p>
            <div class="flex items-center gap-6 text-xs font-semibold text-slate-400">
                <a href="#" class="hover:text-emerald-400 transition">Reglamento IFAB</a>
                <a href="#" class="hover:text-emerald-400 transition">Tribunal Arbitral</a>
                <a href="#" class="hover:text-emerald-400 transition">Soporte Técnico</a>
            </div>
        </div>
    </footer>
</body>
</html>
