<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Gestión de Accesos') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col" style="background-color: #ffffff;">

            {{-- Navbar — Naranja Corporativo Principal --}}
            <nav class="shadow" style="background-color: #DA7E2D;">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">

                        {{-- Logo --}}
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shield-alt text-white text-xl"></i>
                            @if(Auth::check())
                                <a href="{{ route('dashboard') }}"
                                   class="text-white font-heading font-bold text-xl hover:opacity-90">
                                    Gestión de Accesos
                                </a>
                            @else
                                <span class="text-white font-heading font-bold text-xl">
                                    Gestión de Accesos
                                </span>
                            @endif
                        </div>

                        {{-- Opciones --}}
                        <div class="flex items-center gap-3">

                            @if(Auth::check())
                                {{-- Usuario autenticado --}}
                                <div class="flex items-center gap-2 rounded-full px-4 py-1"
                                     style="border: 1px solid rgba(255,255,255,0.5);">
                                    <i class="fas fa-user-circle text-white opacity-80"></i>
                                    <span class="text-white text-sm font-semibold">
                                        {{ Auth::user()->name ?? '' }}
                                    </span>
                                </div>

                                <div class="h-6 w-px bg-white opacity-30"></div>

                                <a href="{{ route('dashboard') }}"
                                   class="text-white px-3 py-1 rounded-lg transition text-sm flex items-center gap-1"
                                   style="border: 1px solid rgba(255,255,255,0.5);"
                                   onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    <i class="fas fa-home"></i> Inicio
                                </a>

                                @php
                                    $notifNoLeidas = \App\Models\Notificacion::where('id_empleado', Auth::user()->idSam())
                                        ->where('leida', false)->count();
                                @endphp
                                <a href="{{ route('notificaciones.index') }}"
                                   class="relative text-white px-3 py-1 rounded-lg transition text-sm flex items-center gap-1"
                                   style="border: 1px solid rgba(255,255,255,0.5);"
                                   onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    <i class="fas fa-bell"></i> Notificaciones
                                    @if($notifNoLeidas > 0)
                                        <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
                                            {{ $notifNoLeidas > 9 ? '9+' : $notifNoLeidas }}
                                        </span>
                                    @endif
                                </a>

                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf
                                    <button type="submit"
                                        class="font-semibold px-3 py-1 rounded-lg hover:opacity-90 transition text-sm flex items-center gap-1"
                                        style="background-color: white; color: #DA7E2D;">
                                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                                    </button>
                                </form>

                            @else
                                {{-- Vigilante sin login --}}
                                <div class="flex items-center gap-2 rounded-full px-4 py-1"
                                     style="border: 1px solid rgba(255,255,255,0.5);">
                                    <i class="fas fa-user-shield text-white opacity-80"></i>
                                    <span class="text-white text-sm font-semibold">
                                        {{ session('vigilante_area', 'Vigilante') }}
                                    </span>
                                </div>

                                <div class="h-6 w-px bg-white opacity-30"></div>

                                <a href="{{ route('vigilante.historial') }}"
                                   class="text-white px-3 py-1 rounded-lg transition text-sm flex items-center gap-1"
                                   style="border: 1px solid rgba(255,255,255,0.5);"
                                   onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    <i class="fas fa-history"></i> Historial
                                </a>

                                <a href="{{ route('login') }}"
                                   class="font-semibold px-3 py-1 rounded-lg hover:opacity-90 transition text-sm flex items-center gap-1"
                                   style="background-color: white; color: #DA7E2D;">
                                    <i class="fas fa-sign-out-alt"></i> Salir
                                </a>
                            @endif

                        </div>
                    </div>
                </div>
            </nav>

            {{-- Page Heading — Naranja Tabla --}}
            @isset($header)
                <header style="background-color: #E26A23; border-top: 1px solid rgba(255,255,255,0.15);">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center justify-between">
                            <h1 class="text-white font-heading font-bold text-xl flex items-center gap-2">
                                {{ $header }}
                            </h1>
                            @if(Auth::check() && Auth::user()->hasRole('administrador'))
                                <span class="text-white text-xs flex items-center gap-1 opacity-80">
                                    <i class="fas fa-user-shield"></i> Administrador
                                </span>
                            @elseif(Auth::check() && Auth::user()->hasRole('autorizador'))
                                <span class="text-white text-xs flex items-center gap-1 opacity-80">
                                    <i class="fas fa-clipboard-check"></i> Autorizador
                                </span>
                            @elseif(Auth::check() && Auth::user()->hasRole('solicitante'))
                                <span class="text-white text-xs flex items-center gap-1 opacity-80">
                                    <i class="fas fa-user"></i> Solicitante
                                </span>
                            @elseif(!Auth::check())
                                <span class="text-white text-xs flex items-center gap-1 opacity-80">
                                    <i class="fas fa-shield-alt"></i> Vigilante
                                </span>
                            @endif
                        </div>
                    </div>
                </header>
            @endisset

            {{-- Modal de advertencia de inactividad --}}
            @if(Auth::check())
            <div id="modal-inactividad" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-xl shadow-xl p-8 max-w-sm w-full text-center">
                    <div class="rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4"
                         style="background-color: #F7F4B1;">
                        <i class="fas fa-clock text-3xl" style="color: #DA7E2D;"></i>
                    </div>
                    <h2 class="font-heading font-bold text-omg-nile text-xl mb-2">¿Sigues ahí?</h2>
                    <p class="text-omg-slate text-sm mb-2">Tu sesión cerrará por inactividad en:</p>
                    <p class="text-4xl font-bold mb-4" id="cuenta-regresiva" style="color: #DA7E2D;">60</p>
                    <p class="text-xs text-gray-400 mb-6">Si no realizas ninguna acción, serás redirigido al login.</p>
                    <button onclick="reiniciarTimer()"
                        class="w-full text-white py-2 rounded-lg font-heading font-semibold hover:opacity-90"
                        style="background-color: #DA7E2D;">
                        <i class="fas fa-check mr-1"></i> Continuar sesión
                    </button>
                </div>
            </div>
            @endif

            {{-- Page Content --}}
            <main class="py-8 flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>

            {{-- Footer — Naranja Corporativo --}}
            <footer class="text-white text-center text-xs py-4" style="background-color: #DA7E2D;">
                <i class="fas fa-shield-alt mr-1"></i>
                © {{ date('Y') }} OMEGA Solutions — Sistema de Gestión de Accesos
            </footer>

        </div>

        {{-- Script de inactividad --}}
        @if(Auth::check())
        <script>
            const MINUTOS_INACTIVIDAD = 20;
            const SEGUNDOS_ADVERTENCIA = 60;

            let timerInactividad;
            let timerCuentaRegresiva;
            let segundosRestantes = SEGUNDOS_ADVERTENCIA;
            const modal = document.getElementById('modal-inactividad');
            const cuentaRegresiva = document.getElementById('cuenta-regresiva');

            function mostrarAviso() {
                segundosRestantes = SEGUNDOS_ADVERTENCIA;
                modal.classList.remove('hidden');
                timerCuentaRegresiva = setInterval(() => {
                    segundosRestantes--;
                    cuentaRegresiva.textContent = segundosRestantes;
                    if (segundosRestantes <= 0) {
                        clearInterval(timerCuentaRegresiva);
                        document.getElementById('logout-form').submit();
                    }
                }, 1000);
            }

            function reiniciarTimer() {
                clearInterval(timerCuentaRegresiva);
                modal.classList.add('hidden');
                clearTimeout(timerInactividad);
                timerInactividad = setTimeout(mostrarAviso, MINUTOS_INACTIVIDAD * 60 * 1000);
            }

            ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evento => {
                document.addEventListener(evento, () => {
                    if (modal.classList.contains('hidden')) {
                        reiniciarTimer();
                    }
                });
            });

            reiniciarTimer();
        </script>
        @endif

        @livewireScripts
    </body>
</html>