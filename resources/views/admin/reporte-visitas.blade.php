<x-app-layout>
    <x-slot name="header">
        Reporte de Visitas
    </x-slot>

    <div class="space-y-4">
        <div class="bg-white shadow-sm rounded-xl p-6">

            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-heading font-semibold flex items-center gap-2" style="color: #DA7E2D;">
                    <i class="fas fa-chart-line"></i> Visitas Finalizadas
                </h3>
                <div class="flex gap-2">
                    <a href="{{ route('admin.citas-consulta') }}"
                       class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                       style="background-color: #DA7E2D;">
                        <i class="fas fa-stethoscope"></i> Ver Citas Consulta
                    </a>
                    <a href="{{ route('admin.reportes') }}"
                       class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                       style="background-color: #3B5675;">
                        <i class="fas fa-arrow-left"></i> Regresar
                    </a>
                </div>
            </div>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('admin.reporte-visitas') }}" class="mb-6">
                <p class="text-xs mb-2 flex items-center gap-1" style="color: #A9AAAD;">
                    <i class="fas fa-info-circle"></i> Filtra por folio, nombre del visitante o anfitrión, y rango de fechas.
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
                    <a href="{{ route('admin.reporte-visitas') }}"
                        class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                        style="background-color: #A9AAAD;">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>

            {{-- Tarjetas por visita --}}
            @forelse($solicitudes as $s)
            @php
                $sv        = $s->solicitudVisitantes->first();
                $qr        = $sv?->qr;
                $registros = $qr ? \App\Models\RegistroAcceso::where('id_qr', $qr->id_qr)
                    ->orderBy('hora_llegada_institucion', 'asc')
                    ->get() : collect();
                $registro  = $registros->first();
                $entrada   = $registro?->hora_llegada_institucion ? \Carbon\Carbon::parse($registro->hora_llegada_institucion) : null;
                $salida    = $registro?->hora_salida_institucion ? \Carbon\Carbon::parse($registro->hora_salida_institucion) : null;
                $duracion  = ($entrada && $salida) ? $entrada->diff($salida) : null;
            @endphp

            <div class="border rounded-xl p-5 mb-4" style="border-color: #A9AAAD;">

                {{-- Encabezado --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-sm" style="color: #DA7E2D;">
                            {{ str_replace('VIS-', '', $s->folio) }}
                        </span>
                        <span class="px-2 py-1 rounded-full text-white text-xs font-semibold bg-green-700">
                            Finalizada
                        </span>
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                            {{ $s->tipo->nombre ?? '—' }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($s->fecha_inicio)->format('d/m/Y H:i') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    {{-- Visitante(s) --}}
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-users mr-1"></i> Visitante(s)
                        </p>
                        @foreach($s->visitantes as $v)
                            <p class="font-semibold text-omg-slate text-sm">{{ $v->nombre }} {{ $v->apellidos }}</p>
                            <p class="text-xs text-gray-400">{{ $v->correo_personal }}</p>
                        @endforeach
                    </div>

                    {{-- Anfitrión --}}
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-user-tie mr-1"></i> Anfitrión
                        </p>
                        <p class="font-semibold text-omg-slate text-sm">{{ $s->solicitante->name ?? '—' }}</p>
                    </div>

                    {{-- Lugar --}}
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-map-marker-alt mr-1"></i> Lugar
                        </p>
                        <p class="font-semibold text-omg-slate text-sm">{{ $s->lugar_encuentro }}</p>
                    </div>

                    {{-- Tiempo total --}}
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-clock mr-1"></i> Tiempo en institución
                        </p>
                        @if($duracion)
                            <p class="font-bold text-sm" style="color: #DA7E2D;">
                                {{ $duracion->h }}h {{ $duracion->i }}min
                            </p>
                        @else
                            <p class="text-gray-400 text-sm">—</p>
                        @endif
                    </div>
                </div>

                {{-- Timeline de movimientos --}}
                <div class="border-t pt-3" style="border-color: #f0d8c8;">
                    <p class="text-xs font-semibold mb-3" style="color: #A9AAAD;">
                        <i class="fas fa-route mr-1"></i> Timeline de la visita
                    </p>
                    <div class="flex flex-wrap gap-3 items-center">

                        @if($entrada)
                        <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                            <i class="fas fa-sign-in-alt text-blue-500 text-sm"></i>
                            <div>
                                <p class="text-xs font-semibold text-blue-600">Entró a institución</p>
                                <p class="text-xs text-gray-500">{{ $entrada->format('H:i') }}</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        @endif

                        @if($s->hora_llegada_encuentro)
                        <div class="flex items-center gap-2 bg-purple-50 border border-purple-200 rounded-lg px-3 py-2">
                            <i class="fas fa-handshake text-purple-500 text-sm"></i>
                            <div>
                                <p class="text-xs font-semibold text-purple-600">Llegó al encuentro</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($s->hora_llegada_encuentro)->format('H:i') }}</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        @endif

                        @if($s->hora_salida_encuentro)
                        <div class="flex items-center gap-2 bg-orange-50 border border-orange-200 rounded-lg px-3 py-2">
                            <i class="fas fa-door-open text-orange-500 text-sm"></i>
                            <div>
                                <p class="text-xs font-semibold text-orange-600">Salió del encuentro</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($s->hora_salida_encuentro)->format('H:i') }}</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        @endif

                        @if($salida)
                        <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                            <i class="fas fa-sign-out-alt text-green-600 text-sm"></i>
                            <div>
                                <p class="text-xs font-semibold text-green-700">Salió de institución</p>
                                <p class="text-xs text-gray-500">{{ $salida->format('H:i') }}</p>
                            </div>
                        </div>
                        @endif

                        @if(!$entrada && !$s->hora_llegada_encuentro && !$salida)
                        <p class="text-xs text-gray-400">Sin registros de movimiento</p>
                        @endif
                    </div>

                    {{-- Datos del vigilante --}}
                    @if($registro && ($registro->telefono_vigilante_entrada || $registro->caseta_entrada))
                    <div class="mt-3 flex gap-4 flex-wrap">
                        @if($registro->telefono_vigilante_entrada)
                        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                            <i class="fas fa-phone text-gray-400 text-xs"></i>
                            <div>
                                <p class="text-xs font-semibold text-gray-600">Tel. Vigilante (entrada)</p>
                                <p class="text-xs text-gray-500">{{ $registro->telefono_vigilante_entrada }}</p>
                            </div>
                        </div>
                        @endif
                        @if($registro->caseta_entrada)
                        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                            <i class="fas fa-map-marker-alt text-gray-400 text-xs"></i>
                            <div>
                                <p class="text-xs font-semibold text-gray-600">Área de acceso (entrada)</p>
                                <p class="text-xs text-gray-500">{{ $registro->caseta_entrada }}</p>
                            </div>
                        </div>
                        @endif
                        @if($registro->telefono_vigilante_salida)
                        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                            <i class="fas fa-phone text-gray-400 text-xs"></i>
                            <div>
                                <p class="text-xs font-semibold text-gray-600">Tel. Vigilante (salida)</p>
                                <p class="text-xs text-gray-500">{{ $registro->telefono_vigilante_salida }}</p>
                            </div>
                        </div>
                        @endif
                        @if($registro->caseta_salida)
                        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                            <i class="fas fa-map-marker-alt text-gray-400 text-xs"></i>
                            <div>
                                <p class="text-xs font-semibold text-gray-600">Área de acceso (salida)</p>
                                <p class="text-xs text-gray-500">{{ $registro->caseta_salida }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-folder-open text-4xl mb-2 block"></i>
                <p class="font-semibold">No hay visitas finalizadas</p>
            </div>
            @endforelse

            <div class="mt-4">{{ $solicitudes->links() }}</div>
        </div>
    </div>
</x-app-layout>