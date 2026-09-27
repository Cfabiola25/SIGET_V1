<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#090D16">
    <title>Iniciar sesión | SIGET</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="siget-shell min-h-screen text-siget-ink">
    <main class="mx-auto grid min-h-screen w-full max-w-[1600px] lg:grid-cols-[minmax(0,1fr)_560px]">
        <section class="siget-grid relative hidden flex-col justify-between overflow-hidden border-r border-siget-ink/10 px-10 py-10 lg:flex lg:px-14 lg:py-12">
            <a href="{{ url('/') }}" class="relative z-10 flex w-fit items-center gap-3 font-semibold" aria-label="SIGET, inicio">
                <span class="grid size-10 place-items-center rounded-lg bg-emerald-500 text-sm font-black text-slate-950">S</span>
                <span class="text-xl font-bold tracking-tight text-white">SIGET<span class="text-emerald-400">.</span></span>
            </a>

            <div class="relative z-10 max-w-2xl py-14">
                <p class="mb-5 text-xs font-bold uppercase tracking-[0.18em] text-siget-coral">Gestión deportiva</p>
                <h1 class="siget-display max-w-xl text-6xl leading-[1.05] text-siget-ink">Cada torneo, en juego.</h1>

                <div class="mt-12 max-w-xl border-t border-siget-ink/15">
                    <div class="flex items-center justify-between border-b border-siget-ink/15 py-4">
                        <span class="text-sm font-medium">Super administrador</span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-siget-muted">Plataforma</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-siget-ink/15 py-4">
                        <span class="text-sm font-medium">Administrador de torneo</span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-siget-muted">Organización</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-siget-ink/15 py-4">
                        <span class="text-sm font-medium">Capitán y jugador</span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-siget-muted">Equipo</span>
                    </div>
                </div>
            </div>

            <p class="relative z-10 text-xs font-medium uppercase tracking-wider text-siget-muted">SIGET · 2026</p>
            <span aria-hidden="true" class="pointer-events-none absolute -bottom-28 -right-20 size-96 rounded-full border-[36px] border-siget-primary/10"></span>
        </section>

        <section class="flex min-h-screen flex-col justify-center px-6 py-10 sm:px-12 lg:px-14">
            <a href="{{ url('/') }}" class="mb-12 flex w-fit items-center gap-3 font-semibold lg:hidden" aria-label="SIGET, inicio">
                <span class="grid size-10 place-items-center rounded-lg bg-emerald-500 text-sm font-black text-slate-950">S</span>
                <span class="text-xl font-bold tracking-tight text-white">SIGET<span class="text-emerald-400">.</span></span>
            </a>

            <div class="siget-rise mx-auto w-full max-w-md">
                <p class="text-sm font-semibold text-siget-coral">Bienvenido de nuevo</p>
                <h2 class="siget-display mt-3 text-4xl leading-tight">Iniciar sesión</h2>
                <p class="mt-3 text-sm leading-6 text-siget-muted">Ingresa con el correo y la contraseña de tu cuenta SIGET.</p>

                @if ($errors->any())
                    <div class="mt-7 rounded-lg border border-siget-coral/30 bg-siget-coral/10 px-4 py-3 text-sm text-siget-ink" role="alert">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form class="mt-8 space-y-5" method="POST" action="{{ route('login.authenticate') }}">
                    @csrf
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                            @error('email') aria-invalid="true" @enderror
                            class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            placeholder="nombre@correo.com">
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold">Contraseña</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="w-full rounded-lg border border-slate-800 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            placeholder="Tu contraseña">
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-1">
                        <label for="remember" class="flex cursor-pointer items-center gap-2.5 text-sm text-siget-muted">
                            <input id="remember" name="remember" type="checkbox" value="1" class="size-4 rounded border-siget-ink/30 accent-siget-primary">
                            Recordarme
                        </label>
                        <span class="text-right text-xs text-siget-muted">Acceso según los permisos de tu cuenta</span>
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-emerald-500 px-5 py-3.5 text-sm font-bold text-slate-950 transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                        Entrar a SIGET
                    </button>
                </form>

                <p class="mt-8 border-t border-siget-ink/10 pt-6 text-center text-sm text-siget-muted">
                    ¿Aún no tienes cuenta?
                    <a href="{{ route('register') }}" class="ml-1 font-semibold text-siget-ink underline decoration-siget-primary decoration-2 underline-offset-4 hover:text-siget-coral">Crear cuenta</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
