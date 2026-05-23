<x-app-layout>
    <x-slot name="header">
        Confirmación de Acceso
    </x-slot>

    <div class="flex justify-center">
        <div class="bg-white shadow-sm rounded-lg p-8 text-center max-w-md w-full">

            <div class="flex justify-center mb-6">
                <div class="bg-green-100 rounded-full p-6 animate-bounce">
                    <i class="fas fa-check-circle text-green-500 text-6xl"></i>
                </div>
            </div>

            <h2 class="font-heading font-bold text-omg-nile text-2xl mb-2">
                {{ $tipo == 'entrada' ? '¡Entrada Registrada!' : '¡Salida Registrada!' }}
            </h2>

            <p class="text-omg-slate mb-2 font-semibold text-lg">
                {{ $nombre }}
            </p>

            <p class="text-omg-kashmir text-sm mb-6">
                {{ $tipo == 'entrada' ? 'Hora de entrada' : 'Hora de salida' }}:
                <span class="font-bold text-omg-slate">{{ now()->format('H:i:s') }}</span>
            </p>

            <a href="{{ route('vigilante.index') }}"
               class="bg-omg-coral text-white px-6 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center justify-center gap-2">
                <i class="fas fa-qrcode"></i> Nuevo Escaneo
            </a>

        </div>
    </div>
</x-app-layout>