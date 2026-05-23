<x-app-layout>
    <x-slot name="header">
        Nueva Solicitud de Visita
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg p-6">

        {{-- Error si no carga el catálogo de motivos (RF11) --}}
        @if(isset($error_motivos))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ $error_motivos }}
            </div>
        @endif

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

        <form action="{{ route('solicitudes.store') }}" method="POST">
            @csrf

            <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                <i class="fas fa-clipboard"></i> Datos de la Visita
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

                {{-- Fecha y Hora --}}
                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-calendar mr-1"></i> Fecha y Hora <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="fecha_inicio"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile"
                        value="{{ old('fecha_inicio') }}">
                    @error('fecha_inicio')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Lugar de Encuentro --}}
                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-map-marker-alt mr-1"></i> Lugar de Encuentro <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="lugar_encuentro"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile"
                        value="{{ old('lugar_encuentro') }}" placeholder="Ej. Sala de juntas, Oficina 204">
                    @error('lugar_encuentro')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipo de Visita --}}
                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-tag mr-1"></i> Tipo de Visita <span class="text-red-500">*</span>
                    </label>
                    <select name="id_tipo_solicitud" id="tipo_solicitud"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile"
                        onchange="toggleGrupal()">
                        <option value="" disabled selected>Seleccione el tipo de visitante</option>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->id_tipo_solicitud }}"
                                {{ old('id_tipo_solicitud') == $tipo->id_tipo_solicitud ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_tipo_solicitud')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Tolerancia --}}
                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-clock mr-1"></i> Tolerancia de Llegada <span class="text-red-500">*</span>
                    </label>
                    <select name="tolerancia_antes"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        <option value="15">15 minutos</option>
                        <option value="30">30 minutos</option>
                    </select>
                </div>

                {{-- Motivo desde catálogo (RF18) --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-file-alt mr-1"></i> Motivo de la Visita <span class="text-red-500">*</span>
                    </label>
                    @if($motivos->isEmpty())
                        <div class="bg-yellow-50 border border-yellow-300 text-yellow-700 px-3 py-2 rounded text-sm">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            No fue posible cargar tipos de visitante. Recarga la página.
                        </div>
                    @else
                        <select name="motivo_visita"
                            class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                            <option value="" disabled selected>Seleccione el motivo de visita</option>
                            @foreach($motivos as $motivo)
                                <option value="{{ $motivo->nombre }}"
                                    {{ old('motivo_visita') == $motivo->nombre ? 'selected' : '' }}>
                                    {{ $motivo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('motivo_visita')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

            </div>

            <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                <i class="fas fa-users"></i> Datos del Visitante
            </h3>

            <div id="visitantes-container" class="space-y-4 mb-4">
                {{-- Visitante 1 siempre visible --}}
                <div class="visitante-card border border-omg-kashmir rounded-xl p-5 bg-white shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="bg-omg-nile text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold numero-visitante">
                            1
                        </div>
                        <p class="font-heading font-semibold text-omg-nile text-sm nombre-visitante">Visitante 1</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1">
                                <i class="fas fa-user mr-1"></i> Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="visitante_nombre[]" placeholder="Nombre"
                                class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1">
                                <i class="fas fa-user mr-1"></i> Apellidos <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="visitante_apellidos[]" placeholder="Apellidos"
                                class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1">
                                <i class="fas fa-envelope mr-1"></i> Correo <span class="text-red-500">*</span>
                            </label>
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
        function toggleGrupal() {
            const tipo = document.getElementById('tipo_solicitud');
            const btnAgregar = document.getElementById('btn-agregar');
            const contador = document.getElementById('contador');
            const esGrupal = tipo.value == 2;

            btnAgregar.classList.toggle('hidden', !esGrupal);
            contador.classList.toggle('hidden', !esGrupal);

            // Si cambia a Individual → eliminar visitantes extra
            if (!esGrupal) {
                const cards = document.querySelectorAll('.visitante-card');
                cards.forEach((card, index) => {
                    if (index > 0) card.remove();
                });
                actualizarContador();
            }
        }

        function agregarVisitante() {
            const container = document.getElementById('visitantes-container');
            const total = container.querySelectorAll('.visitante-card').length + 1;
            const card = document.createElement('div');
            card.className = 'visitante-card border border-omg-kashmir rounded-xl p-5 bg-white shadow-sm relative';
            card.innerHTML = `
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-user mr-1"></i> Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="visitante_nombre[]" placeholder="Nombre"
                            class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-user mr-1"></i> Apellidos <span class="text-red-500">*</span></label>
                        <input type="text" name="visitante_apellidos[]" placeholder="Apellidos"
                            class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-envelope mr-1"></i> Correo <span class="text-red-500">*</span></label>
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
            const el = document.getElementById('num-visitantes');
            if (el) el.textContent = document.querySelectorAll('.visitante-card').length;
        }
    </script>
</x-app-layout>