{{--
Empresa:     OMEGA Solutions
Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
Archivo:     resources/views/dashboard.blade.php
Creación:    19/03/2026
Creado por:  Jacqueline Marifer Escobar Espinoza
Aprobado por: Líder de Área

Changelog:
ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, panel de inicio con accesos directos por rol
ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar header con color azul institucional #3B5675
--}}

<x-app-layout>
    <x-slot name="header">
        <span style="color: #3B5675;">Dashboard</span>
    </x-slot>

    <div class="flex flex-wrap gap-6">

        @role('solicitante')
        <a href="{{ route('solicitudes.index') }}"
            class="w-80 bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-omg-coral">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-file-alt text-omg-coral text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Mis Solicitudes</h3>
                    <p class="text-sm text-omg-slate">Gestión de visitas</p>
                </div>
            </div>
            <p class="text-sm text-gray-500"><i class="fas fa-info-circle mr-1"></i> Crea y consulta tus solicitudes.</p>
        </a>
        @endrole

        @role('autorizador')
        <a href="{{ route('autorizador.index') }}"
            class="w-80 bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-omg-nile">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-clipboard-check text-omg-nile text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Solicitudes Pendientes</h3>
                    <p class="text-sm text-omg-slate">Aprobar solicitudes</p>
                </div>
            </div>
            <p class="text-sm text-gray-500"><i class="fas fa-info-circle mr-1"></i> Autoriza o rechaza solicitudes.</p>
        </a>
        @endrole

        @role('vigilante')
        <a href="{{ route('vigilante.index') }}"
            class="w-80 bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-omg-kashmir">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-shield-alt text-omg-kashmir text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Control de Accesos</h3>
                    <p class="text-sm text-omg-slate">Escanear QR</p>
                </div>
            </div>
            <p class="text-sm text-gray-500"><i class="fas fa-info-circle mr-1"></i> Escanea QR y registra entradas.</p>
        </a>
        @endrole

        @role('administrador')
        <a href="{{ route('admin.reportes') }}"
            class="w-80 bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-green-600">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-chart-bar text-green-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Reportes</h3>
                    <p class="text-sm text-omg-slate">Estadísticas del sistema</p>
                </div>
            </div>
            <p class="text-sm text-gray-500"><i class="fas fa-info-circle mr-1"></i> Ver reportes y estadísticas.</p>
        </a>

        <a href="{{ route('admin.exclusiones') }}"
            class="w-80 bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-red-600">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-ban text-red-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Lista de Exclusión</h3>
                    <p class="text-sm text-omg-slate">Visitantes vetados</p>
                </div>
            </div>
            <p class="text-sm text-gray-500"><i class="fas fa-info-circle mr-1"></i> Gestiona visitantes bloqueados.</p>
        </a>

        <a href="{{ route('admin.visitantes-activos') }}"
            class="w-80 bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-omg-coral">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-user-check text-omg-coral text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Visitantes Activos</h3>
                    <p class="text-sm text-omg-slate">En institución ahora</p>
                </div>
            </div>
            <p class="text-sm text-gray-500"><i class="fas fa-info-circle mr-1"></i> Ver quién está dentro de la institución.</p>
        </a>
        @endrole

        @role('organizador')
        <a href="{{ route('eventos.index') }}"
            class="w-80 bg-white shadow-sm rounded-xl p-6 hover:shadow-md transition border-l-4 border-purple-600">
            <div class="flex items-center gap-4 mb-3">
                <div class="bg-omg-chardon p-4 rounded-full">
                    <i class="fas fa-calendar-alt text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-omg-nile text-lg">Eventos</h3>
                    <p class="text-sm text-omg-slate">Gestión de eventos y QR grupal</p>
                </div>
            </div>
            <p class="text-sm text-gray-500"><i class="fas fa-info-circle mr-1"></i> Crea eventos y genera QR para grupos.</p>
        </a>
        @endrole

    </div>
</x-app-layout>