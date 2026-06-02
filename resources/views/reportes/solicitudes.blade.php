<x-app-layout>
    <x-slot name="header">
        {{ $tituloPagina }}
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg p-6">

        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-heading font-semibold text-omg-slate flex items-center gap-2">
                <i class="fas {{ $esAdmin ? 'fa-chart-bar' : 'fa-check-circle' }}" style="color: #DA7E2D;"></i>
                {{ $tituloPagina }}
            </h3>
            <a href="{{ $rutaRegresar }}"
               class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
               style="background-color: #3B5675;">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>

        <form method="GET" action="{{ $rutaReportes }}" class="mb-6">
            <div class="flex gap-3 items-end flex-wrap">
                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-filter mr-1"></i> Estado
                    </label>
                    <select name="estado"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 180px;">
                        <option value="">Todos</option>
                        <option value="1" {{ ($filtros['estado'] ?? '') == '1' ? 'selected' : '' }}>Pendiente</option>
                        <option value="2" {{ ($filtros['estado'] ?? '') == '2' ? 'selected' : '' }}>Autorizada</option>
                        <option value="3" {{ ($filtros['estado'] ?? '') == '3' ? 'selected' : '' }}>Rechazada</option>
                        <option value="4" {{ ($filtros['estado'] ?? '') == '4' ? 'selected' : '' }}>Cancelada</option>
                        <option value="5" {{ ($filtros['estado'] ?? '') == '5' ? 'selected' : '' }}>En Institución</option>
                        <option value="6" {{ ($filtros['estado'] ?? '') == '6' ? 'selected' : '' }}>En Encuentro</option>
                        <option value="7" {{ ($filtros['estado'] ?? '') == '7' ? 'selected' : '' }}>En Tránsito</option>
                        <option value="8" {{ ($filtros['estado'] ?? '') == '8' ? 'selected' : '' }}>Finalizada</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-user mr-1"></i> Solicitante
                    </label>
                    <input type="text" name="solicitante" value="{{ $filtros['solicitante'] ?? '' }}"
                        placeholder="Nombre del solicitante"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 180px;">
                </div>

                @if($esAdmin)
                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-user-check mr-1"></i> Autorizador
                    </label>
                    <input type="text" name="autorizador" value="{{ $filtros['autorizador'] ?? '' }}"
                        placeholder="Nombre del autorizador"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 180px;">
                </div>
                @endif

                @if($esVistaAutorizador ?? false)
                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-envelope mr-1"></i> Visita (correo)
                    </label>
                    <input type="text" name="correo" value="{{ $filtros['correo'] ?? '' }}"
                        placeholder="Correo del visitante"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 200px;">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-calendar mr-1"></i> Fecha
                    </label>
                    <input type="date" name="fecha" value="{{ $filtros['fecha'] ?? '' }}"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 140px;">
                </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-tag mr-1"></i> Tipo
                    </label>
                    <select name="tipo"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 160px;">
                        <option value="">Todos</option>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->id_tipo_solicitud }}"
                                {{ ($filtros['tipo'] ?? '') == $tipo->id_tipo_solicitud ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-calendar-alt mr-1"></i> Desde
                    </label>
                    <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 140px;">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-omg-slate mb-1">
                        <i class="fas fa-calendar-alt mr-1"></i> Hasta
                    </label>
                    <input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}"
                        class="border border-omg-kashmir rounded px-3 py-1 bg-omg-chardon text-sm focus:outline-none"
                        style="width: 140px;">
                </div>

                <button type="submit"
                    class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                    style="background-color: #DA7E2D;">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="{{ $rutaReportes }}"
                    class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                    style="background-color: #A9AAAD;">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>

        @if($esAdmin)
        <p class="text-xs text-omg-slate mb-4">
            <i class="fas fa-info-circle mr-1"></i> Mostrando solicitudes de todos los usuarios del sistema.
        </p>
        @else
        <p class="text-xs text-omg-slate mb-4">
            <i class="fas fa-info-circle mr-1"></i> Mostrando solo las solicitudes que usted ha autorizado.
        </p>
        @endif

        @if($esAdmin)
        <table class="w-full text-sm text-left border">
            <thead class="text-white" style="background-color: #E26A23;">
                <tr>
                    <th class="px-4 py-2">Folio</th>
                    <th class="px-4 py-2">Visitante</th>
                    <th class="px-4 py-2">Solicitante</th>
                    <th class="px-4 py-2">Autorizador</th>
                    <th class="px-4 py-2">Fecha de Visita</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">Estado</th>
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
                    <td class="px-4 py-2 text-xs">
                        @foreach($s->visitantes as $v)
                            <p class="font-semibold text-omg-slate">{{ $v->nombre }} {{ $v->apellidos }}</p>
                        @endforeach
                    </td>
                    <td class="px-4 py-2">{{ $s->solicitante->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2">
                        @php $infoAutorizador = $s->infoAutorizador(); @endphp
                        <span>{{ $infoAutorizador['texto'] }}</span>
                        @if($infoAutorizador['motivo'])
                            <span class="block text-xs text-orange-600 mt-0.5" title="{{ $infoAutorizador['motivo'] }}">
                                <i class="fas fa-info-circle"></i> {{ $infoAutorizador['motivo'] }}
                            </span>
                        @endif
                    </td>
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
                        @if($s->solicitanteNoMarcoEncuentro())
                            <span class="block text-xs text-orange-600 font-semibold mt-1">
                                <i class="fas fa-exclamation-triangle"></i> Encuentro no marcado por solicitante
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center px-4 py-8">
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
        @else
        <table class="w-full text-sm text-left border">
            <thead class="text-white" style="background-color: #E26A23;">
                <tr>
                    <th class="px-4 py-2">Folio</th>
                    <th class="px-4 py-2">Visitante</th>
                    <th class="px-4 py-2">Correo visitante</th>
                    <th class="px-4 py-2">Solicitante</th>
                    <th class="px-4 py-2">Fecha de Visita</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">Estado</th>
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
                    <td class="px-4 py-2">
                        @foreach($s->visitantes as $v)
                            <p class="font-semibold text-omg-slate">{{ $v->nombre }} {{ $v->apellidos }}</p>
                        @endforeach
                    </td>
                    <td class="px-4 py-2 text-xs">
                        @foreach($s->visitantes as $v)
                            <p class="text-gray-500">{{ $v->correo_personal }}</p>
                        @endforeach
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
                        @if($s->solicitanteNoMarcoEncuentro())
                            <span class="block text-xs text-orange-600 font-semibold mt-1">
                                <i class="fas fa-exclamation-triangle"></i> Encuentro no marcado por solicitante
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center px-4 py-8">
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
        @endif

        <div class="mt-4">
            {{ $solicitudes->links() }}
        </div>
    </div>
</x-app-layout>
