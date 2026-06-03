{{--
Empresa:     OMEGA Solutions
Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
Archivo:     resources/views/solicitudes/historial.blade.php
Creación:    31/05/2026
Creado por:  Jacqueline Marifer Escobar Espinoza
Aprobado por: Líder de Área

Changelog:
ID: 1 | Fecha: 31/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, historial de visitas finalizadas con tiempo total dentro de institución
--}}

<x-app-layout>
    <x-slot name="header">
        Historial de Visitas
    </x-slot>

    <div class="space-y-4">

        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-heading font-semibold text-omg-slate flex items-center gap-2">
                    <i class="fas fa-history" style="color: #DA7E2D;"></i>
                    Visitas Finalizadas
                </h3>
                <a href="{{ route('solicitudes.index') }}"
                   class="text-white px-4 py-2 rounded hover:opacity-90 font-heading font-semibold flex items-center gap-2 text-sm"
                   style="background-color: #3B5675;">
                    <i class="fas fa-arrow-left"></i> Regresar
                </a>
            </div>

            @forelse($solicitudes as $s)
            @php
                $sv      = $s->solicitudVisitantes->first();
                $qr      = $sv?->qr;
                $registro = $qr ? \App\Models\RegistroAcceso::where('id_qr', $qr->id_qr)
                    ->whereNotNull('hora_salida_institucion')
                    ->orderBy('id_registro', 'desc')
                    ->first() : null;

                $entrada  = $registro ? \Carbon\Carbon::parse($registro->hora_llegada_institucion) : null;
                $salida   = $registro ? \Carbon\Carbon::parse($registro->hora_salida_institucion) : null;
                $duracion = ($entrada && $salida) ? $entrada->diff($salida) : null;
            @endphp

            <div class="border rounded-xl p-5 mb-4 hover:shadow-md transition"
                 style="border-color: #A9AAAD; background-color: #f9fafb;">

                {{-- Encabezado --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-sm" style="color: #DA7E2D;">
                            {{ $s->folio }}
                        </span>
                        <span class="px-2 py-1 rounded-full text-white text-xs font-semibold bg-green-700">
                            Finalizada
                        </span>
                    </div>
                    <span class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($s->fecha_inicio)->format('d/m/Y') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-users mr-1"></i> Visitante(s)
                        </p>
                        @foreach($s->visitantes as $v)
                            <p class="font-semibold text-omg-slate text-sm">{{ $v->nombre }} {{ $v->apellidos }}</p>
                        @endforeach
                    </div>
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-map-marker-alt mr-1"></i> Lugar
                        </p>
                        <p class="font-semibold text-omg-slate text-sm">{{ $s->lugar_encuentro }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-sign-in-alt mr-1"></i> Entrada
                        </p>
                        <p class="font-semibold text-omg-slate text-sm">
                            {{ $entrada ? $entrada->format('H:i') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-sign-out-alt mr-1"></i> Salida
                        </p>
                        <p class="font-semibold text-omg-slate text-sm">
                            {{ $salida ? $salida->format('H:i') : '—' }}
                        </p>
                    </div>
                </div>

                {{-- Tiempo total --}}
                @if($duracion)
                <div class="flex items-center gap-2 bg-white rounded-lg px-4 py-2 border"
                     style="border-color: #A9AAAD;">
                    <i class="fas fa-clock" style="color: #DA7E2D;"></i>
                    <span class="text-sm font-semibold text-omg-slate">
                        Tiempo dentro de la institución:
                    </span>
                    <span class="text-sm font-bold" style="color: #DA7E2D;">
                        {{ $duracion->h }}h {{ $duracion->i }}min
                    </span>
                </div>
                @endif

                {{-- Encuentro --}}
                @if($s->hora_llegada_encuentro)
                <div class="mt-3 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-handshake mr-1"></i> Llegada al encuentro
                        </p>
                        <p class="font-semibold text-omg-slate text-sm">
                            {{ \Carbon\Carbon::parse($s->hora_llegada_encuentro)->format('H:i') }}
                        </p>
                    </div>
                    @if($s->hora_salida_encuentro)
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color: #A9AAAD;">
                            <i class="fas fa-door-open mr-1"></i> Salida del encuentro
                        </p>
                        <p class="font-semibold text-omg-slate text-sm">
                            {{ \Carbon\Carbon::parse($s->hora_salida_encuentro)->format('H:i') }}
                        </p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Botón eliminar --}}
                <div class="flex justify-end mt-3">
                    <form action="{{ route('solicitudes.destroy', $s->id_solicitud) }}" method="POST"
                        onsubmit="return confirm('¿Eliminar esta solicitud del historial?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="bg-red-600 text-white px-3 py-1 rounded hover:opacity-90 text-xs flex items-center gap-1">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-history text-4xl mb-2 block"></i>
                <p class="font-semibold">No hay visitas finalizadas</p>
            </div>
            @endforelse

            <div class="mt-4">
                {{ $solicitudes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>