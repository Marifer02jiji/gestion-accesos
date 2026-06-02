<x-app-layout>
    <x-slot name="header">
        Todas las Solicitudes
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg p-6">

        {{-- Barra superior con regresar --}}
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-heading font-semibold text-omg-slate flex items-center gap-2">
                <i class="fas fa-list" style="color: #DA7E2D;"></i>
                Listado Completo de Solicitudes
            </h3>
            <div class="flex gap-2">
                <a href="{{ route('admin.reporte-visitas') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                   style="background-color: #DA7E2D;">
                    <i class="fas fa-chart-line"></i> Visitas
                </a>
                <a href="{{ route('admin.exclusiones') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                   style="background-color: #9ABBC9;">
                    <i class="fas fa-shield-alt"></i> Exclusiones
                </a>
                <a href="{{ route('admin.reportes') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                   style="background-color: #3B5675;">
                    <i class="fas fa-arrow-left"></i> Regresar
                </a>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('admin.todas-solicitudes') }}" class="mb-6">
            <div class="flex gap-3 items-end flex-wrap">

                {{-- Filtro por estado --}}
                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-filter mr-1"></i> Estado
                    </label>
                    <select name="estado"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 180px;">
                        <option value="">Todos</option>
                        <option value="1" {{ ($estado ?? '') == '1' ? 'selected' : '' }}>Pendiente</option>
                        <option value="2" {{ ($estado ?? '') == '2' ? 'selected' : '' }}>Autorizada</option>
                        <option value="3" {{ ($estado ?? '') == '3' ? 'selected' : '' }}>Rechazada</option>
                        <option value="4" {{ ($estado ?? '') == '4' ? 'selected' : '' }}>Cancelada</option>
                        <option value="5" {{ ($estado ?? '') == '5' ? 'selected' : '' }}>En Institución</option>
                        <option value="6" {{ ($estado ?? '') == '6' ? 'selected' : '' }}>En Encuentro</option>
                        <option value="7" {{ ($estado ?? '') == '7' ? 'selected' : '' }}>En Tránsito</option>
                        <option value="8" {{ ($estado ?? '') == '8' ? 'selected' : '' }}>Finalizada</option>
                    </select>
                </div>

                {{-- Filtro Desde --}}
                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-calendar mr-1"></i> Desde
                    </label>
                    <input type="date" name="desde" value="{{ $desde ?? '' }}"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 140px;">
                </div>

                {{-- Filtro Hasta --}}
                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-calendar mr-1"></i> Hasta
                    </label>
                    <input type="date" name="hasta" value="{{ $hasta ?? '' }}"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 140px;">
                </div>

                {{-- Botones --}}
                <button type="submit"
                    class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                    style="background-color: #DA7E2D;">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="{{ route('admin.todas-solicitudes') }}"
                    class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                    style="background-color: #A9AAAD;">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>

        {{-- Estadísticas rápidas --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded px-3 py-2">
                <p class="text-xs font-semibold" style="color: #A9AAAD;">Pendientes</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $solicitudes->total() > 0 ? $solicitudes->where('id_estado_solicitud', 1)->count() : 0 }}</p>
            </div>
            <div class="bg-green-50 border-l-4 border-green-500 rounded px-3 py-2">
                <p class="text-xs font-semibold" style="color: #A9AAAD;">Autorizadas</p>
                <p class="text-2xl font-bold text-green-600">{{ $solicitudes->total() > 0 ? $solicitudes->where('id_estado_solicitud', 2)->count() : 0 }}</p>
            </div>
            <div class="bg-red-50 border-l-4 border-red-500 rounded px-3 py-2">
                <p class="text-xs font-semibold" style="color: #A9AAAD;">Rechazadas</p>
                <p class="text-2xl font-bold text-red-600">{{ $solicitudes->total() > 0 ? $solicitudes->where('id_estado_solicitud', 3)->count() : 0 }}</p>
            </div>
            <div class="bg-gray-50 border-l-4 border-gray-500 rounded px-3 py-2">
                <p class="text-xs font-semibold" style="color: #A9AAAD;">Total</p>
                <p class="text-2xl font-bold" style="color: #DA7E2D;">{{ $solicitudes->total() }}</p>
            </div>
        </div>

        {{-- Tabla --}}
        <table class="w-full text-sm text-left border">
            <thead class="text-white" style="background-color: #E26A23;">
                <tr>
                    <th class="px-4 py-2">Folio</th>
                    <th class="px-4 py-2">Solicitante</th>
                    <th class="px-4 py-2">Fecha de Visita</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Visitantes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($solicitudes as $s)
                <tr class="border-b" style="transition: background 0.2s;"
                    onmouseover="this.style.backgroundColor='#FFF3EC'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <td class="px-4 py-2 font-mono font-bold" style="color: #DA7E2D;">
                        {{ $s->folio ?? $s->id_solicitud }}
                    </td>
                    <td class="px-4 py-2">{{ $s->solicitante->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2">
                        {{ \Carbon\Carbon::parse($s->fecha_inicio)->format('d/m/Y H:i') }}
                    </td>
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
                    <td class="px-4 py-2 text-xs">
                        @foreach($s->visitantes as $v)
                            <p class="text-omg-slate">• {{ $v->nombre }} {{ $v->apellidos }}</p>
                        @endforeach
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center px-4 py-8">
                        <div class="flex flex-col items-center gap-2 text-gray-400">
                            <i class="fas fa-folder-open text-4xl"></i>
                            <p class="font-semibold">No se encontraron registros</p>
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
</x-app-layout>
