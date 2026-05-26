<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión — Gestión de Accesos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-omg-nile min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="font-heading font-bold text-white text-3xl">Gestión de Accesos</h1>
            <p class="text-omg-kashmir text-sm mt-1">Sistema de Control de Visitas</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">

            <h2 class="font-heading font-bold text-omg-nile text-xl mb-6 text-center">
                Iniciar Sesión
            </h2>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                    @foreach($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-user mr-1"></i> Usuario
                    </label>
                    <input type="text" name="usuario" value="{{ old('usuario') }}" autofocus
                        placeholder="Tu usuario del SAM"
                        class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-lock mr-1"></i> Contraseña
                    </label>
                    <input type="password" name="password"
                        placeholder="Tu contraseña"
                        class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                </div>

                <button type="submit"
                    class="w-full bg-omg-coral text-white py-2 rounded-lg font-heading font-semibold hover:opacity-90 transition flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>

                {{-- Acceso para vigilantes --}}
                <div class="mt-5 pt-4 border-t border-gray-100 text-center">
                    <p class="text-xs text-omg-kashmir mb-2">¿Eres vigilante?</p>
                    <a href="{{ route('vigilante.index') }}"
                       class="inline-flex items-center gap-2 bg-omg-chardon border border-omg-kashmir text-omg-nile px-4 py-2 rounded-lg text-sm font-semibold hover:bg-omg-nile hover:text-white transition">
                        <i class="fas fa-shield-alt text-omg-coral"></i> Acceso para Vigilantes
                    </a>
                </div>

            </form>
        </div>

        <p class="text-center text-omg-kashmir text-xs mt-6">
            © 2026 OMEGA Solutions — Todos los derechos reservados
        </p>
    </div>

</body>
</html>