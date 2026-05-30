<x-app-layout>
    <x-slot name="header">
        Detalle de Solicitud
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

        @if($solicitud->folio)
            <div class="bg-omg-chardon border border-omg-kashmir rounded-xl px-5 py-3 mb-6 flex items-center justify-between">
                <div>
                    <p class="text-xs text-omg-kashmir font-semibold">Folio de Solicitud</p>
                    <p class="font-heading font-bold text-omg-nile text-xl tracking-widest">{{ $solicitud->folio }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-omg-kashmir font-semibold">Estado</p>
                    <span class="px-3 py-1 rounded-full text-white text-xs font-semibold
                        @if($solicitud->estado->nombre == 'Pendiente') bg-yellow-500
                        @elseif($solicitud->estado->nombre == 'Autorizada') bg-green-500
                        @elseif($solicitud->estado->nombre == 'En Institucion') bg-blue-500
                        @elseif($solicitud->estado->nombre == 'En Encuentro') bg-purple-500
                        @elseif($solicitud->estado->nombre == 'En Transito a Salida') bg-orange-500
                        @elseif($solicitud->estado->nombre == 'Finalizada') bg-green-700
                        @elseif($solicitud->estado->nombre == 'Cancelada') bg-gray-500
                        @else bg-red-500 @endif">
                        {{ $solicitud->estado->nombre }}
                    </span>
                </div>
            </div>
        @endif

        <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
            <i class="fas fa-file-alt"></i> Información de la Visita
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-omg-kashmir font-semibold flex items-center gap-1">
                    <i class="fas fa-calendar"></i> Fecha de Visita
                </p>
                <p class="font-semibold text-omg-slate">{{ $solicitud->fecha_inicio }}</p>
            </div>
            <div>
                <p class="text-sm text-omg-kashmir font-semibold flex items-center gap-1">
                    <i class="fas fa-map-marker-alt"></i> Lugar de Encuentro
                </p>
                <p class="font-semibold text-omg-slate">{{ $solicitud->lugar_encuentro }}</p>
            </div>
            <div>
                <p class="text-sm text-omg-kashmir font-semibold flex items-center gap-1">
                    <i class="fas fa-file-alt"></i> Motivo
                </p>
                <p class="font-semibold text-omg-slate">{{ $solicitud->motivo_visita }}</p>
            </div>
            <div>
                <p class="text-sm text-omg-kashmir font-semibold flex items-center gap-1">
                    <i class="fas fa-tag"></i> Tipo
                </p>
                <p class="font-semibold text-omg-slate">{{ $solicitud->tipo->nombre }}</p>
            </div>
            <div>
                <p class="text-sm text-omg-kashmir font-semibold flex items-center gap-1">
                    <i class="fas fa-clock"></i> Tolerancia
                </p>
                <p class="font-semibold text-omg-slate">{{ $solicitud->tolerancia_antes }} min antes / {{ $solicitud->tolerancia_despues }} min después</p>
            </div>

            {{-- Tiempos de encuentro --}}
            @if($solicitud->hora_llegada_encuentro)
            <div>
                <p class="text-sm text-omg-kashmir font-semibold flex items-center gap-1">
                    <i class="fas fa-map-marker-alt"></i> Llegada al Encuentro
                </p>
                <p class="font-semibold text-omg-slate">
                    {{ \Carbon\Carbon::parse($solicitud->hora_llegada_encuentro)->format('d/m/Y H:i') }}
                </p>
            </div>
            @endif

            @if($solicitud->hora_salida_encuentro)
            <div>
                <p class="text-sm text-omg-kashmir font-semibold flex items-center gap-1">
                    <i class="fas fa-sign-out-alt"></i> Salida del Encuentro
                </p>
                <p class="font-semibold text-omg-slate">
                    {{ \Carbon\Carbon::parse($solicitud->hora_salida_encuentro)->format('d/m/Y H:i') }}
                </p>
            </div>
            @endif
        </div>

        <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
            <i class="fas fa-users"></i> Visitantes
        </h3>

        <table class="w-full text-sm text-left border mb-6">
            <thead class="text-white" style="background-color: #E26A23;">
                <tr>
                    <th class="px-4 py-2"><i class="fas fa-user mr-1"></i> Nombre</th>
                    <th class="px-4 py-2"><i class="fas fa-user mr-1"></i> Apellidos</th>
                    <th class="px-4 py-2"><i class="fas fa-envelope mr-1"></i> Correo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($solicitud->visitantes as $v)
                <tr class="border-b"
                    onmouseover="this.style.backgroundColor='#FFF3EC'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <td class="px-4 py-2">{{ $v->nombre }}</td>
                    <td class="px-4 py-2">{{ $v->apellidos }}</td>
                    <td class="px-4 py-2">{{ $v->correo_personal }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-end gap-4 flex-wrap">

            {{-- Regresar --}}
            <a href="{{ route('solicitudes.index') }}"
               class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
               style="background-color: #3B5675;">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>

            {{-- Cancelar solicitud --}}
            @if($solicitud->estado->nombre == 'Pendiente')
                <form action="{{ route('solicitudes.cancelar', $solicitud->id_solicitud) }}" method="POST"
                    onsubmit="return confirm('¿Desea cancelar esta solicitud?')">
                    @csrf
                    <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                        <i class="fas fa-times-circle"></i> Cancelar Solicitud
                    </button>
                </form>
            @endif

            {{-- Enviar QR --}}
            @if($solicitud->estado->nombre == 'Autorizada')
                <form action="{{ route('solicitudes.enviarQR', $solicitud->id_solicitud) }}" method="POST"
                    onsubmit="return confirm('¿Desea enviar el pase QR al visitante?')">
                    @csrf
                    <button type="submit"
                        class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                        style="background-color: #DA7E2D;">
                        <i class="fas fa-paper-plane"></i> Enviar QR al Visitante
                    </button>
                </form>
            @endif

            {{-- Botón dinámico encuentro --}}
            @if($solicitud->id_estado_solicitud === 5)
                <form action="{{ route('solicitudes.llegadaEncuentro', $solicitud->id_solicitud) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="text-white px-4 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                        style="background-color: #DA7E2D;">
                        <i class="fas fa-map-marker-alt"></i> Registrar Llegada a Encuentro
                    </button>
                </form>
            @elseif($solicitud->id_estado_solicitud === 6)
                <form action="{{ route('solicitudes.salidaEncuentro', $solicitud->id_solicitud) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="text-white px-4 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                        style="background-color: #E26A23;">
                        <i class="fas fa-sign-out-alt"></i> Registrar Salida de Encuentro
                    </button>
                </form>
            @elseif($solicitud->id_estado_solicitud === 7)
                <span class="text-xs font-semibold px-3 py-2 rounded-lg border flex items-center gap-1"
                    style="color: #A9AAAD; border-color: #A9AAAD;">
                    <i class="fas fa-walking"></i> En Tránsito a la Salida
                </span>
            @elseif($solicitud->id_estado_solicitud === 8)
                <span class="text-xs font-semibold px-3 py-2 rounded-lg border border-green-400 text-green-600 flex items-center gap-1">
                    <i class="fas fa-check-circle"></i> Visita Finalizada
                </span>
            @endif

        </div>
    </div>
</x-app-layout>