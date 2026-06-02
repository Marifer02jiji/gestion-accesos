<x-app-layout>
    <x-slot name="header">
        Lista de Exclusión
    </x-slot>

    <div class="space-y-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Encabezado con botón regresar --}}
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-omg-slate flex items-center gap-2">
                <i class="fas fa-shield-alt" style="color: #DA7E2D;"></i> Gestión de Lista de Exclusión
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('dashboard') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                   style="background-color: #3B5675;">
                    <i class="fas fa-arrow-left"></i> Regresar
                </a>
            </div>
        </div>

        {{-- Agregar --}}
        <div class="bg-white shadow-sm rounded-xl p-6">
            <h3 class="text-lg font-heading font-semibold text-white mb-4 px-3 py-2 rounded-lg flex items-center gap-2"
                style="background-color: #E26A23;">
                <i class="fas fa-user-slash"></i> Agregar a Lista de Exclusión
            </h3>
            <form action="{{ route('admin.exclusiones.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-omg-slate mb-1">
                            <i class="fas fa-user mr-1"></i> Visitante
                        </label>
                        <select name="id_visitante"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2"
                            style="border-color: #A9AAAD; background-color: #FFF3EC;">
                            <option value="">Seleccione un visitante</option>
                            @foreach($visitantes as $v)
                                <option value="{{ $v->id_visitante }}">
                                    {{ $v->nombre }} {{ $v->apellidos }} — {{ $v->correo_personal }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_visitante')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-omg-slate mb-1">
                            <i class="fas fa-comment mr-1"></i> Motivo
                        </label>
                        <textarea name="motivo_exclusion" rows="2"
                            placeholder="Mínimo 10 caracteres"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2"
                            style="border-color: #A9AAAD; background-color: #FFF3EC;"></textarea>
                        @error('motivo_exclusion')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-red-600 text-white px-3 py-1 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                        <i class="fas fa-ban"></i> Agregar a Lista
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabla con filtros --}}
        <div class="bg-white shadow-sm rounded-xl p-6">
            <h3 class="text-lg font-heading font-semibold text-white mb-4 px-3 py-2 rounded-lg flex items-center gap-2"
                style="background-color: #E26A23;">
                <i class="fas fa-list"></i> Visitantes Bloqueados
            </h3>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('admin.exclusiones') }}" class="mb-4">
                <p class="text-xs mb-2 flex items-center gap-1" style="color: #A9AAAD;">
                    <i class="fas fa-info-circle"></i> Puedes filtrar por nombre, correo, motivo o rango de fechas — usa uno o combínalos.
                </p>
                <div class="flex gap-3 items-end flex-wrap">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-search mr-1"></i> Nombre, correo o motivo
                        </label>
                        <input type="text" name="buscar" value="{{ $buscar ?? '' }}"
                            placeholder="Buscar..."
                            class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-calendar mr-1"></i> Desde
                        </label>
                        <input type="date" name="desde" value="{{ $desde ?? '' }}"
                            class="border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-omg-slate mb-1">
                            <i class="fas fa-calendar mr-1"></i> Hasta
                        </label>
                        <input type="date" name="hasta" value="{{ $hasta ?? '' }}"
                            class="border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon text-sm focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                            style="background-color: #DA7E2D;">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.exclusiones') }}"
                            class="text-white px-3 py-1 rounded-lg hover:opacity-90 text-sm font-semibold flex items-center gap-1"
                            style="background-color: #A9AAAD;">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <table class="w-full text-sm text-left border">
                <thead class="text-white" style="background-color: #E26A23;">
                    <tr>
                        <th class="px-4 py-2">Visitante</th>
                        <th class="px-4 py-2">Correo</th>
                        <th class="px-4 py-2">Motivo</th>
                        <th class="px-4 py-2">Fecha Bloqueo</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exclusiones as $e)
                    <tr class="border-b"
                        onmouseover="this.style.backgroundColor='#FFF3EC'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        <td class="px-4 py-2 font-semibold">{{ $e->visitante->nombre }} {{ $e->visitante->apellidos }}</td>
                        <td class="px-4 py-2">{{ $e->visitante->correo_personal }}</td>
                        <td class="px-4 py-2">{{ $e->motivo_exclusion }}</td>
                        <td class="px-4 py-2">
                            {{ \Carbon\Carbon::parse($e->fecha_bloqueo)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-2">
                            <form action="{{ route('admin.exclusiones.destroy', $e->id_lista_exclusion) }}"
                                method="POST"
                                onsubmit="return confirm('¿Desea eliminar esta entrada de la lista de exclusión?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-600 text-white px-3 py-1 rounded hover:opacity-90 text-xs flex items-center gap-1">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center px-4 py-8 text-gray-400">
                            <i class="fas fa-check-circle text-3xl mb-2 block"></i>
                            No hay visitantes en la lista de exclusión
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $exclusiones->links() }}</div>
        </div>
    </div>
</x-app-layout>