<x-app-layout>
    <x-slot name="header">
        Reportes y Estadísticas
    </x-slot>

    <div class="space-y-6">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-omg-nile">
                <p class="text-xs text-omg-kashmir font-semibold"><i class="fas fa-list mr-1"></i> Total Solicitudes</p>
                <p class="text-3xl font-bold text-omg-nile">{{ $totalSolicitudes }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-yellow-500">
                <p class="text-xs text-omg-kashmir font-semibold"><i class="fas fa-clock mr-1"></i> Pendientes</p>
                <p class="text-3xl font-bold text-yellow-500">{{ $pendientes }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-500">
                <p class="text-xs text-omg-kashmir font-semibold"><i class="fas fa-check-circle mr-1"></i> Autorizadas</p>
                <p class="text-3xl font-bold text-green-500">{{ $autorizadas }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-red-500">
                <p class="text-xs text-omg-kashmir font-semibold"><i class="fas fa-times-circle mr-1"></i> Rechazadas</p>
                <p class="text-3xl font-bold text-red-500">{{ $rechazadas }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-gray-500">
                <p class="text-xs text-omg-kashmir font-semibold"><i class="fas fa-ban mr-1"></i> Canceladas</p>
                <p class="text-3xl font-bold text-gray-500">{{ $canceladas }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-omg-coral">
                <p class="text-xs text-omg-kashmir font-semibold"><i class="fas fa-door-open mr-1"></i> Total Accesos</p>
                <p class="text-3xl font-bold text-omg-coral">{{ $totalAccesos }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-omg-kashmir">
                <p class="text-xs text-omg-kashmir font-semibold"><i class="fas fa-user-check mr-1"></i> En Campus</p>
                <p class="text-3xl font-bold text-omg-kashmir">{{ $visitantesActivos }}</p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-heading font-semibold text-omg-nile flex items-center gap-2">
                    <i class="fas fa-history"></i> Últimas 10 Solicitudes
                </h3>
                <a href="{{ route('admin.visitantes-activos') }}"
                   class="bg-omg-coral text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm">
                    <i class="fas fa-user-check"></i> Ver Visitantes Activos
                </a>
            </div>

            <table class="w-full text-sm text-left border">
                <thead class="bg-omg-nile text-white">
                    <tr>
                        <th class="px-4 py-2">Folio</th>
                        <th class="px-4 py-2">Solicitante</th>
                        <th class="px-4 py-2">Fecha</th>
                        <th class="px-4 py-2">Tipo</th>
                        <th class="px-4 py-2">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimasSolicitudes as $s)
                    <tr class="hover:bg-omg-chardon border-b">
                        <td class="px-4 py-2 font-mono font-bold text-omg-nile">
                            VIS-{{ str_pad($s->id_solicitud, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-2">{{ $s->solicitante->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $s->fecha_inicio }}</td>
                        <td class="px-4 py-2">{{ $s->tipo->nombre ?? 'N/A' }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded-full text-white text-xs font-semibold
                                @if($s->estado->nombre == 'Pendiente') bg-yellow-500
                                @elseif($s->estado->nombre == 'Autorizada') bg-green-500
                                @elseif($s->estado->nombre == 'Cancelada') bg-gray-500
                                @else bg-red-500 @endif">
                                {{ $s->estado->nombre }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center px-4 py-8 text-gray-400">
                            <i class="fas fa-folder-open text-3xl mb-2 block"></i>
                            No se encontraron registros
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>