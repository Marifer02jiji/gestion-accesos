<x-app-layout>
    <x-slot name="header">
        Visitantes Activos en Institución
    </x-slot>

    <div class="bg-white shadow-sm rounded-xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-heading font-semibold flex items-center gap-2" style="color: #DA7E2D;">
                <i class="fas fa-user-check"></i> Visitantes dentro de la institución
                <span class="text-white text-xs font-bold px-2 py-1 rounded-full ml-2"
                      style="background-color: #DA7E2D;">
                    {{ $visitantes->count() }}
                </span>
            </h3>
            <div class="flex gap-2">
                <a href="{{ route('dashboard') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                   style="background-color: #3B5675;">
                    <i class="fas fa-arrow-left"></i> Regresar
                </a>
            </div>
        </div>

        <table class="w-full text-sm text-left border">
            <thead class="text-white" style="background-color: #E26A23;">
                <tr>
                    <th class="px-4 py-2">Visitante</th>
                    <th class="px-4 py-2">Correo</th>
                    <th class="px-4 py-2">Anfitrión</th>
                    <th class="px-4 py-2">Hora Entrada</th>
                    <th class="px-4 py-2">Tiempo en Institución</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitantes as $r)
                @php
                    $entrada    = \Carbon\Carbon::parse($r->hora_llegada_institucion);
                    $ahora      = \Carbon\Carbon::now();
                    $diff       = $entrada->diff($ahora);
                    $totalHoras = $entrada->diffInHours($ahora);
                    $masde10hrs = $totalHoras >= 10;
                    $solicitante = $r->qr->solicitudVisitante->solicitud->solicitante ?? null;
                    $bgHover    = $masde10hrs ? '#FEE2E2' : 'transparent';
                @endphp
                <tr class="border-b {{ $masde10hrs ? 'bg-red-50' : '' }}"
                    onmouseover="this.style.backgroundColor='#FFF3EC'"
                    onmouseout="this.style.backgroundColor='{{ $bgHover }}'">

                    <td class="px-4 py-2 font-semibold">
                        {{ $r->qr->solicitudVisitante->visitante->nombre }}
                        {{ $r->qr->solicitudVisitante->visitante->apellidos }}
                        @if($masde10hrs)
                            <span class="block text-xs text-red-600 font-bold">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Tiempo excedido
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-2">
                        {{ $r->qr->solicitudVisitante->visitante->correo_personal }}
                    </td>

                    <td class="px-4 py-2">
                        @if($solicitante)
                            <span class="flex items-center gap-1">
                                <i class="fas fa-user-tie text-xs" style="color: #A9AAAD;"></i>
                                {{ $solicitante->name }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">Sin anfitrión</span>
                        @endif
                    </td>

                    <td class="px-4 py-2">
                        {{ \Carbon\Carbon::parse($r->hora_llegada_institucion)->format('H:i') }}
                    </td>

                    <td class="px-4 py-2">
                        <span class="{{ $totalHoras >= 2 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                            {{ $diff->h }}h {{ $diff->i }}min
                        </span>
                    </td>

                    <td class="px-4 py-2">
                        @if($masde10hrs)
                            <form action="{{ route('admin.registrarSalida') }}" method="POST"
                                onsubmit="return confirm('Registrar salida forzada de este visitante?')">
                                @csrf
                                <input type="hidden" name="id_qr" value="{{ $r->id_qr }}">
                                <button type="submit"
                                    class="bg-red-600 text-white px-3 py-1 rounded hover:opacity-90 text-xs flex items-center gap-1">
                                    <i class="fas fa-sign-out-alt"></i> Registrar Salida
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center px-4 py-8 text-gray-400">
                        <i class="fas fa-check-circle text-3xl mb-2 block"></i>
                        No hay visitantes activos en este momento
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>