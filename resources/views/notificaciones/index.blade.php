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
            <form action="{{ route('notificaciones.todas-leidas') }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-omg-nile text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm">
                    <i class="fas fa-check-double"></i> Marcar todas como leídas
                </button>
            </form>
        </div>

        {{-- Lista de notificaciones --}}
        @forelse($notificaciones as $n)
        <div class="border rounded-xl p-4 mb-3 flex items-start gap-4 transition
            {{ $n->leida ? 'bg-white border-gray-200' : 'bg-omg-chardon border-omg-kashmir' }}">

            {{-- Ícono según tipo --}}
            <div class="p-3 rounded-full flex-shrink-0
                {{ $n->leida ? 'bg-gray-100' : 'bg-omg-nile' }}">
                @if($n->tipo == 'autorizada')
                    <i class="fas fa-check-circle {{ $n->leida ? 'text-gray-400' : 'text-white' }} text-lg"></i>
                @elseif($n->tipo == 'rechazada')
                    <i class="fas fa-times-circle {{ $n->leida ? 'text-gray-400' : 'text-white' }} text-lg"></i>
                @elseif($n->tipo == 'entrada')
                    <i class="fas fa-sign-in-alt {{ $n->leida ? 'text-gray-400' : 'text-white' }} text-lg"></i>
                @elseif($n->tipo == 'salida')
                    <i class="fas fa-sign-out-alt {{ $n->leida ? 'text-gray-400' : 'text-white' }} text-lg"></i>
                @else
                    <i class="fas fa-bell {{ $n->leida ? 'text-gray-400' : 'text-white' }} text-lg"></i>
                @endif
            </div>

            {{-- Contenido --}}
            <div class="flex-1">
                <p class="font-semibold text-omg-slate text-sm">{{ $n->mensaje }}</p>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                    <i class="fas fa-clock"></i> {{ $n->fecha_creado }}
                </p>
            </div>

            {{-- Acción --}}
            @if(!$n->leida)
            <form action="{{ route('notificaciones.leida', $n->id_notificaciones) }}" method="POST">
                @csrf
                <button type="submit"
                    class="text-omg-nile hover:text-omg-coral text-xs font-semibold flex items-center gap-1">
                    <i class="fas fa-eye"></i> Marcar leída
                </button>
            </form>
            @else
            <span class="text-xs text-gray-400 flex items-center gap-1">
                <i class="fas fa-check"></i> Leída
            </span>
            @endif
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