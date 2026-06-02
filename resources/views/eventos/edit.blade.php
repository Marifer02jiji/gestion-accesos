<x-app-layout>
    <x-slot name="header">
        Editar Evento
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg p-6">

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li class="flex items-center gap-2">
                            <i class="fas fa-exclamation-circle"></i> {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-omg-chardon border border-omg-kashmir rounded-xl px-5 py-3 mb-6">
            <p class="text-xs text-omg-kashmir font-semibold">Folio del Evento</p>
            <p class="font-heading font-bold text-omg-nile text-xl tracking-widest">{{ $evento->folio }}</p>
        </div>

        <form action="{{ route('eventos.update', $evento->id_evento) }}" method="POST">
            @csrf
            @method('PUT')

            <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                <i class="fas fa-edit"></i> Editar Información del Evento
            </h3>

            <div class="grid grid-cols-2 gap-4 mb-6">

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-user mr-1"></i> Nombre del Responsable
                    </label>
                    <input type="text" name="nombre_responsable" value="{{ old('nombre_responsable', $evento->nombre_responsable) }}"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    @error('nombre_responsable')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-envelope mr-1"></i> Correo del Responsable
                    </label>
                    <input type="email" name="correo_responsable" value="{{ old('correo_responsable', $evento->correo_responsable) }}"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    @error('correo_responsable')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-users mr-1"></i> Número de Personas (Máximo: 1000)
                    </label>
                    <input type="number" name="numero_personas" value="{{ old('numero_personas', $evento->numero_personas) }}"
                        min="1" max="1000"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    <p class="text-xs text-omg-kashmir mt-1">Cantidad actual: <strong>{{ $evento->numero_personas }}</strong></p>
                    @error('numero_personas')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1 text-gray-500">
                        <i class="fas fa-calendar mr-1"></i> Fecha del Evento
                    </label>
                    <input type="text" value="{{ \Carbon\Carbon::parse($evento->fecha_evento)->format('d/m/Y H:i') }}"
                        disabled
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-600">
                    <p class="text-xs italic text-gray-500 mt-1">No se puede editar la fecha del evento</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1 text-gray-500">
                        <i class="fas fa-list mr-1"></i> Tipo de Evento
                    </label>
                    <input type="text" value="{{ $evento->tipo_evento }}"
                        disabled
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-600">
                    <p class="text-xs italic text-gray-500 mt-1">No se puede editar el tipo de evento</p>
                </div>

            </div>

            <div class="flex justify-end gap-4 mt-6 pt-4 border-t border-omg-chardon">
                <a href="{{ route('eventos.show', $evento->id_evento) }}"
                   class="text-white px-4 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                   style="background-color: #3B5675;">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit"
                    class="text-white px-6 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                    style="background-color: #DA7E2D;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
