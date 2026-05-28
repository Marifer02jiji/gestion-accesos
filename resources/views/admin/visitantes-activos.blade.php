<x-app-layout>
    <x-slot name="header">
        Visitantes Activos en Campus
    </x-slot>

    <div class="bg-white shadow-sm rounded-xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-heading font-semibold flex items-center gap-2" style="color: #DA7E2D;">
                <i class="fas fa-user-check"></i> Visitantes dentro del campus
                <span class="text-white text-xs font-bold px-2 py-1 rounded-full ml-2"
                      style="background-color: #DA7E2D;">
                    {{ $visitantes->count() }}
                </span>
            </h3>
            <a href="{{ route('admin.reportes') }}"
               class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
               style="background-color: #DA7E2D;">
                <i class="fas fa-arrow-left"></i> Regresar
            </a>
        </div>

        <table class="w-full text-sm text-left border">
            <thead class="text-white" style="background-color: #E26A23;">
                <tr>
                    <th class="px-4 py-2">Visitante</th>
                    <th class="px-4 py-2">Correo</th>
                    <th class="px-4 py-2">Hora Entrada</th>
                    <th class="px-4 py-2">Tiempo en Campus</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitantes as $r)
                <tr class="border-b"
                    onmouseover="this.style.backgroundColor='#FFF3EC'"
                    onmouseout="this.style.backgroundColor='transparent'">
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
                            $ahora   = \Carbon\Carbon::now();
                            $diff    = $entrada->diff($ahora);
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