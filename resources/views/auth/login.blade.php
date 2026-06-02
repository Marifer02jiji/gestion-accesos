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
<body class="min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg, #F29066 0%, #3B5675 100%);">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="font-heading font-bold text-white text-3xl">Gestión de Accesos</h1>
            <p class="text-white text-sm mt-1">Sistema de Control de Visitas</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">

            <h2 class="font-heading font-bold text-omg-nile text-xl mb-6 text-center">
                Iniciar Sesión
            </h2>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                    <p>✓ {{ session('success') }}</p>
                </div>
            @endif

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
                        placeholder="Usuario o correo institucional"
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
                    class="w-full text-white py-2 rounded-lg font-heading font-semibold hover:opacity-90 transition flex items-center justify-center gap-2"
                    style="background-color: #DA7E2D;">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>

            </form>
        </div>

        <p class="text-center text-white text-xs mt-6">
            © {{ date('Y') }} OMEGA Solutions — Todos los derechos reservados
        </p>
    </div>

</body>
</html>