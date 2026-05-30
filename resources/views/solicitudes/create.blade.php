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
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        <option value="" disabled {{ old('id_tipo_solicitud') ? '' : 'selected' }}>Seleccione una opción</option>
                        @foreach($tipos->where('nombre', '!=', 'Consulta') as $tipo)
                            <option value="{{ $tipo->id_tipo_solicitud }}"
                                data-nombre="{{ $tipo->nombre }}"
                                {{ old('id_tipo_solicitud') == $tipo->id_tipo_solicitud ? 'selected' : '' }}>
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
                                onchange="toggleGrupal()"
                                {{ old('es_grupal') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-omg-kashmir peer-focus:outline-none rounded-full peer
                                peer-checked:after:translate-x-full peer-checked:bg-omg-coral
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                            </div>
                        </label>
                        <span class="text-sm text-omg-slate">Grupal</span>
                    </div>
                    <input type="hidden" name="es_grupal" id="es_grupal" value="{{ old('es_grupal', '0') }}">
                </div>

                {{-- Fecha --}}
                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-calendar mr-1"></i> Fecha y Hora
                    </label>
                    <input type="datetime-local" name="fecha_inicio" id="fecha_inicio"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile"
                        value="{{ old('fecha_inicio') }}">
                    <p class="text-xs text-omg-kashmir mt-1">
                        Lunes a Viernes 6:00 AM – 9:00 PM · Sábado hasta 2:00 PM · No domingos.
                    </p>
                    @error('fecha_inicio')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Lugar de encuentro --}}
                <div class="relative">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-map-marker-alt mr-1"></i> Lugar de Encuentro
                    </label>
                    <input type="text" id="lugar-search"
                        placeholder="Escribe para filtrar o selecciona..."
                        autocomplete="off"
                        value="{{ old('lugar_encuentro') }}"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile"
                        oninput="filtrarLugares(this.value)"
                        onfocus="mostrarLugares()"
                        onblur="setTimeout(ocultarLugares, 200)">
                    <input type="hidden" name="lugar_encuentro" id="lugar_encuentro" value="{{ old('lugar_encuentro') }}">
                    <ul id="lugares-dropdown"
                        class="hidden absolute left-0 right-0 bg-white border border-omg-kashmir rounded-lg shadow-lg mt-0 overflow-y-auto"
                        style="z-index:9999; max-height:210px;  top:calc(100% - 15px);">
                    </ul>
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
                                <option value="15" {{ old('tolerancia_antes', '15') == '15' ? 'selected' : '' }}>15 min</option>
                                <option value="30" {{ old('tolerancia_antes') == '30' ? 'selected' : '' }}>30 min</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-omg-kashmir mb-1">Después (min)</label>
                            <select name="tolerancia_despues"
                                class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-coral">
                                <option value="15" {{ old('tolerancia_despues', '15') == '15' ? 'selected' : '' }}>15 min</option>
                                <option value="30" {{ old('tolerancia_despues') == '30' ? 'selected' : '' }}>30 min</option>
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
                            <input type="text" name="visitante_nombre[]"
                                placeholder="Nombre"
                                value="{{ old('visitante_nombre.0') }}"
                                class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-user mr-1"></i> Apellidos</label>
                            <input type="text" name="visitante_apellidos[]"
                                placeholder="Apellidos"
                                value="{{ old('visitante_apellidos.0') }}"
                                class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-omg-slate mb-1"><i class="fas fa-envelope mr-1"></i> Correo</label>
                            <input type="email" name="visitante_correo[]"
                                placeholder="correo@ejemplo.com"
                                value="{{ old('visitante_correo.0') }}"
                                class="w-full border border-omg-kashmir rounded-lg px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botón agregar visitante (solo grupal) --}}
            <div id="btn-agregar" class="{{ old('es_grupal') == '1' ? '' : 'hidden' }} mb-4">
                <button type="button" onclick="agregarVisitante()"
                    class="w-full border-2 border-dashed border-omg-kashmir text-omg-nile px-4 py-3 rounded-xl hover:bg-omg-chardon transition font-heading font-semibold flex items-center justify-center gap-2">
                    <i class="fas fa-user-plus"></i> Agregar otro visitante
                </button>
            </div>

            {{-- Contador --}}
            <div id="contador" class="{{ old('es_grupal') == '1' ? '' : 'hidden' }} mb-6">
                <p class="text-sm text-omg-kashmir flex items-center gap-1">
                    <i class="fas fa-users"></i>
                    Total de visitantes: <span id="num-visitantes" class="font-bold text-omg-nile ml-1">1</span>
                </p>
            </div>

            <div class="flex justify-end gap-4 mt-6 pt-4 border-t border-omg-chardon">
                <a href="{{ route('solicitudes.index') }}"
                   class="text-white px-4 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                   style="background-color: #3B5675;">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit"
                    class="text-white px-6 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                    style="background-color: #DA7E2D;">
                    Guardar <i class="fas fa-save"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        const lugares = [
            'Edificio A — Edificio Administrativo',
            'Edificio B — Centro de Información',
            'Edificio B-1 — Centro de Cómputo',
            'Edificio B-2 — Ingeniería Industrial',
            'Edificio B-3 — Posgrados e Investigación',
            'Edificio B-4 — Ingeniería Logística y Subdirección Académica',
            'Edificio B-5 — Laboratorio de Ingeniería Ambiental',
            'Edificio C-1 — Unidad de Apoyo Tutorial',
            'Edificio C-2 — Desarrollo Académico',
            'Edificio C-3 — Lenguas Extranjeras y Sala de Titulación',
            'Edificio D — Cubículos de Ingeniería Química',
            'Edificio D-1 — Laboratorio de Ingeniería Electrónica',
            'Edificio D-3 — Ingeniería Mecatrónica',
            'Edificio F',
            'Edificio G — Sindicato',
            'Edificio G — Laboratorio de Análisis',
            'Edificio H — Cafetería',
            'Edificio I — Ciencias Básicas',
            'Edificio J — Laboratorio de Química',
            'Edificio K — Gestión Tecnológica y Vinculación',
            'Edificio K — Sala Audiovisual y Laboratorio',
            'Edificio M — Ingeniería Electromecánica',
            'Edificio N — Laboratorio de Análisis',
            'Edificio O — Actividades Extraescolares',
            'Edificio P — Ingeniería Química',
            'Edificio Q — Ingeniería Electrónica',
            'Edificio R — Almacén General y Departamento de Mantenimiento',
            'Edificio S — LIEM',
            'Edificio T — Ingeniería TICs y Sistemas',
            'Edificio U — Cubículos de Profesores',
            'Edificio X — Servicios Escolares',
            'Edificio Y — Ingeniería en GE',
            'Edificio Z — Ingeniería en GE',
        ];

        function filtrarLugares(query) {
            const filtrados = query
                ? lugares.filter(l => l.toLowerCase().includes(query.toLowerCase()))
                : lugares;
            renderDropdown(filtrados);
            document.getElementById('lugares-dropdown').classList.remove('hidden');
            document.getElementById('lugar_encuentro').value = query;
        }

        function mostrarLugares() {
            const query = document.getElementById('lugar-search').value;
            const filtrados = query
                ? lugares.filter(l => l.toLowerCase().includes(query.toLowerCase()))
                : lugares;
            renderDropdown(filtrados);
            document.getElementById('lugares-dropdown').classList.remove('hidden');
        }

        function ocultarLugares() {
            document.getElementById('lugares-dropdown').classList.add('hidden');
        }

        function renderDropdown(lista) {
            const dropdown = document.getElementById('lugares-dropdown');
            if (lista.length === 0) {
                dropdown.innerHTML = '<li class="px-3 py-2 text-sm text-gray-400">No se encontraron resultados</li>';
                return;
            }
            dropdown.innerHTML = lista.map(l => `
                <li class="px-3 py-2 text-sm cursor-pointer border-b border-gray-50 last:border-0"
                    onmouseover="this.style.backgroundColor='#FFF3EC'"
                    onmouseout="this.style.backgroundColor='white'"
                    onmousedown="seleccionarLugar('${l.replace(/'/g, "\\'")}')">
                    <i class="fas fa-map-marker-alt mr-2 text-xs" style="color:#DA7E2D;"></i>${l}
                </li>
            `).join('');
        }

        function seleccionarLugar(lugar) {
            document.getElementById('lugar-search').value = lugar;
            document.getElementById('lugar_encuentro').value = lugar;
            document.getElementById('lugares-dropdown').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('fecha_inicio');
            if (!input || input.value) return;

            const min = new Date();
            min.setHours(min.getHours() + 1);
            min.setSeconds(0, 0);
            const pad = (n) => String(n).padStart(2, '0');
            input.min = `${min.getFullYear()}-${pad(min.getMonth() + 1)}-${pad(min.getDate())}T${pad(min.getHours())}:${pad(min.getMinutes())}`;

            document.addEventListener('click', (e) => {
                if (!e.target.closest('#lugar-search') && !e.target.closest('#lugares-dropdown')) {
                    ocultarLugares();
                }
            });
        });

        function toggleGrupal() {
            const switchEl   = document.getElementById('switch-grupal');
            const btnAgregar = document.getElementById('btn-agregar');
            const contador   = document.getElementById('contador');
            const esGrupal   = document.getElementById('es_grupal');
            const esGrupalOn = switchEl.checked;

            btnAgregar.classList.toggle('hidden', !esGrupalOn);
            contador.classList.toggle('hidden', !esGrupalOn);
            esGrupal.value = esGrupalOn ? '1' : '0';

            if (!esGrupalOn) {
                document.querySelectorAll('.visitante-card').forEach((card, i) => {
                    if (i > 0) card.remove();
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