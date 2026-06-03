{{--
Empresa:     OMEGA Solutions
Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
Archivo:     resources/views/eventos/create.blade.php
Creación:    28/05/2026
Creado por:  Jacqueline Marifer Escobar Espinoza
Aprobado por: Líder de Área

Changelog:
ID: 1 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial del formulario con catálogo filtrable de lugares
ID: 2 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar campos de tolerancia antes y después (15 o 30 min)
ID: 3 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Validación de máximo 1000 personas por evento
--}}

<x-app-layout>
    <x-slot name="header">
        Nuevo Evento
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

        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4 flex items-center gap-2 text-sm">
            <i class="fas fa-info-circle"></i>
            Al guardar el evento se generará y enviará el QR automáticamente al correo del responsable.
        </div>

        <form action="{{ route('eventos.store') }}" method="POST">
            @csrf

            <h3 class="text-lg font-heading font-semibold text-omg-nile mb-4 border-b border-omg-kashmir pb-2 flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i> Datos del Evento
            </h3>

            <div class="grid grid-cols-2 gap-4 mb-6">

                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-list mr-1"></i> Tipo de Evento
                    </label>
                    <select name="tipo_evento"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                        <option value="" disabled {{ old('tipo_evento') ? '' : 'selected' }}>Seleccione una opción</option>
                        <option value="Académico" {{ old('tipo_evento') == 'Académico' ? 'selected' : '' }}>
                            Académico — Evaluaciones, exámenes, talleres
                        </option>
                        <option value="Administrativo" {{ old('tipo_evento') == 'Administrativo' ? 'selected' : '' }}>
                            Administrativo — Inscripciones, trámites, titulación
                        </option>
                        <option value="Cívico y Cultural" {{ old('tipo_evento') == 'Cívico y Cultural' ? 'selected' : '' }}>
                            Cívico y Cultural — Festivales, graduaciones, ceremonias
                        </option>
                        <option value="Deportivo y Salud" {{ old('tipo_evento') == 'Deportivo y Salud' ? 'selected' : '' }}>
                            Deportivo y Salud — Torneos, campañas de bienestar
                        </option>
                        <option value="Comunitario y Social" {{ old('tipo_evento') == 'Comunitario y Social' ? 'selected' : '' }}>
                            Comunitario y Social — Juntas, kermeses, vinculación
                        </option>
                    </select>
                    @error('tipo_evento')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-users mr-1"></i> Número de Personas
                    </label>
                    <input type="number" name="numero_personas" value="{{ old('numero_personas') }}"
                        min="1" placeholder="Ej. 40"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    @error('numero_personas')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-file-alt mr-1"></i> Descripción
                    </label>
                    <textarea name="descripcion" rows="2"
                        placeholder="Descripción del evento (opcional)"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">{{ old('descripcion') }}</textarea>
                </div>

                <div class="relative">
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-map-marker-alt mr-1"></i> Lugar
                    </label>
                    <input type="text" id="lugar-search"
                        placeholder="Escribe para filtrar o selecciona..."
                        autocomplete="off"
                        value="{{ old('lugar') }}"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile"
                        oninput="filtrarLugares(this.value)"
                        onfocus="mostrarLugares()"
                        onblur="setTimeout(ocultarLugares, 200)">
                    <input type="hidden" name="lugar" id="lugar" value="{{ old('lugar') }}">
                    <ul id="lugares-dropdown"
                        class="hidden absolute left-0 right-0 bg-white border border-omg-kashmir rounded-lg shadow-lg overflow-y-auto"
                        style="z-index:9999; max-height:200px; top:calc(100% - 15px);">
                    </ul>
                    @error('lugar')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-calendar mr-1"></i> Fecha y Hora
                    </label>
                    <input type="datetime-local" name="fecha_evento" value="{{ old('fecha_evento') }}"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    <p class="text-xs text-omg-kashmir mt-1">Solo Lunes a Viernes — no sábados ni domingos.</p>
                    @error('fecha_evento')
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

                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-user mr-1"></i> Nombre del Responsable
                    </label>
                    <input type="text" name="nombre_responsable" value="{{ old('nombre_responsable') }}"
                        placeholder="Nombre completo del responsable"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    @error('nombre_responsable')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-omg-slate mb-1">
                        <i class="fas fa-envelope mr-1"></i> Correo del Responsable
                    </label>
                    <input type="email" name="correo_responsable" value="{{ old('correo_responsable') }}"
                        placeholder="correo@ejemplo.com"
                        class="w-full border border-omg-kashmir rounded px-3 py-2 bg-omg-chardon focus:outline-none focus:ring-2 focus:ring-omg-nile">
                    @error('correo_responsable')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="flex justify-end gap-4 mt-6 pt-4 border-t border-omg-chardon">
                <a href="{{ route('eventos.index') }}"
                   class="text-white px-4 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                   style="background-color: #3B5675;">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit"
                    class="text-white px-6 py-2 rounded-lg hover:opacity-90 font-heading font-semibold flex items-center gap-2"
                    style="background-color: #DA7E2D;">
                    <i class="fas fa-save"></i> Guardar y Enviar QR
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
            'Edificio F - Matemáticas Básicas',
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
            'Entrada Principal',
            'Auditorio',
            'Estacionamiento',
        ];

        function filtrarLugares(query) {
            const filtrados = query
                ? lugares.filter(l => l.toLowerCase().includes(query.toLowerCase()))
                : lugares;
            renderDropdown(filtrados);
            document.getElementById('lugares-dropdown').classList.remove('hidden');
            document.getElementById('lugar').value = query;
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
            document.getElementById('lugar').value = lugar;
            document.getElementById('lugares-dropdown').classList.add('hidden');
        }
    </script>
</x-app-layout>