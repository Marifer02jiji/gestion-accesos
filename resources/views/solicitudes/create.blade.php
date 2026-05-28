<x-app-layout>
    <x-slot name="header">
        Nueva Solicitud de Visita
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

        <form action="{{ route('solicitudes.store') }}" method="POST" id="form-solicitud">
            @csrf

            <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                <i class="fas fa-clipboard"></i> Datos de la Visita
            </h3>

            <div class="grid grid-cols-2 gap-4 mb-6">

                {{-- Tipo de visita --}}
                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-tag mr-1"></i> Tipo de Visita
                    </label>
                    <select name="id_tipo_solicitud" id="tipo_solicitud"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile"
                        onchange="actualizarDireccion()">
                        <option value="" disabled selected>Seleccione una opción</option>
                        @foreach($tipos->where('nombre', '!=', 'Consulta') as $tipo)
                            <option value="{{ $tipo->id_tipo_solicitud }}" data-nombre="{{ $tipo->nombre }}">
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_tipo_solicitud')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Switch visita grupal --}}
                <div class="flex flex-col justify-center">
                    <label class="block text-sm font-semibold text-omg-slate mb-2">
                        <i class="fas fa-users mr-1"></i> Visita Grupal
                    </label>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-omg-slate">Individual</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="switch-grupal" class="sr-only peer"
                                onchange="toggleGrupal()">
                            <div class="w-11 h-6 bg-omg-kashmir peer-focus:outline-none rounded-full peer
                                peer-checked:after:translate-x-full peer-checked:bg-omg-coral
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                            </div>
                        </label>
                        <span class="text-sm text-omg-slate">Grupal</span>
                    </div>
                    <input type="hidden" name="es_grupal" id="es_grupal" value="0">
                </div>

                {{-- Dirección automática según tipo --}}
                <div id="direccion-info" class="col-span-2 hidden">
                    <div class="bg-omg-chardon border border-omg-kashmir rounded-lg px-4 py-3 flex items-center gap-2">
                        <i class="fas fa-info-circle text-omg-nile"></i>
                        <p class="text-sm text-omg-slate" id="direccion-texto"></p>
                    </div>
                </div>

                {{-- Fecha --}}
                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-calendar mr-1"></i> Fecha y Hora
                    </label>
                    <input type="datetime-local" name="fecha_inicio"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile"
                        value="{{ old('fecha_inicio') }}">
                    @error('fecha_inicio')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Lugar --}}
                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-map-marker-alt mr-1"></i> Lugar de Encuentro
                    </label>
                    <input type="text" name="lugar_encuentro"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile"
                        value="{{ old('lugar_encuentro') }}">
                    @error('lugar_encuentro')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tolerancia --}}
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-omg-slate mb-2">
                        <i class="fas fa-clock mr-1"></i> Tolerancia de llegada
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-omg-kashmir mb-1">Antes (min)</label>
                            <select name="tolerancia_antes"
                                class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-coral">
                                <option value="15">15 min</option>
                                <option value="30">30 min</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-omg-kashmir mb-1">Después (min)</label>
                            <select name="tolerancia_despues"
                                class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-coral">
                                <option value="15">15 min</option>
                                <option value="30">30 min</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Motivo --}}
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-file-alt mr-1"></i> Motivo de la Visita
                    </label>
                    <textarea name="motivo_visita" rows="3"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">{{ old('motivo_visita') }}</textarea>
                    @error('motivo_visita')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Visitantes --}}
            <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                <i class="fas fa-users"></i> Datos del Visitante
            </h3>

            <div id="visitantes-container" class="space-y-4 mb-4">
                <div class="visitante-card border border-omg-kashmir rounded-xl p-5 bg-white shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="bg-omg-nile text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold numero-visitante">1</div>
                        <p class="font-heading font-semibold text-omg-nile text-sm nombre-visitante">Visitante 1</p>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-user mr-1"></i> Nombre</label>
                            <input type="text" name="visitante_nombre[]" placeholder="Nombre"
                                class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-user mr-1"></i> Apellidos</label>
                            <input type="text" name="visitante_apellidos[]" placeholder="Apellidos"
                                class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-envelope mr-1"></i> Correo</label>
                            <input type="email" name="visitante_correo[]" placeholder="correo@ejemplo.com"
                                class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botón agregar visitante (solo grupal) --}}
            <div id="btn-agregar" class="hidden mb-4">
                <button type="button" onclick="agregarVisitante()"
                    class="w-full border-2 border-dashed border-omg-kashmir text-omg-nile px-4 py-3 rounded-xl hover:bg-omg-chardon transition font-heading font-semibold flex items-center justify-center gap-2">
                    <i class="fas fa-user-plus"></i> Agregar otro visitante
                </button>
            </div>

            {{-- Contador --}}
            <div id="contador" class="hidden mb-6">
                <p class="text-sm text-omg-kashmir flex items-center gap-1">
                    <i class="fas fa-users"></i>
                    Total de visitantes: <span id="num-visitantes" class="font-bold text-omg-nile ml-1">1</span>
                </p>
            </div>

            <div class="flex justify-end gap-4 mt-6 pt-4 border-t border-omg-chardon">
                <a href="{{ route('solicitudes.index') }}"
                   class="bg-omg-nile text-white px-4 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit"
                    class="bg-omg-coral text-white px-6 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2">
                    Guardar <i class="fas fa-save"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        function actualizarDireccion() {
            const select = document.getElementById('tipo_solicitud');
            const option = select.options[select.selectedIndex];
            const nombre = option.dataset.nombre;
            const info   = document.getElementById('direccion-info');
            const texto  = document.getElementById('direccion-texto');

            if (!nombre) { info.classList.add('hidden'); return; }

            info.classList.remove('hidden');

            if (nombre === 'Proveedor' || nombre === 'Institucional / Negocios') {
                texto.innerHTML = '<strong>Dirección:</strong> Esta solicitud será enviada al <strong>Departamento</strong> correspondiente para su autorización';
            } else if (nombre === 'Personal') {
                texto.innerHTML = '<strong>Dirección:</strong> Esta solicitud será enviada a tu <strong>Jefe directo</strong> para su autorización';
            }
        }

        function toggleGrupal() {
            const switchEl   = document.getElementById('switch-grupal');
            const btnAgregar = document.getElementById('btn-agregar');
            const contador   = document.getElementById('contador');
            const esGrupal   = document.getElementById('es_grupal');
            const esGrupalOn = switchEl.checked;

            btnAgregar.classList.toggle('hidden', !esGrupalOn);
            contador.classList.toggle('hidden', !esGrupalOn);
            esGrupal.value = esGrupalOn ? '1' : '0';

            // Si desactiva grupal, elimina visitantes extra
            if (!esGrupalOn) {
                const cards = document.querySelectorAll('.visitante-card');
                cards.forEach((card, index) => {
                    if (index > 0) card.remove();
                });
                actualizarContador();
            }
        }

        function agregarVisitante() {
            const container = document.getElementById('visitantes-container');
            const total     = container.querySelectorAll('.visitante-card').length + 1;
            const card      = document.createElement('div');
            card.className  = 'visitante-card border border-omg-kashmir rounded-xl p-5 bg-white shadow-sm relative';
            card.innerHTML  = `
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="bg-omg-nile text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold numero-visitante">${total}</div>
                        <p class="font-heading font-semibold text-omg-nile text-sm nombre-visitante">Visitante ${total}</p>
                    </div>
                    <button type="button" onclick="eliminarVisitante(this)"
                        class="text-red-500 hover:text-red-700 flex items-center gap-1 text-sm">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-user mr-1"></i> Nombre</label>
                        <input type="text" name="visitante_nombre[]" placeholder="Nombre"
                            class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-user mr-1"></i> Apellidos</label>
                        <input type="text" name="visitante_apellidos[]" placeholder="Apellidos"
                            class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-envelope mr-1"></i> Correo</label>
                        <input type="email" name="visitante_correo[]" placeholder="correo@ejemplo.com"
                            class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                </div>`;
            container.appendChild(card);
            actualizarContador();
        }

        function eliminarVisitante(btn) {
            btn.closest('.visitante-card').remove();
            actualizarNumeracion();
            actualizarContador();
        }

        function actualizarNumeracion() {
            document.querySelectorAll('.visitante-card').forEach((card, index) => {
                const n = card.querySelector('.numero-visitante');
                const p = card.querySelector('.nombre-visitante');
                if (n) n.textContent = index + 1;
                if (p) p.textContent = `Visitante ${index + 1}`;
            });
        }

        function actualizarContador() {
            const total = document.querySelectorAll('.visitante-card').length;
            document.getElementById('num-visitantes').textContent = total;
        }
    </script>
</x-app-layout>