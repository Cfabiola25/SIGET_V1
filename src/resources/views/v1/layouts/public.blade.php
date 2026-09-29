<!DOCTYPE html>
<html lang="es" class="h-full bg-[#f4f6fa] scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGET-SF') | Portal Oficial de Torneos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
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
    </style>
</head>
<body class="min-h-full bg-[#f4f6fa] text-slate-800 antialiased flex flex-col selection:bg-emerald-500 selection:text-white">

    <!-- BARRA SUPERIOR PÚBLICA (CRISP WHITE CON IDENTIDAD SIGET-SF) -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Logo & Marca Principal -->
                <div class="flex items-center gap-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                        <span class="size-9 rounded-xl bg-[#057a55] flex items-center justify-center text-white font-black text-base shadow-sm group-hover:bg-[#046c4b] transition transform group-hover:scale-105">
                            S
                        </span>
                        <span class="font-display text-xl sm:text-2xl font-black tracking-tight text-[#057a55]">
                            SIGET<span class="text-slate-900">-SF</span>
                        </span>
                    </a>

                    <!-- Enlaces de Navegación del Portal Público -->
                    <nav class="hidden md:flex items-center gap-1 sm:gap-2">
                        <a href="{{ url('/') }}" 
                           class="px-3.5 py-2 text-sm font-bold transition rounded-lg relative {{ request()->is('/') ? 'text-[#057a55] after:absolute after:bottom-0 after:left-3.5 after:right-3.5 after:h-0.5 after:bg-[#057a55]' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            Home
                        </a>
                        <a href="{{ route('standings.index') }}" 
                           class="px-3.5 py-2 text-sm font-semibold transition rounded-lg {{ request()->routeIs('standings.*') ? 'text-[#057a55] font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            Standings
                        </a>
                        <a href="{{ route('matches.index') }}" 
                           class="px-3.5 py-2 text-sm font-semibold transition rounded-lg {{ request()->routeIs('matches.*') ? 'text-[#057a55] font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            Calendar
                        </a>
                        <a href="{{ url('/#seccion-estadisticas') }}" 
                           class="px-3.5 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 rounded-lg transition">
                            Stats
                        </a>
                        <a href="{{ route('teams.index') }}" 
                           class="px-3.5 py-2 text-sm font-semibold transition rounded-lg {{ request()->routeIs('teams.*') ? 'text-[#057a55] font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            Equipos
                        </a>
                        <a href="{{ route('scouting.index') }}" 
                           class="px-3.5 py-2 text-sm font-semibold transition rounded-lg {{ request()->routeIs('scouting.*') ? 'text-[#057a55] font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            Scouting
                        </a>
                    </nav>
                </div>

                <!-- Lado Derecho: Selector Rápido de Torneo & Autenticación -->
                <div class="flex items-center gap-3">
                    
                    <!-- Botón de Búsqueda Rápida de Torneos -->
                    <button type="button" 
                            onclick="document.getElementById('quickTournamentModal')?.showModal()" 
                            class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-full transition" 
                            title="Explorar Torneos y Buscar">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    <!-- Estado de Sesión del Usuario: Público vs Logueado -->
                    @auth
                        <!-- Usuario Ya Autenticado: Muestra perfil y acceso a panel interno -->
                        <div class="flex items-center gap-2 sm:gap-3 pl-2 border-l border-slate-200">
                            <a href="{{ auth()->user()->isReferee() ? route('referees.portal') : route('dashboard') }}" 
                               class="inline-flex items-center gap-2 bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs sm:text-sm font-bold py-2 px-3.5 sm:px-4 rounded-full shadow-xs transition transform hover:-translate-y-0.5" 
                               title="Ir a tu panel">
                                <span class="hidden sm:inline">{{ auth()->user()->isReferee() ? 'Panel Arbitral' : 'Mi Panel' }}</span>
                                <span class="sm:hidden">{{ auth()->user()->isReferee() ? 'Árbitro' : 'Panel' }}</span>
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-full transition" 
                                        title="Cerrar Sesión">
                                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Usuario Visitante: Botón Login llamativo y limpio como en la maqueta -->
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center justify-center bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] text-white text-xs sm:text-sm font-bold py-2 sm:py-2.5 px-5 sm:px-6 rounded-full shadow-xs transition-all duration-150 transform hover:-translate-y-0.5">
                            Login
                        </a>
                    @endauth

                    <!-- Botón Hamburguesa Móvil -->
                    <button type="button" 
                            onclick="document.getElementById('mobile-public-nav').classList.toggle('hidden')" 
                            class="md:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menú Móvil Desplegable -->
        <div id="mobile-public-nav" class="hidden md:hidden border-t border-slate-200/80 bg-white px-4 py-3 space-y-1 shadow-lg">
            <a href="{{ url('/') }}" class="block px-3 py-2 text-sm font-bold text-[#057a55] rounded-lg bg-emerald-50">Home</a>
            <a href="{{ route('standings.index') }}" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 rounded-lg">Standings</a>
            <a href="{{ route('matches.index') }}" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 rounded-lg">Calendar</a>
            <a href="{{ url('/#seccion-estadisticas') }}" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 rounded-lg">Stats</a>
            <a href="{{ route('teams.index') }}" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 rounded-lg">Equipos</a>
            <a href="{{ route('scouting.index') }}" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 rounded-lg">Scouting</a>
            @guest
                <div class="pt-2 border-t border-slate-100 flex gap-2">
                    <a href="{{ route('login') }}" class="flex-1 text-center bg-[#057a55] text-white font-bold py-2 rounded-lg text-sm">Iniciar Sesión</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center border border-slate-300 text-slate-700 font-bold py-2 rounded-lg text-sm">Registrarse</a>
                </div>
            @endguest
        </div>
    </header>

    <!-- MENSAJES FLASH -->
    @if (session('status') || session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <svg class="size-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('status') ?? session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <svg class="size-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-bold">&times;</button>
            </div>
        </div>
    @endif

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 w-full pb-16">
        @yield('content')
    </main>

    <!-- FOOTER DEL PORTAL PÚBLICO (IDÉNTICO A LA MAQUETA DEL USUARIO) -->
    <footer class="mt-auto bg-[#dbe7f6] text-slate-600 py-6 px-4 sm:px-6 lg:px-8 border-t border-slate-300/50">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
            <!-- Brand -->
            <div class="flex items-center gap-2 font-display font-black text-base text-[#057a55]">
                <span>SIGET-SF</span>
            </div>

            <!-- Copyright -->
            <div class="text-slate-600 text-center">
                &copy; {{ date('Y') }} SIGET-SF Tournament Management. All rights reserved.
            </div>

            <!-- Links -->
            <div class="flex items-center gap-5 text-slate-600 font-medium">
                <a href="#" class="hover:text-slate-900 hover:underline">Privacy Policy</a>
                <a href="#" class="hover:text-slate-900 hover:underline">Terms of Service</a>
                <a href="#" class="hover:text-slate-900 hover:underline">Contact Us</a>
            </div>
        </div>
    </footer>

    <!-- MODAL EXPLORADOR DE TORNEOS -->
    <dialog id="quickTournamentModal" class="rounded-2xl p-0 backdrop:bg-slate-950/70 border-0 shadow-2xl max-w-lg w-full">
        <div class="bg-white p-6 rounded-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 font-display font-bold text-lg text-slate-900">
                    <span class="text-emerald-600 text-xl">🏆</span>
                    <span>Explorar Torneos Disponibles</span>
                </div>
                <button type="button" onclick="document.getElementById('quickTournamentModal').close()" class="p-1 text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>
            <div class="mt-4 space-y-2.5 max-h-72 overflow-y-auto">
                @if(isset($allTournaments))
                    @foreach($allTournaments as $t)
                        <a href="{{ url('/?tournament_id=' . $t->id) }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/40 transition group">
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 group-hover:text-emerald-700">{{ $t->name }}</h4>
                                <p class="text-xs text-slate-500">{{ $t->sport_type }} · {{ $t->teams_count }} Equipos · {{ $t->matches_count }} Partidos</p>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full uppercase {{ $t->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $t->status }}
                            </span>
                        </a>
                    @endforeach
                @else
                    <p class="text-xs text-slate-500">Selecciona torneos desde el selector principal.</p>
                @endif
            </div>
            <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end">
                <button type="button" onclick="document.getElementById('quickTournamentModal').close()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg">Cerrar</button>
            </div>
        </div>
    </dialog>

</body>
</html>
