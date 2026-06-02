<x-app-layout>
    <x-slot name="header">
        Citas por Consulta
    </x-slot>

    <div class="space-y-4">
        <div class="bg-white shadow-sm rounded-xl p-6">

            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-heading font-semibold flex items-center gap-2" style="color: #DA7E2D;">
                    <i class="fas fa-stethoscope"></i> Consultas Finalizadas
                </h3>
                <div class="flex gap-2">
                    <a href="{{ route('admin.todas-solicitudes') }}"
                       class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                       style="background-color: #DA7E2D;">
                        <i class="fas fa-list-check"></i> Todas Solicitudes
                    </a>
                    <a href="{{ route('admin.reporte-visitas') }}"
                       class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                       style="background-color: #3B5675;">
                        <i class="fas fa-arrow-left"></i> Regresar a Visitas
                    </a>
                </div>
            </div>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('admin.citas-consulta') }}" class="mb-6">
                <p class="text-xs mb-2 flex items-center gap-1" style="color: #A9AAAD;">
                    <i class="fas fa-info-circle"></i> Citas por consulta registradas desde Flutter. Filtra por folio, nombre del visitante o anfitrión.
                </p>
                <div class="flex gap-2 items-end flex-wrap">
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-search mr-1"></i> Folio, visitante o anfitrión
                        </label>
                        <input type="text" name="buscar" value="{{ $buscar ?? '' }}"
                            placeholder="Buscar..."
                            style="width:220px;"
                            class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-calendar mr-1"></i> Desde
                        </label>
                        <input type="date" name="desde" value="{{ $desde ?? '' }}"
                            style="width:150px;"
                            class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-calendar mr-1"></i> Hasta
                        </label>
                        <input type="date" name="hasta" value="{{ $hasta ?? '' }}"
                            style="width:150px;"
                            class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <button type="submit"
                        class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                        style="background-color: #DA7E2D;">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.citas-consulta') }}"
                        class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                        style="background-color: #A9AAAD;">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>

            {{-- Tabla --}}
            <table class="w-full text-sm text-left border">
                <thead class="text-white" style="background-color: #E26A23;">
                    <tr>
                        <th class="px-4 py-2">Folio</th>
                        <th class="px-4 py-2">Visitante</th>
                        <th class="px-4 py-2">Anfitrión</th>
                        <th class="px-4 py-2">Fecha</th>
                        <th class="px-4 py-2">Tipo</th>
                        <th class="px-4 py-2">QR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $s)
                    <tr class="border-b" style="transition: background 0.2s;"
                        onmouseover="this.style.backgroundColor='#FFF3EC'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        <td class="px-4 py-2 font-mono font-bold" style="color: #DA7E2D;">
                            {{ $s->folio ?? '—' }}
                        </td>
                        <td class="px-4 py-2">
                            @foreach($s->visitantes as $v)
                                <div class="text-xs">{{ $v->nombre }} {{ $v->apellidos }}</div>
                            @endforeach
                        </td>
                        <td class="px-4 py-2">{{ $s->solicitante->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-xs">{{ \Carbon\Carbon::parse($s->fecha_inicio)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded-full text-white text-xs font-semibold bg-purple-500">
                                {{ $s->tipo->nombre ?? 'Consulta' }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            @forelse($s->solicitudVisitantes as $sv)
                                @if($sv->qr)
                                    <span class="px-2 py-1 rounded-full text-white text-xs font-semibold bg-green-500">
                                        ✓ {{ $sv->qr->codigo ?? 'Generado' }}
                                    </span>
                                @endif
                            @empty
                                <span style="color: #A9AAAD;" class="text-xs">Sin QR</span>
                            @endforelse
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center px-4 py-8">
                            <div class="flex flex-col items-center gap-2 text-gray-400">
                                <i class="fas fa-folder-open text-3xl mb-2"></i>
                                <p class="font-semibold">No se encontraron citas por consulta</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
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
        </div>
    </div>
</x-app-layout>
