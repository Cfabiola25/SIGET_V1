<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
</head>
<body>
    <h1>Crear cuenta</h1>
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <label>Nombre <input type="text" name="name" value="{{ old('name') }}" required></label>
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label>
        <label>Rol
            <select name="role" required>
                <option value="organizer">Organizador</option>
                <option value="captain">Capitán</option>
                <option value="player">Jugador</option>
            </select>
        </label>
        <label>Contraseña <input type="password" name="password" required></label>
        <label>Confirmar contraseña <input type="password" name="password_confirmation" required></label>
        <button type="submit">Registrarme</button>
    </form>
    <a href="{{ route('login') }}">Ya tengo cuenta</a>
</body>
</html>