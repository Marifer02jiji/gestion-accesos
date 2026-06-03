{{--
Empresa:     OMEGA Solutions
Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
Archivo:     resources/views/auth/reset-password-simple.blade.php
Creación:    02/06/2026
Creado por:  Jacqueline Marifer Escobar Espinoza
Aprobado por: Líder de Área

Changelog:
ID: 1 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, formulario de reset automático de contraseña al nombre de usuario
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer Contraseña — Gestión de Accesos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg, #F29066 0%, #3B5675 100%);">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="font-heading font-bold text-white text-3xl">Gestión de Accesos</h1>
            <p class="text-white text-sm mt-1 opacity-80">Restablecer Contraseña</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">

            <h2 class="font-heading font-bold text-omg-nile text-xl mb-2 text-center">
                <i class="fas fa-key mr-2" style="color: #DA7E2D;"></i>
                ¿Olvidaste tu contraseña?
            </h2>
            <p class="text-xs text-gray-400 text-center mb-6">
                Ingresa tu nombre de usuario y tu contraseña se restablecerá a tu mismo usuario.
            </p>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                    @foreach($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.reset.simple.post') }}">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-user mr-1"></i> Nombre de Usuario
                    </label>
                    <input type="text" name="usuario" value="{{ old('usuario') }}"
                        placeholder="Ej. mauro"
                        autofocus
                        class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                </div>

                <button type="submit"
                    class="w-full text-white py-2 rounded-lg font-heading font-semibold hover:opacity-90 transition flex items-center justify-center gap-2"
                    style="background-color: #DA7E2D;">
                    <i class="fas fa-redo"></i> Restablecer Contraseña
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}"
                    class="text-xs hover:opacity-80 flex items-center justify-center gap-1"
                    style="color: #3B5675;">
                    <i class="fas fa-arrow-left"></i> Regresar al Login
                </a>
            </div>
        </div>

        <p class="text-center text-white text-xs mt-6">
            © {{ date('Y') }} OMEGA Solutions — Todos los derechos reservados
        </p>
    </div>
</body>
</html>