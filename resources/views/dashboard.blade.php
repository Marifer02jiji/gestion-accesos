<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="grid grid-cols-1 gap-6">

        {{-- Módulo Solicitante --}}
        @role('solicitante')
        <a href="{{ route('solicitudes.index') }}"
            class="bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-omg-coral">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-file-alt text-omg-coral text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Mis Solicitudes</h3>
                    <p class="text-sm text-omg-slate">Gestión de visitas</p>
                </div>
            </div>
            <p class="text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Crea y consulta tus solicitudes de visita.
            </p>
        </a>
        @endrole

        {{-- Módulo Autorizador --}}
        @role('autorizador')
        <a href="{{ route('autorizador.index') }}"
            class="bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-omg-nile">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-clipboard-check text-omg-nile text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Solicitudes Pendientes</h3>
                    <p class="text-sm text-omg-slate">Aprobar solicitudes</p>
                </div>
            </div>
            <p class="text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Autoriza o rechaza solicitudes pendientes.
            </p>
        </a>
        @endrole

        {{-- Módulo Vigilante --}}
        @role('vigilante')
        <a href="{{ route('vigilante.index') }}"
            class="bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-omg-kashmir">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-shield-alt text-omg-kashmir text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Control de Accesos</h3>
                    <p class="text-sm text-omg-slate">Escanear QR</p>
                </div>
            </div>
            <p class="text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Escanea QR y registra entradas y salidas.
            </p>
        </a>
        @endrole

    </div>
</x-app-layout>