<x-app-layout>
    <x-slot name="header">
        Eventos
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
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-heading font-semibold text-omg-slate flex items-center gap-2">
                    <i class="fas fa-calendar-alt" style="color: #DA7E2D;"></i>
                    Lista de Eventos
                </h3>
                <a href="{{ route('eventos.create') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                   style="background-color: #DA7E2D;">
                    <i class="fas fa-plus"></i> Nuevo Evento
                </a>
            </div>

            <table class="w-full text-sm text-left border">
                <thead class="text-white" style="background-color: #E26A23;">
                    <tr>
                        <th class="px-4 py-2">Folio</th>
                        <th class="px-4 py-2">Tipo</th>
                        <th class="px-4 py-2">Responsable</th>
                        <th class="px-4 py-2">Lugar</th>
                        <th class="px-4 py-2">Fecha</th>
                        <th class="px-4 py-2">Personas</th>
                        <th class="px-4 py-2">Estado</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventos as $e)
                    <tr class="border-b"
                        onmouseover="this.style.backgroundColor='#FFF3EC'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        <td class="px-4 py-2 font-mono text-xs" style="color: #DA7E2D;">
                            {{ str_replace('EVT-', '', $e->folio) }}
                        </td>
                        <td class="px-4 py-2 font-semibold">{{ $e->tipo_evento }}</td>
                        <td class="px-4 py-2">
                            <span class="font-semibold">{{ $e->nombre_responsable }}</span>
                            <span class="block text-xs text-gray-400">{{ $e->correo_responsable }}</span>
                        </td>
                        <td class="px-4 py-2">{{ $e->lugar }}</td>
                        <td class="px-4 py-2">
                            {{ \Carbon\Carbon::parse($e->fecha_evento)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="font-bold" style="color: #DA7E2D;">{{ $e->numero_personas }}</span>
                        </td>
                        <td class="px-4 py-2">
                            @if($e->id_estado_solicitud == 1)
                                <span class="px-2 py-1 rounded-full text-white text-xs font-semibold bg-yellow-500">
                                    Sin QR
                                </span>
                            @elseif($e->id_estado_solicitud == 2)
                                <span class="px-2 py-1 rounded-full text-white text-xs font-semibold bg-green-500">
                                    QR Enviado
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex gap-2">
                                <a href="{{ route('eventos.show', $e->id_evento) }}"
                                   class="text-white px-3 py-1 rounded hover:opacity-90 text-xs flex items-center gap-1"
                                   style="background-color: #DA7E2D;">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <form action="{{ route('eventos.destroy', $e->id_evento) }}" method="POST"
                                    onsubmit="return confirm('¿Eliminar este evento?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:opacity-90 text-xs flex items-center gap-1">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center px-4 py-8 text-gray-400">
                            <i class="fas fa-calendar-times text-4xl mb-2 block"></i>
                            <p class="font-semibold">No hay eventos registrados</p>
                            <p class="text-sm">Crea tu primer evento usando el botón "Nuevo Evento"</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">{{ $eventos->links() }}</div>
        </div>
    </div>
</x-app-layout>