<x-app-layout>
    <x-slot name="header">
        Historial de Solicitudes Autorizadas
    </x-slot>

    <div>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-6">

            {{-- Barra superior con regresar --}}
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-heading font-semibold text-omg-slate flex items-center gap-2">
                    <i class="fas fa-history" style="color: #DA7E2D;"></i>
                    Mis Solicitudes Autorizadas
                </h3>
                <a href="{{ route('autorizador.index') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                   style="background-color: #3B5675;">
                    <i class="fas fa-arrow-left"></i> Regresar
                </a>
            </div>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('autorizador.historial') }}" class="mb-6">
                <div class="flex gap-3 items-end flex-wrap">

                    {{-- Filtro por estado --}}
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-filter mr-1"></i> Estado
                        </label>
                        <select name="filtro"
                            class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                            style="width: 180px;">
                            <option value="todas" {{ ($filtro ?? '') == 'todas' ? 'selected' : '' }}>Todas</option>
                            <option value="autorizadas" {{ ($filtro ?? '') == 'autorizadas' ? 'selected' : '' }}>Autorizadas</option>
                            <option value="finalizadas" {{ ($filtro ?? '') == 'finalizadas' ? 'selected' : '' }}>Finalizadas</option>
                            <option value="canceladas" {{ ($filtro ?? '') == 'canceladas' ? 'selected' : '' }}>Canceladas</option>
                            <option value="rechazadas" {{ ($filtro ?? '') == 'rechazadas' ? 'selected' : '' }}>Rechazadas</option>
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
                    <a href="{{ route('autorizador.historial') }}"
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
                                @if($s->estado->nombre == 'Autorizada') bg-green-500
                                @elseif($s->estado->nombre == 'Finalizada') bg-green-700
                                @elseif($s->estado->nombre == 'Cancelada') bg-gray-500
                                @elseif($s->estado->nombre == 'Rechazada') bg-red-500
                                @else bg-blue-500 @endif">
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
                                <p class="text-sm">No hay solicitudes que coincidan con los filtros aplicados</p>
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
