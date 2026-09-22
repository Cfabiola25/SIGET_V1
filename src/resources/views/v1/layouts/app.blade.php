<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGET')</title>
</head>
<body>
    <header>
        <nav>
            <a href="/">Inicio</a>
            <a href="/tournaments">Torneos</a>
            <a href="/matches">Partidos</a>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
