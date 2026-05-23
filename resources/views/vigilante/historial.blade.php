<x-app-layout>
    <x-slot name="header">
        Historial de Accesos
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg p-6">

        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-heading font-semibold text-omg-slate flex items-center gap-2">
                <i class="fas fa-history text-omg-nile"></i>
                Registro de Entradas y Salidas
            </h3>
            <a href="{{ route('vigilante.index') }}"
               class="bg-omg-nile text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>

        <table class="w-full text-sm text-left border">
            <thead class="bg-omg-nile text-white">
                <tr>
                    <th class="px-4 py-2"><i class="fas fa-user mr-1"></i> Visitante</th>
                    <th class="px-4 py-2"><i class="fas fa-sign-in-alt mr-1"></i> Entrada</th>
                    <th class="px-4 py-2"><i class="fas fa-sign-out-alt mr-1"></i> Salida</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $r)
                <tr class="hover:bg-omg-chardon border-b">
                    <td class="px-4 py-2">
                        {{ $r->qr->solicitudVisitante->visitante->nombre }}
                        {{ $r->qr->solicitudVisitante->visitante->apellidos }}
                    </td>
                    <td class="px-4 py-2">{{ $r->hora_llegada_institucion }}</td>
                    <td class="px-4 py-2">
                        @if($r->hora_salida_institucion)
                            {{ $r->hora_salida_institucion }}
                        @else
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-circle text-xs"></i> Dentro
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center px-4 py-8">
                        <div class="flex flex-col items-center gap-2 text-gray-400">
                            <i class="fas fa-history text-4xl"></i>
                            <p class="font-semibold">No se encontraron registros</p>
                            <p class="text-sm">Aún no hay entradas o salidas registradas</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $registros->links() }}
        </div>

    </div>
</x-app-layout>