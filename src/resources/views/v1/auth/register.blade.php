<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | SIGET-SF Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f6fa] text-slate-900 font-sans antialiased">
    <main class="grid min-h-screen lg:grid-cols-[480px_1fr]">
        <!-- Left Brand Sidebar (Dark Navy #182232) -->
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
                        Nuevo Registro
                    </span>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white leading-tight">
                        Únete al Ecosistema Oficial de Torneos.
                    </h1>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Crea tu usuario para acceder a la gestión de nóminas de clubes, actas digitales arbitrales o la dirección integral de torneos deportivos.
                    </p>
                </div>
            </div>

            <div class="text-xs text-slate-400 pt-8 border-t border-slate-700/60 flex items-center justify-between">
                <span>SIGET-SF Enterprise &bull; 2026</span>
                <span class="font-mono text-emerald-400">v2.4 Pro</span>
            </div>
        </section>

        <!-- Right Form Panel -->
        <section class="flex flex-col justify-center items-center px-6 py-12 sm:px-12 bg-[#f4f6fa]">
            <div class="w-full max-w-md">
                <div class="bg-white rounded-2xl border border-slate-200/90 p-8 shadow-xs">
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Crear Nueva Cuenta</h2>
                    <p class="mt-1 text-xs text-slate-500">Ingresa tus datos para registrarte en el sistema.</p>

                    @if ($errors->any())
                        <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-800 font-medium">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form class="mt-6 space-y-4" method="POST" action="{{ route('register.store') }}">
                        @csrf
                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nombre Completo</label>
                            <input id="name" 
                                   name="name" 
                                   type="text" 
                                   value="{{ old('name') }}" 
                                   required 
                                   class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#057a55] focus:ring-1 focus:ring-[#057a55]"
                                   placeholder="Juan Pérez">
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Correo Electrónico</label>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#057a55] focus:ring-1 focus:ring-[#057a55]"
                                   placeholder="usuario@correo.com">
                        </div>

                        <div>
                            <label for="role" class="block text-xs font-semibold text-slate-700 mb-1.5">Rol en la Plataforma</label>
                            <select id="role" name="role" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 outline-none transition focus:border-[#057a55] focus:ring-1 focus:ring-[#057a55]">
                                <option value="admin">Administrador de Torneo / Director</option>
                                <option value="captain">Director Técnico (DT) / Capitán</option>
                                <option value="player">Jugador Registrado</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">Contraseña</label>
                                <input id="password" 
                                       name="password" 
                                       type="password" 
                                       required
                                       class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 outline-none transition focus:border-[#057a55] focus:ring-1 focus:ring-[#057a55]"
                                       placeholder="••••••••">
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">Confirmar</label>
                                <input id="password_confirmation" 
                                       name="password_confirmation" 
                                       type="password" 
                                       required
                                       class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-900 outline-none transition focus:border-[#057a55] focus:ring-1 focus:ring-[#057a55]"
                                       placeholder="••••••••">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full rounded-lg bg-[#057a55] hover:bg-[#046c4b] active:bg-[#03543a] py-2.5 text-xs font-semibold text-white shadow-2xs transition">
                                Completar Registro
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 border-t border-slate-100 pt-5 text-center text-xs text-slate-500">
                        ¿Ya tienes una cuenta?
                        <a href="{{ route('login') }}" class="font-semibold text-[#057a55] hover:underline ml-1">Iniciar Sesión</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>