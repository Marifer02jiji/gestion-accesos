{{--
Empresa:     OMEGA Solutions
Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
Archivo:     resources/views/autorizador/index.blade.php
Creación:    19/03/2026
Creado por:  Jacqueline Marifer Escobar Espinoza
Aprobado por: Líder de Área

Changelog:
ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, lista de solicitudes pendientes para el autorizador
ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Botones de autorizar y rechazar con confirmación
ID: 3 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Seguimiento de estados de visita (pasos 1-4) para visitas autorizadas
--}}

<x-app-layout>
    <x-slot name="header">
        Solicitudes
        <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-bold ml-2">
            Pendientes de Autorización
        </span>
    </x-slot>

    <div>
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

        <div class="bg-white shadow-sm rounded-lg p-6">

            {{-- Barra superior con botón de reportes --}}
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-heading font-semibold text-omg-slate flex items-center gap-2">
                    <i class="fas fa-list" style="color: #DA7E2D;"></i>
                    Solicitudes para Autorizar
                </h3>
                <a href="{{ route('autorizador.reportes') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                   style="background-color: #3B5675;">
                    <i class="fas fa-check-circle"></i> Mis solicitudes autorizadas
                </a>
            </div>
            <form method="GET" action="{{ route('autorizador.index') }}" class="mb-6">
                <div class="flex gap-3 items-end flex-wrap">
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-user mr-1"></i> Solicitante
                        </label>
                        <input type="text" name="solicitante" value="{{ $solicitante ?? '' }}"
                            placeholder="Nombre del solicitante"
                            class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile"
                            style="width: 200px;">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-envelope mr-1"></i> Correo
                        </label>
                        <input type="text" name="correo" value="{{ $correo ?? '' }}"
                            placeholder="Correo visitante o solicitante"
                            class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile"
                            style="width: 220px;">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-calendar mr-1"></i> Fecha de visita
                        </label>
                        <input type="date" name="fecha" value="{{ $fecha ?? '' }}"
                            class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile"
                            style="width: 150px;">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-clock mr-1"></i> Hora de visita
                        </label>
                        <input type="time" name="hora" value="{{ $hora ?? '' }}"
                            class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile"
                            style="width: 130px;">
                    </div>
                    <button type="submit"
                        class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                        style="background-color: #DA7E2D;">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('autorizador.index') }}"
                        class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                        style="background-color: #A9AAAD;">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>

            @forelse($solicitudes as $s)
            @php $fechaPasada = now() > \Carbon\Carbon::parse($s->fecha_inicio); @endphp
            <div class="border rounded-xl p-4 mb-4 transition hover:shadow-md {{ $fechaPasada && $s->estado->nombre == 'Pendiente' ? 'opacity-70' : '' }}"
                 style="border-color: {{ $fechaPasada && $s->estado->nombre == 'Pendiente' ? '#e5e7eb' : '#A9AAAD' }}; background-color: {{ $fechaPasada && $s->estado->nombre == 'Pendiente' ? '#f9fafb' : '#FFF3EC' }};">

                <div class="grid grid-cols-3 gap-4 mb-3">
                    <div>
                        <p class="text-xs font-semibold flex items-center gap-1" style="color: #A9AAAD;">
                            <i class="fas fa-user"></i> Solicitante
                        </p>
                        <p class="font-semibold text-omg-slate">{{ $s->solicitante->name ?? 'Sin nombre' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold flex items-center gap-1" style="color: #A9AAAD;">
                            <i class="fas fa-calendar"></i> Fecha de Visita
                        </p>
                        <p class="font-semibold text-omg-slate">
                            {{ \Carbon\Carbon::parse($s->fecha_inicio)->format('d/m/Y H:i') }}
                            @if($fechaPasada)
                                <span class="block text-xs text-red-500 font-semibold mt-0.5">
                                    <i class="fas fa-clock mr-1"></i> Fecha vencida
                                </span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold flex items-center gap-1" style="color: #A9AAAD;">
                            <i class="fas fa-info-circle"></i> Estado
                        </p>
                        <span class="px-2 py-1 rounded-full text-white text-xs font-semibold
                            @if($s->estado->nombre == 'Pendiente') bg-yellow-500
                            @elseif($s->estado->nombre == 'Autorizada') bg-green-500
                            @elseif($s->estado->nombre == 'En Institucion') bg-blue-500
                            @elseif($s->estado->nombre == 'En Encuentro') bg-purple-500
                            @elseif($s->estado->nombre == 'En Transito a Salida') bg-orange-500
                            @elseif($s->estado->nombre == 'Finalizada') bg-green-700
                            @elseif($s->estado->nombre == 'Cancelada') bg-gray-500
                            @else bg-red-500 @endif">
                            {{ $s->estado->nombre }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold flex items-center gap-1" style="color: #A9AAAD;">
                            <i class="fas fa-map-marker-alt"></i> Lugar
                        </p>
                        <p class="font-semibold text-omg-slate">{{ $s->lugar_encuentro }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold flex items-center gap-1" style="color: #A9AAAD;">
                            <i class="fas fa-file-alt"></i> Motivo
                        </p>
                        <p class="font-semibold text-omg-slate">{{ $s->motivo_visita }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold flex items-center gap-1" style="color: #A9AAAD;">
                            <i class="fas fa-users"></i> Visitantes
                        </p>
                        @foreach($s->visitantes as $v)
                            <p class="text-omg-slate text-sm">{{ $v->nombre }} {{ $v->apellidos }}</p>
                        @endforeach
                    </div>
                </div>

                {{-- Seguimiento de estados para visitas en curso --}}
                @if(in_array($s->id_estado_solicitud, [5, 6, 7, 8]))
                    <div class="mt-3 mb-3 bg-white rounded-lg border p-3" style="border-color: #A9AAAD;">
                        <p class="text-xs font-semibold mb-3" style="color: #A9AAAD;">
                            <i class="fas fa-route mr-1"></i> Seguimiento de la visita
                        </p>
                        <div class="flex items-center gap-2 flex-wrap">

                            {{-- Paso 1: Llegó a institución --}}
                            <div class="flex items-center gap-1">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white
                                    {{ in_array($s->id_estado_solicitud, [5,6,7,8]) ? 'bg-blue-500' : 'bg-gray-300' }}">
                                    1
                                </span>
                                <span class="text-xs font-semibold {{ in_array($s->id_estado_solicitud, [5,6,7,8]) ? 'text-blue-600' : 'text-gray-400' }}">
                                    Llegó a institución
                                </span>
                            </div>

                            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>

                            {{-- Paso 2: En encuentro --}}
                            <div class="flex items-center gap-1">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white
                                    {{ in_array($s->id_estado_solicitud, [6,7,8]) ? 'bg-purple-500' : 'bg-gray-300' }}">
                                    2
                                </span>
                                <span class="text-xs font-semibold {{ in_array($s->id_estado_solicitud, [6,7,8]) ? 'text-purple-600' : 'text-gray-400' }}">
                                    En encuentro
                                </span>
                            </div>

                            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>

                            {{-- Paso 3: En tránsito --}}
                            <div class="flex items-center gap-1">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white
                                    {{ in_array($s->id_estado_solicitud, [7,8]) ? 'bg-orange-500' : 'bg-gray-300' }}">
                                    3
                                </span>
                                <span class="text-xs font-semibold {{ in_array($s->id_estado_solicitud, [7,8]) ? 'text-orange-600' : 'text-gray-400' }}">
                                    En tránsito a salida
                                </span>
                            </div>

                            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>

                            {{-- Paso 4: Finalizada --}}
                            <div class="flex items-center gap-1">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white
                                    {{ $s->id_estado_solicitud == 8 ? 'bg-green-700' : 'bg-gray-300' }}">
                                    4
                                </span>
                                <span class="text-xs font-semibold {{ $s->id_estado_solicitud == 8 ? 'text-green-700' : 'text-gray-400' }}">
                                    Salió de institución
                                </span>
                            </div>

                        </div>
                    </div>
                @endif

                @if($s->estado->nombre == 'Pendiente')
                    @if($fechaPasada)
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-red-500 font-semibold flex items-center gap-1 px-3 py-2 bg-red-50 rounded-lg border border-red-200">
                                <i class="fas fa-ban"></i> Esta solicitud ya no puede autorizarse — la fecha de visita venció
                            </span>
                            <form action="{{ route('autorizador.rechazar', $s->id_solicitud) }}" method="POST"
                                onsubmit="return confirm('La fecha ya venció. ¿Rechazar esta solicitud?')">
                                @csrf
                                <button type="submit"
                                    class="bg-gray-400 text-white px-3 py-1 rounded-lg hover:opacity-90 text-xs flex items-center gap-1">
                                    <i class="fas fa-times-circle"></i> Rechazar y cerrar
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex justify-end gap-3">
                            <form action="{{ route('autorizador.rechazar', $s->id_solicitud) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                                    <i class="fas fa-times-circle"></i> Rechazar
                                </button>
                            </form>
                            <form action="{{ route('autorizador.autorizar', $s->id_solicitud) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="text-white px-4 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                                    style="background-color: #DA7E2D;">
                                    <i class="fas fa-check-circle"></i> Autorizar
                                </button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                <div class="flex flex-col items-center gap-2">
                    <i class="fas fa-clipboard-check text-4xl"></i>
                    <p class="font-semibold">No se encontraron registros</p>
                    <p class="text-sm">No hay solicitudes pendientes con los filtros aplicados</p>
                </div>
            </div>
            @endforelse

            <div class="mt-4">
                {{ $solicitudes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>