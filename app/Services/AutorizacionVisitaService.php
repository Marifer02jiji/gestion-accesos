<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Services/AutorizacionVisitaService.php
 * Creación:    19/05/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, servicio de jerarquía SAM para determinar autorizadores válidos por solicitante
 * ID: 2 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Implementar idsSolicitantesAutorizables() consultando estructura organizacional SAM
 * ID: 3 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar puedeGestionarSolicitud() para validar si autorizador puede aprobar o rechazar
 * ID: 4 | Fecha: 03/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar autorizadores globales que pueden ver todas las solicitudes sin importar jerarquía
 */

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutorizacionVisitaService
{
    // Usuarios SAM que pueden ver y autorizar TODAS las solicitudes
    private const AUTORIZADORES_GLOBALES = ['mauro'];

    public static function normalizarUsuario(string $usuario): string
    {
        $u = strtolower(trim($usuario));

        if (str_contains($u, '@')) {
            $u = explode('@', $u)[0];
        }

        return $u;
    }

    public function usuarioEstaEnMatriz(string $usuarioSam): bool
    {
        $u = self::normalizarUsuario($usuarioSam);

        return array_key_exists($u, config('autorizacion_visita.matriz', []));
    }

    public function usuarioEsAutorizadorConfigurado(string $usuarioSam): bool
    {
        return $this->usuarioEstaEnMatriz($usuarioSam);
    }

    public function esAutorizadorGlobal(string $usuarioSam): bool
    {
        $u = self::normalizarUsuario($usuarioSam);
        return in_array($u, self::AUTORIZADORES_GLOBALES, true);
    }

    /**
     * IDs SAM de solicitantes que el autorizador puede gestionar.
     */
    public function idsSolicitantesAutorizables(int $idEmpleadoAutorizador, string $usuarioSam): array
    {
        $u = self::normalizarUsuario($usuarioSam);

        // Si es autorizador global, retorna TODOS los empleados activos
        if ($this->esAutorizadorGlobal($u)) {
            return $this->todosLosEmpleados($idEmpleadoAutorizador);
        }

        $matriz = config('autorizacion_visita.matriz', []);

        if (array_key_exists($u, $matriz)) {
            return $this->resolverIdsDesdeUsuarios(
                $matriz[$u],
                $idEmpleadoAutorizador
            );
        }

        return $this->idsPorDepartamentoYJefeSam($idEmpleadoAutorizador);
    }

    public function puedeGestionarSolicitud(
        int $idEmpleadoAutorizador,
        string $usuarioSam,
        int $idSolicitante
    ): bool {
        if ($idSolicitante === $idEmpleadoAutorizador) {
            return false;
        }

        $visibles = $this->idsSolicitantesAutorizables(
            $idEmpleadoAutorizador,
            $usuarioSam
        );

        return in_array($idSolicitante, $visibles, true);
    }

    /**
     * Retorna todos los empleados activos del SAM excepto el autorizador mismo.
     */
    private function todosLosEmpleados(int $idExcluir): array
    {
        try {
            return DB::connection('sam')
                ->table('empleados')
                ->where('estatus', 'Activo')
                ->where('id_empleado', '!=', $idExcluir)
                ->pluck('id_empleado')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('SAM no disponible para autorizador global: ' . $e->getMessage());
            return [];
        }
    }

    private function resolverIdsDesdeUsuarios(array $usuarios, int $idExcluir): array
    {
        if ($usuarios === []) {
            return [];
        }

        $normalizados = array_values(array_unique(array_map(
            fn ($name) => self::normalizarUsuario((string) $name),
            $usuarios
        )));

        try {
            return DB::connection('sam')
                ->table('empleados')
                ->whereIn('usuario', $normalizados)
                ->pluck('id_empleado')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0 && $id !== $idExcluir)
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning(
                'SAM: no se resolvieron solicitantes de la matriz de autorización: '
                . $e->getMessage()
            );

            return [];
        }
    }

    /** Respaldo cuando el usuario no está en la matriz explícita. */
    private function idsPorDepartamentoYJefeSam(int $id): array
    {
        try {
            $subordinados = DB::connection('sam')
                ->table('empleados')
                ->where('jefe', $id)
                ->pluck('id_empleado')
                ->toArray();

            $idDepartamento = DB::connection('sam')
                ->table('empleados')
                ->where('id_empleado', $id)
                ->value('id_departamento');

            $delDepartamento = $idDepartamento
                ? DB::connection('sam')
                    ->table('empleados')
                    ->where('id_departamento', $idDepartamento)
                    ->pluck('id_empleado')
                    ->toArray()
                : [];

            return array_values(array_unique(array_map(
                'intval',
                array_merge($subordinados, $delDepartamento)
            )));
        } catch (\Throwable $e) {
            Log::warning('SAM no disponible para filtro de autorizador: ' . $e->getMessage());
            return [];
        }
    }
}