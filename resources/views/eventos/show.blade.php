{{--
Empresa:     OMEGA Solutions
Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
Archivo:     resources/views/eventos/show.blade.php
Creación:    28/05/2026
Creado por:  Jacqueline Marifer Escobar Espinoza
Aprobado por: Líder de Área

Changelog:
ID: 1 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, detalle del evento con QR generado y botón de reenvío
--}}

<x-app-layout>
    <x-slot name="header">
        Detalle de Evento
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg p-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Encabezado folio --}}
        <div class="bg-omg-chardon border border-omg-kashmir rounded-xl px-5 py-3 mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs text-omg-kashmir font-semibold">Folio del Evento</p>
                <p class="font-heading font-bold text-omg-nile text-xl tracking-widest">{{ $evento->folio }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-omg-kashmir font-semibold">Estado</p>
                <span class="px-3 py-1 rounded-full text-white text-xs font-semibold bg-green-500">
                    QR Generado y Enviado
                </span>
            </div>
        </div>

        <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
            <i class="fas fa-calendar-alt"></i> Información del Evento
        </h3>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;"><i class="fas fa-tag mr-1"></i> Nombre</p>
                <p class="font-semibold text-omg-slate text-lg">{{ $evento->nombre_evento }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;"><i class="fas fa-list mr-1"></i> Tipo</p>
                <p class="font-semibold text-omg-slate">{{ $evento->tipo_evento }}</p>
            </div>
            @if($evento->descripcion)
            <div class="col-span-2">
                <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;"><i class="fas fa-file-alt mr-1"></i> Descripción</p>
                <p class="font-semibold text-omg-slate">{{ $evento->descripcion }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;"><i class="fas fa-map-marker-alt mr-1"></i> Lugar</p>
                <p class="font-semibold text-omg-slate">{{ $evento->lugar }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;"><i class="fas fa-calendar mr-1"></i> Fecha y Hora</p>
                <p class="font-semibold text-omg-slate">{{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;"><i class="fas fa-users mr-1"></i> Número de Personas</p>
                <p class="font-bold text-2xl" style="color: #DA7E2D;">{{ $evento->numero_personas }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;"><i class="fas fa-user mr-1"></i> Responsable</p>
                <p class="font-semibold text-omg-slate">{{ $evento->nombre_responsable }}</p>
                <p class="text-xs text-omg-kashmir">{{ $evento->correo_responsable }}</p>
            </div>
        </div>

        {{-- QR --}}
        @if($evento->qr)
        <div class="bg-omg-chardon border border-omg-kashmir rounded-xl p-4 mb-6">
            <p class="text-xs font-semibold mb-3" style="color: #A9AAAD;"><i class="fas fa-qrcode mr-1"></i> Código QR del Evento</p>
            <div class="flex items-center gap-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($evento->qr->codigo_numerico) }}"
                    alt="QR Evento" class="rounded-lg border border-omg-kashmir">
                <div>
                    <p class="font-mono font-bold text-omg-nile text-lg">{{ $evento->qr->codigo_numerico }}</p>
                    <p class="text-xs text-omg-kashmir mt-1">
                        Válido: {{ \Carbon\Carbon::parse($evento->qr->vigencia_inicio)->format('d/m/Y H:i') }}
                        — {{ \Carbon\Carbon::parse($evento->qr->vigencia_final)->format('d/m/Y H:i') }}
                    </p>
                    <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                        <i class="fas fa-check-circle"></i> Enviado a {{ $evento->correo_responsable }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <div class="flex justify-end gap-3 flex-wrap">
            <a href="{{ route('eventos.index') }}"
               class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
               style="background-color: #3B5675;">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>

            @if($evento->qr)
                <form action="{{ route('eventos.reenviarQR', $evento->id_evento) }}" method="POST"
                    onsubmit="return confirm('¿Reenviar el QR al responsable?')">
                    @csrf
                    <button type="submit"
                        class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                        style="background-color: #DA7E2D;">
                        <i class="fas fa-paper-plane"></i> Reenviar QR
                    </button>
                </form>
            @endif

            <form action="{{ route('eventos.destroy', $evento->id_evento) }}" method="POST"
                onsubmit="return confirm('¿Eliminar este evento?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</x-app-layout>