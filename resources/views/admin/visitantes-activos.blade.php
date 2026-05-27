<x-app-layout>
    <x-slot name="header">
        Visitantes Activos en Campus
    </x-slot>

    <div class="bg-white shadow-sm rounded-xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-heading font-semibold text-omg-nile flex items-center gap-2">
                <i class="fas fa-user-check"></i> Visitantes dentro del campus
                <span class="bg-omg-coral text-white text-xs font-bold px-2 py-1 rounded-full ml-2">
                    {{ $visitantes->count() }}
                </span>
            </h3>
            <a href="{{ route('admin.reportes') }}"
               class="bg-omg-nile text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>

        <table class="w-full text-sm text-left border">
            <thead class="bg-omg-nile text-white">
                <tr>
                    <th class="px-4 py-2">Visitante</th>
                    <th class="px-4 py-2">Correo</th>
                    <th class="px-4 py-2">Hora Entrada</th>
                    <th class="px-4 py-2">Tiempo en Campus</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitantes as $r)
                <tr class="hover:bg-omg-chardon border-b">
                    <td class="px-4 py-2 font-semibold">
                        {{ $r->qr->solicitudVisitante->visitante->nombre }}
                        {{ $r->qr->solicitudVisitante->visitante->apellidos }}
                    </td>
                    <td class="px-4 py-2">
                        {{ $r->qr->solicitudVisitante->visitante->correo_personal }}
                    </td>
                    <td class="px-4 py-2">{{ $r->hora_llegada_institucion }}</td>
                    <td class="px-4 py-2">
                        @php
                            $entrada = \Carbon\Carbon::parse($r->hora_llegada_institucion);
                            $ahora = \Carbon\Carbon::now();
                            $diff = $entrada->diff($ahora);
                        @endphp
                        <span class="{{ $diff->h >= 2 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                            {{ $diff->h }}h {{ $diff->i }}min
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center px-4 py-8 text-gray-400">
                        <i class="fas fa-check-circle text-3xl mb-2 block"></i>
                        No hay visitantes activos en este momento
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>