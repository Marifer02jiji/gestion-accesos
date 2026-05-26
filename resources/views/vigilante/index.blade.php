<x-app-layout>
    <x-slot name="header">
        Control de Accesos
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

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                @foreach($errors->all() as $error)
                    <p class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Datos del vigilante --}}
        @if(!session('vigilante_telefono'))
            <div class="bg-white shadow-sm rounded-xl p-6">
                <h3 class="text-base font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                    <i class="fas fa-user-shield"></i> Identificación del Vigilante
                </h3>
                <form action="{{ route('vigilante.identificar') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1">
                                <i class="fas fa-phone mr-1"></i> Número de Teléfono
                            </label>
                            <input type="text" name="telefono" placeholder="10 dígitos" maxlength="10"
                                pattern="[0-9]{10}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1">
                                <i class="fas fa-map-marker-alt mr-1"></i> Área Asignada
                            </label>
                            <select name="area" class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                                <option value="">Seleccione su área</option>
                                <option value="Entrada principal">Entrada principal</option>
                                <option value="Entrada lateral">Entrada lateral</option>
                                <option value="Edificio A">Edificio A</option>
                                <option value="Edificio B">Edificio B</option>
                                <option value="Edificio T">Edificio T</option>
                                <option value="Dirección">Dirección</option>
                                <option value="Recursos Materiales">Recursos Materiales</option>
                                <option value="Estacionamiento">Estacionamiento</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full bg-omg-coral text-white py-3 rounded-lg font-heading font-semibold hover:opacity-90 flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Continuar
                    </button>
                </form>
            </div>
        @else
            {{-- Info del vigilante activo --}}
            <div class="bg-omg-nile text-white rounded-xl px-5 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-user-shield text-omg-coral text-xl"></i>
                    <div>
                        <p class="font-semibold text-sm">{{ session('vigilante_area') }}</p>
                        <p class="text-omg-kashmir text-xs"><i class="fas fa-phone mr-1"></i>{{ session('vigilante_telefono') }}</p>
                    </div>
                </div>
                <a href="{{ route('vigilante.salirSesion') }}"
                    class="text-xs text-omg-kashmir hover:text-white flex items-center gap-1">
                    <i class="fas fa-sign-out-alt"></i> Cambiar
                </a>
            </div>

            {{-- Acciones rápidas --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-omg-nile text-white rounded-xl p-4 flex items-center gap-3 hover:opacity-90 cursor-pointer"
                    onclick="document.getElementById('form-escanear').scrollIntoView({behavior: 'smooth'})">
                    <i class="fas fa-qrcode text-2xl"></i>
                    <div>
                        <p class="font-heading font-bold">Escanear QR</p>
                        <p class="text-omg-kashmir text-xs">Registrar entrada o salida</p>
                    </div>
                </div>
                <a href="{{ route('vigilante.historial') }}"
                    class="bg-omg-coral text-white rounded-xl p-4 flex items-center gap-3 hover:opacity-90">
                    <i class="fas fa-history text-2xl"></i>
                    <div>
                        <p class="font-heading font-bold">Historial</p>
                        <p class="text-xs opacity-80">Ver registros de acceso</p>
                    </div>
                </a>
            </div>

            {{-- Visitas del día --}}
            <div class="bg-white shadow-sm rounded-xl p-6">
                <h3 class="text-base font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                    <i class="fas fa-calendar-day"></i> Visitas Programadas Hoy
                    <span class="ml-2 bg-omg-coral text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $visitasHoy->count() }}
                    </span>
                </h3>

                @forelse($visitasHoy as $visita)
                <div class="border border-omg-kashmir rounded-lg p-3 mb-3 bg-omg-chardon flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-omg-nile text-white rounded-full w-9 h-9 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <div>
                            @foreach($visita->visitantes as $v)
                                <p class="font-semibold text-omg-slate text-sm">{{ $v->nombre }} {{ $v->apellidos }}</p>
                            @endforeach
                            <p class="text-xs text-omg-kashmir flex items-center gap-2 mt-0.5">
                                <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($visita->fecha_inicio)->format('H:i') }}
                                <i class="fas fa-map-marker-alt ml-1"></i> {{ $visita->lugar_encuentro }}
                            </p>
                            @if($visita->folio)
                                <p class="text-xs text-omg-nile font-mono font-bold mt-0.5">
                                    <i class="fas fa-ticket-alt mr-1"></i> {{ $visita->folio }}
                                </p>
                            @endif
                        </div>
                    </div>
                    {{-- Estado en tiempo real --}}
                    @php
                        $registro = \App\Models\RegistroAcceso::whereHas('qr.solicitudVisitante.solicitud', function($q) use ($visita) {
                            $q->where('id_solicitud', $visita->id_solicitud);
                        })->orderBy('id_registro', 'desc')->first();
                    @endphp
                    @if($registro && $registro->hora_llegada_institucion && !$registro->hora_salida_institucion)
                        <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-1 rounded-full flex items-center gap-1 flex-shrink-0">
                            <i class="fas fa-building"></i> Dentro
                        </span>
                    @elseif($registro && $registro->hora_salida_institucion)
                        <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-2 py-1 rounded-full flex items-center gap-1 flex-shrink-0">
                            <i class="fas fa-sign-out-alt"></i> Salió
                        </span>
                    @else
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded-full flex items-center gap-1 flex-shrink-0">
                            <i class="fas fa-check-circle"></i> Autorizada
                        </span>
                    @endif
                </div>
                @empty
                <div class="text-center py-4 text-gray-400">
                    <i class="fas fa-calendar-times text-2xl mb-1"></i>
                    <p class="text-sm">No hay visitas programadas para hoy</p>
                </div>
                @endforelse
            </div>

            {{-- Formulario escaneo --}}
            <div id="form-escanear" class="bg-white shadow-sm rounded-xl p-6">
                <h3 class="text-base font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                    <i class="fas fa-qrcode"></i> Escanear Código QR
                </h3>
                <form action="{{ route('vigilante.escanear') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-omg-slate mb-1">
                            <i class="fas fa-barcode mr-1"></i> Código VIS-XXXX-XXXX
                        </label>
                        <input type="text" name="codigo_qr" autofocus
                            placeholder="VIS-0000-0000"
                            class="w-full border-2 border-omg-kashmir rounded-lg px-4 py-3 text-base font-mono bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile tracking-widest text-center">
                    </div>
                    <button type="submit"
                        class="w-full bg-omg-coral text-white py-3 rounded-lg font-heading font-semibold text-base hover:opacity-90 flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Verificar
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>