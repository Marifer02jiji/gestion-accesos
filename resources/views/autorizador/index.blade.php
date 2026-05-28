<x-app-layout>
    <x-slot name="header">
        Solicitudes
    </x-slot>

    <div>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-6">

            {{-- Filtros --}}
            <div class="flex items-center gap-3 mb-6 flex-wrap">
                <span class="font-heading font-semibold text-omg-slate text-sm flex items-center gap-1">
                    <i class="fas fa-filter"></i> Filtrar:
                </span>
                <a href="{{ route('autorizador.index', ['filtro' => 'todos']) }}"
                   class="px-4 py-1 rounded-full text-sm font-semibold transition"
                   style="{{ $filtro == 'todos' ? 'background-color:#DA7E2D; color:white;' : 'border:1px solid #A9AAAD; color:#DA7E2D;' }}">
                    <i class="fas fa-list mr-1"></i> Todos
                </a>
                <a href="{{ route('autorizador.index', ['filtro' => 'pendientes']) }}"
                   class="px-4 py-1 rounded-full text-sm font-semibold transition
                   {{ $filtro == 'pendientes' ? 'bg-yellow-500 text-white' : 'border border-gray-300 text-omg-slate hover:bg-orange-50' }}">
                    <i class="fas fa-clock mr-1"></i> Pendientes
                </a>
                <a href="{{ route('autorizador.index', ['filtro' => 'aprobadas']) }}"
                   class="px-4 py-1 rounded-full text-sm font-semibold transition
                   {{ $filtro == 'aprobadas' ? 'bg-green-500 text-white' : 'border border-gray-300 text-omg-slate hover:bg-orange-50' }}">
                    <i class="fas fa-check-circle mr-1"></i> Aprobadas
                </a>
                <a href="{{ route('autorizador.index', ['filtro' => 'rechazadas']) }}"
                   class="px-4 py-1 rounded-full text-sm font-semibold transition
                   {{ $filtro == 'rechazadas' ? 'bg-red-500 text-white' : 'border border-gray-300 text-omg-slate hover:bg-orange-50' }}">
                    <i class="fas fa-times-circle mr-1"></i> Rechazadas
                </a>
            </div>

            @forelse($solicitudes as $s)
            <div class="border rounded-xl p-4 mb-4 transition hover:shadow-md"
                 style="border-color: #A9AAAD; background-color: #FFF3EC;">
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
                        <p class="font-semibold text-omg-slate">{{ $s->fecha_inicio }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold flex items-center gap-1" style="color: #A9AAAD;">
                            <i class="fas fa-info-circle"></i> Estado
                        </p>
                        <span class="px-2 py-1 rounded-full text-white text-xs font-semibold
                            @if($s->estado->nombre == 'Pendiente') bg-yellow-500
                            @elseif($s->estado->nombre == 'Autorizada') bg-green-500
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

                @if($s->estado->nombre == 'Pendiente')
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
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                <div class="flex flex-col items-center gap-2">
                    <i class="fas fa-clipboard-check text-4xl"></i>
                    <p class="font-semibold">No se encontraron registros</p>
                    <p class="text-sm">No hay solicitudes en esta categoría</p>
                </div>
            </div>
            @endforelse

            <div class="mt-4">
                {{ $solicitudes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>