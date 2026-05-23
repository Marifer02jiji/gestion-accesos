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
                <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded-full flex items-center gap-1 flex-shrink-0">
                    <i class="fas fa-check-circle"></i> Autorizada
                </span>
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
                    @error('codigo_qr')
                        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
                <button type="submit"
                    class="w-full bg-omg-coral text-white py-3 rounded-lg font-heading font-semibold text-base hover:opacity-90 flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i> Verificar
                </button>
            </form>
        </div>

    </div>
</x-app-layout>