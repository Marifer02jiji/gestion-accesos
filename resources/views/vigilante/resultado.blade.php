<x-app-layout>
    <x-slot name="header">
        Resultado del Escaneo
    </x-slot>

    <div>
        <div class="bg-white shadow-sm rounded-lg p-6 mb-4">

            <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                <i class="fas fa-user-check"></i> Información del Visitante
            </h3>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-xs text-omg-kashmir font-semibold flex items-center gap-1">
                        <i class="fas fa-user"></i> Nombre
                    </p>
                    <p class="font-semibold text-omg-slate">
                        {{ $qr->solicitudVisitante->visitante->nombre }}
                        {{ $qr->solicitudVisitante->visitante->apellidos }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-omg-kashmir font-semibold flex items-center gap-1">
                        <i class="fas fa-envelope"></i> Correo
                    </p>
                    <p class="font-semibold text-omg-slate">
                        {{ $qr->solicitudVisitante->visitante->correo_personal }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-omg-kashmir font-semibold flex items-center gap-1">
                        <i class="fas fa-file-alt"></i> Motivo
                    </p>
                    <p class="font-semibold text-omg-slate">
                        {{ $qr->solicitudVisitante->solicitud->motivo_visita }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-omg-kashmir font-semibold flex items-center gap-1">
                        <i class="fas fa-clock"></i> Vigencia
                    </p>
                    <p class="font-semibold text-omg-slate">
                        {{ $qr->vigencia_inicio }} — {{ $qr->vigencia_final }}
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('vigilante.index') }}"
                   class="bg-omg-pastel text-omg-slate px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <form action="{{ route('vigilante.salida') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_qr" value="{{ $qr->id_qr }}">
                    <button type="submit"
                        class="bg-omg-nile text-white px-6 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                        <i class="fas fa-sign-out-alt"></i> Registrar Salida
                    </button>
                </form>
                <form action="{{ route('vigilante.entrada') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_qr" value="{{ $qr->id_qr }}">
                    <button type="submit"
                        class="bg-omg-coral text-white px-6 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Registrar Entrada
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>