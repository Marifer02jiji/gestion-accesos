<x-app-layout>
    <x-slot name="header">
        Código QR de Acceso
    </x-slot>

    <div class="flex justify-center">
        <div class="bg-white shadow-sm rounded-lg p-8 text-center max-w-md w-full">

            <h3 class="font-heading font-bold text-omg-nile text-xl mb-2 flex items-center justify-center gap-2">
                <i class="fas fa-user"></i>
                {{ $qr->solicitudVisitante->visitante->nombre }}
                {{ $qr->solicitudVisitante->visitante->apellidos }}
            </h3>

            <p class="text-sm text-omg-slate mb-1 flex items-center justify-center gap-1">
                <i class="fas fa-clock text-omg-kashmir"></i>
                Vigencia: {{ $qr->vigencia_inicio }} — {{ $qr->vigencia_final }}
            </p>

            <div class="flex justify-center my-6">
                {!! QrCode::size(250)->generate($qr->codigo_numerico) !!}
            </div>

            <div class="mb-6">
                <p class="text-xs text-omg-kashmir font-semibold mb-1">Código de validación</p>
                <p class="text-xl font-bold text-omg-nile font-mono tracking-widest">{{ $qr->codigo_numerico }}</p>
            </div>
            <div class="flex justify-center gap-4">
                <a href="{{ route('solicitudes.index') }}"
                   class="bg-omg-nile text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Regresar
                </a>
            </div>

        </div>
    </div>
</x-app-layout>