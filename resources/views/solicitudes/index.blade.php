<x-app-layout>
    <x-slot name="header">
        Mis Solicitudes
    </x-slot>

    <div>
        {{-- Alertas --}}
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

            {{-- Barra superior --}}
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-heading font-semibold text-omg-slate flex items-center gap-2">
                    <i class="fas fa-list" style="color: #DA7E2D;"></i>
                    Lista de Solicitudes
                </h3>
                <a href="{{ route('solicitudes.create') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                   style="background-color: #DA7E2D;">
                    <i class="fas fa-plus"></i> Nueva Solicitud
                </a>
            </div>

            {{-- Tabla --}}
            <table class="w-full text-sm text-left border">
                <thead class="text-white" style="background-color: #E26A23;">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Fecha</th>
                        <th class="px-4 py-2">Lugar</th>
                        <th class="px-4 py-2">Motivo</th>
                        <th class="px-4 py-2">Estado</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $s)
                    @php $fechaPasada = now() > \Carbon\Carbon::parse($s->fecha_inicio); @endphp
                    <tr class="border-b" style="transition: background 0.2s;"
                        onmouseover="this.style.backgroundColor='#FFF3EC'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        <td class="px-4 py-2 font-mono text-xs" style="color: #DA7E2D;">
                            {{ $s->folio ?? $s->id_solicitud }}
                        </td>
                        <td class="px-4 py-2">
                            {{ \Carbon\Carbon::parse($s->fecha_inicio)->format('d/m/Y H:i') }}
                            @if($fechaPasada && !in_array($s->estado->nombre, ['Finalizada','Cancelada','Rechazada']))
                                <span class="block text-xs text-gray-400">
                                    <i class="fas fa-clock mr-1"></i> Expirada
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $s->lugar_encuentro }}</td>
                        <td class="px-4 py-2">{{ $s->motivo_visita }}</td>
                        <td class="px-4 py-2">
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
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex gap-2 flex-wrap">
                                <a href="{{ route('solicitudes.show', $s->id_solicitud) }}"
                                   class="text-white px-3 py-1 rounded hover:opacity-90 text-xs flex items-center gap-1"
                                   style="background-color: #3B5675;">
                                    <i class="fas fa-eye"></i> Ver
                                </a>

                                {{-- Botón dinámico encuentro --}}
                                @if($s->id_estado_solicitud === 5)
                                    <form action="{{ route('solicitudes.llegadaEncuentro', $s->id_solicitud) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="text-white px-3 py-1 rounded hover:opacity-90 text-xs flex items-center gap-1"
                                            style="background-color: #16a34a;">
                                            <i class="fas fa-map-marker-alt"></i> Llegada Encuentro
                                        </button>
                                    </form>
                                @elseif($s->id_estado_solicitud === 6)
                                    <form action="{{ route('solicitudes.salidaEncuentro', $s->id_solicitud) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="text-white px-3 py-1 rounded hover:opacity-90 text-xs flex items-center gap-1"
                                            style="background-color: #16a34a;">
                                            <i class="fas fa-sign-out-alt"></i> Salida Encuentro
                                        </button>
                                    </form>
                                @endif

                                {{-- Eliminar: cancelada, rechazada, finalizada o fecha pasada --}}
                                @if(in_array($s->estado->nombre, ['Cancelada', 'Rechazada', 'Finalizada']) || $fechaPasada)
                                    <form action="{{ route('solicitudes.destroy', $s->id_solicitud) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar esta solicitud?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-600 text-white px-3 py-1 rounded hover:opacity-90 text-xs flex items-center gap-1">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center px-4 py-8">
                            <div class="flex flex-col items-center gap-2 text-gray-400">
                                <i class="fas fa-folder-open text-4xl"></i>
                                <p class="font-semibold">No se encontraron registros</p>
                                <p class="text-sm">Crea tu primera solicitud usando el botón "Nueva Solicitud"</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $solicitudes->links() }}
            </div>

             {{-- Link a historial --}}
            <div class="mt-4 text-right">
                <a href="{{ route('solicitudes.historial') }}"
                    class="text-sm flex items-center gap-1 justify-end"
                    style="color: #DA7E2D;">
                    <i class="fas fa-history"></i> Ver historial de visitas finalizadas
                </a>
            </div>

        </div>
    </div>
</x-app-layout>