<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | SIGET-SF Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f6fa] text-slate-900 font-sans antialiased">
    <main class="grid min-h-screen lg:grid-cols-[480px_1fr]">
        <!-- Left Brand Sidebar (Dark Navy #182232 matching sidebar) -->
        <section class="hidden lg:flex flex-col justify-between bg-[#182232] p-12 text-white border-r border-[#223147]">
            <div>
                <!-- Brand Logo & Badge -->
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#057a55] text-base font-extrabold text-white shadow-md">
                        S
                    </span>
                    <div>
                        <div class="text-lg font-black tracking-tight text-white flex items-center gap-1.5">
                            SIGET-SF
                            <span class="size-1.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-[11px] font-medium text-slate-400">Tournament Management Suite</p>
                    </div>
                </div>

                <div class="mt-20 space-y-6">
                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#057a55]/20 text-emerald-400 border border-[#057a55]/40">
                        Official Backoffice
                    </span>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white leading-tight">
                        Gestión Formal & Ejecutiva de Torneos.
                    </h1>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Control integral de fixtures, nóminas digitales, arbitraje profesional y certificación reglamentaria conforme a estándares FIFA e IFAB.
                    </p>

                    <div class="pt-6 space-y-3.5 border-t border-slate-700/60 text-xs text-slate-300">
                        <div class="flex items-center gap-2.5">
                            <span class="size-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</span>
                            <span>Consola arbitral en tiempo real & acta digital inmutable</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="size-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</span>
                            <span>Generador algorítmico Berger & eliminación directa</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="size-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</span>
                            <span>Auditoría de disputas y tribunal de disciplina</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-xs text-slate-400 pt-8 border-t border-slate-700/60 flex items-center justify-between">
                <span>SIGET-SF Enterprise &bull; 2026</span>
                <span class="font-mono text-emerald-400">v2.4 Pro</span>
            </div>
        </section>

        <!-- Right Form Panel (Clean White / Slate-50) -->
        <section class="flex flex-col justify-center items-center px-6 py-12 sm:px-12 bg-[#f4f6fa]">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="flex items-center gap-3 mb-8 lg:hidden">
                    <span class="grid size-9 place-items-center rounded-xl bg-[#057a55] text-sm font-extrabold text-white">S</span>
                    <div>
                        <span class="text-base font-black text-slate-900">SIGET-SF</span>
                        <p class="text-[10px] text-slate-500">Tournament Director</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/90 p-8 shadow-xs">
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Acceso al Panel</h2>
                    <p class="mt-1 text-xs text-slate-500">Ingresa tus credenciales oficiales para administrar la plataforma.</p>

                    @if ($errors->any())
                        <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-800 font-medium">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form class="mt-6 space-y-4" method="POST" action="{{ route('login.authenticate') }}">
                        @csrf
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Correo Electrónico</label>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   value="{{ old('email') }}" 
                                   autocomplete="email" 
                                   required 
                                   autofocus
                                   class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#057a55] focus:ring-1 focus:ring-[#057a55]"
                                   placeholder="director@siget.com">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs font-semibold text-slate-700">Contraseña</label>
                            </div>
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="current-password" 
                                   required
                                   class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#057a55] focus:ring-1 focus:ring-[#057a55]"
                                   placeholder="••••••••">
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label for="remember" class="flex cursor-pointer items-center gap-2 text-xs text-slate-600">
                                <input id="remember" name="remember" type="checkbox" value="1" class="size-4 rounded border-slate-300 text-[#057a55] focus:ring-[#057a55]">
                                <span>Recordar mi sesión</span>
                            </label>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] py-2.5 text-xs font-semibold text-white shadow-2xs transition">
                                Entrar a SIGET
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 border-t border-slate-100 pt-5 text-center text-xs text-slate-500">
                        ¿No tienes una cuenta aún?
                        <a href="{{ route('register') }}" class="font-semibold text-[#057a55] hover:underline ml-1">Crear Cuenta</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
