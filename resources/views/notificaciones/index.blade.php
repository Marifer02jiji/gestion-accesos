<x-app-layout>
    <x-slot name="header">
        Notificaciones
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg p-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Barra superior --}}
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-heading font-semibold text-omg-slate flex items-center gap-2">
                <i class="fas fa-bell text-omg-nile"></i>
                Mis Notificaciones
            </h3>
            <div class="flex gap-2">
                <form action="{{ route('notificaciones.todas-leidas') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="text-white px-3 py-1 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                        style="background-color: #3B5675;">
                        <i class="fas fa-check-double"></i> Marcar todas leídas
                    </button>
                </form>
                <form action="{{ route('notificaciones.eliminarTodas') }}" method="POST"
                    onsubmit="return confirm('¿Eliminar todas las notificaciones?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="text-white px-3 py-1 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                        style="background-color: #E26A23;">
                        <i class="fas fa-trash"></i> Eliminar todas
                    </button>
                </form>
            </div>
        </div>

        {{-- Lista de notificaciones --}}
        @forelse($notificaciones as $n)
        <div class="border rounded-xl p-4 mb-3 flex items-start gap-4 transition
            {{ $n->leida ? 'bg-white border-gray-200' : 'bg-omg-chardon border-omg-kashmir' }}">

            {{-- Ícono según tipo --}}
            <div class="p-3 rounded-full flex-shrink-0"
                style="background-color: {{ $n->leida ? '#f3f4f6' : '#3B5675' }}">
                @if($n->tipo == 'autorizada')
                    <i class="fas fa-check-circle text-lg" style="color: {{ $n->leida ? '#9ca3af' : 'white' }}"></i>
                @elseif($n->tipo == 'rechazada')
                    <i class="fas fa-times-circle text-lg" style="color: {{ $n->leida ? '#9ca3af' : 'white' }}"></i>
                @elseif($n->tipo == 'entrada')
                    <i class="fas fa-sign-in-alt text-lg" style="color: {{ $n->leida ? '#9ca3af' : 'white' }}"></i>
                @elseif($n->tipo == 'salida')
                    <i class="fas fa-sign-out-alt text-lg" style="color: {{ $n->leida ? '#9ca3af' : 'white' }}"></i>
                @elseif($n->tipo == 'pendiente')
                    <i class="fas fa-clock text-lg" style="color: {{ $n->leida ? '#9ca3af' : 'white' }}"></i>
                @elseif($n->tipo == 'tolerancia_vencida')
                    <i class="fas fa-exclamation-triangle text-lg" style="color: {{ $n->leida ? '#9ca3af' : 'white' }}"></i>
                @elseif($n->tipo == 'cierre_institucion')
                    <i class="fas fa-door-closed text-lg" style="color: {{ $n->leida ? '#9ca3af' : 'white' }}"></i>
                @else
                    <i class="fas fa-bell text-lg" style="color: {{ $n->leida ? '#9ca3af' : 'white' }}"></i>
                @endif
            </div>

            {{-- Contenido --}}
            <div class="flex-1">
                <p class="font-semibold text-omg-slate text-sm">{{ $n->mensaje }}</p>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                    <i class="fas fa-clock"></i>
                    {{ \Carbon\Carbon::parse($n->fecha_creado)->format('d/m/Y H:i') }}
                </p>

                {{-- Link a la solicitud --}}
                @if($n->id_solicitud)
                    @php
                        $url = match($n->tipo) {
                            'pendiente'          => route('autorizador.index'),
                            default              => route('solicitudes.show', $n->id_solicitud),
                        };
                        $label = match($n->tipo) {
                            'pendiente'          => 'Ver solicitudes pendientes',
                            'autorizada'         => 'Ver mi solicitud autorizada',
                            'rechazada'          => 'Ver mi solicitud rechazada',
                            'entrada'            => 'Ver detalle de la visita',
                            'salida'             => 'Ver detalle de la visita',
                            'encuentro'          => 'Ver detalle de la visita',
                            'tolerancia_vencida' => 'Ver solicitud vencida',
                            'cierre_institucion' => 'Ver solicitud',
                            default              => 'Ver solicitud',
                        };
                    @endphp
                    <a href="{{ $url }}"
                        class="inline-flex items-center gap-1 mt-2 text-xs font-semibold hover:opacity-80"
                        style="color: #DA7E2D;">
                        <i class="fas fa-arrow-right"></i> {{ $label }}
                    </a>
                @endif
            </div>

            {{-- Acciones --}}
            <div class="flex flex-col gap-2 items-end">
                @if(!$n->leida)
                <form action="{{ route('notificaciones.leida', $n->id_notificaciones) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="text-xs font-semibold flex items-center gap-1 hover:opacity-80"
                        style="color: #3B5675;">
                        <i class="fas fa-eye"></i> Marcar leída
                    </button>
                </form>
                @else
                <span class="text-xs text-gray-400 flex items-center gap-1">
                    <i class="fas fa-check"></i> Leída
                </span>
                @endif

                <form action="{{ route('notificaciones.eliminar', $n->id_notificaciones) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar esta notificación?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="text-xs font-semibold flex items-center gap-1 hover:opacity-80 text-red-500">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-gray-400">
            <div class="flex flex-col items-center gap-2">
                <i class="fas fa-bell-slash text-4xl"></i>
                <p class="font-semibold">No se encontraron registros</p>
                <p class="text-sm">No tienes notificaciones por el momento</p>
            </div>
        </div>
        @endforelse

        {{-- Paginación --}}
        <div class="mt-4">
            {{ $notificaciones->links() }}
        </div>

    </div>
</x-app-layout>