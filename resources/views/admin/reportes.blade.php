<x-app-layout>
    <x-slot name="header">
        Reportes y Estadísticas
    </x-slot>

    <div class="space-y-6">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-5 shadow-sm" style="border-left: 4px solid #DA7E2D;">
                <p class="text-xs font-semibold" style="color: #A9AAAD;"><i class="fas fa-list mr-1"></i> Total Solicitudes</p>
                <p class="text-3xl font-bold" style="color: #DA7E2D;">{{ $totalSolicitudes }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-yellow-500">
                <p class="text-xs font-semibold" style="color: #A9AAAD;"><i class="fas fa-clock mr-1"></i> Pendientes</p>
                <p class="text-3xl font-bold text-yellow-500">{{ $pendientes }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-500">
                <p class="text-xs font-semibold" style="color: #A9AAAD;"><i class="fas fa-check-circle mr-1"></i> Autorizadas</p>
                <p class="text-3xl font-bold text-green-500">{{ $autorizadas }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-red-500">
                <p class="text-xs font-semibold" style="color: #A9AAAD;"><i class="fas fa-times-circle mr-1"></i> Rechazadas</p>
                <p class="text-3xl font-bold text-red-500">{{ $rechazadas }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-gray-500">
                <p class="text-xs font-semibold" style="color: #A9AAAD;"><i class="fas fa-ban mr-1"></i> Canceladas</p>
                <p class="text-3xl font-bold text-gray-500">{{ $canceladas }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm" style="border-left: 4px solid #E26A23;">
                <p class="text-xs font-semibold" style="color: #A9AAAD;"><i class="fas fa-door-open mr-1"></i> Total Accesos</p>
                <p class="text-3xl font-bold" style="color: #E26A23;">{{ $totalAccesos }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm" style="border-left: 4px solid #DA7E2D;">
                <p class="text-xs font-semibold" style="color: #A9AAAD;"><i class="fas fa-user-check mr-1"></i> En Institución</p>
                <p class="text-3xl font-bold" style="color: #DA7E2D;">{{ $visitantesActivos }}</p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-heading font-semibold flex items-center gap-2" style="color: #DA7E2D;">
                    <i class="fas fa-history"></i> Últimas 10 Solicitudes
                </h3>
                <div class="flex gap-2">
                    <a href="{{ route('admin.reporte-visitas') }}"
                       class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                       style="background-color: #3B5675;">
                        <i class="fas fa-chart-line"></i> Reporte de Visitas
                    </a>
                    <a href="{{ route('admin.visitantes-activos') }}"
                       class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                       style="background-color: #DA7E2D;">
                        <i class="fas fa-user-check"></i> Ver Visitantes Activos
                    </a>
                </div>
            </div>

            <table class="w-full text-sm text-left border">
                <thead class="text-white" style="background-color: #E26A23;">
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
                    <tr class="border-b"
                        onmouseover="this.style.backgroundColor='#FFF3EC'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        <td class="px-4 py-2 font-mono font-bold" style="color: #DA7E2D;">
                            {{ $s->folio ?? '—' }}
                        </td>
                        <td class="px-4 py-2">{{ $s->solicitante->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($s->fecha_inicio)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $s->tipo->nombre ?? 'N/A' }}</td>
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