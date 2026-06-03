<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Rules/AnticipacionMinimaVisita.php
 * Creación:    28/05/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, regla de validación de anticipación mínima de 1 hora para solicitudes
 */

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AnticipacionMinimaVisita implements ValidationRule
{
    public function __construct(private int $horas = 1) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        $zona = config('app.timezone');

        // datetime-local envía hora local sin zona; interpretar en la zona de la app
        $fechaCita = Carbon::parse($value, $zona);
        $minimaPermitida = now($zona)->addHours($this->horas);

        if ($fechaCita->lt($minimaPermitida)) {
            $fail("La cita debe ser a las {$minimaPermitida->format('H:i')} o después (mínimo {$this->horas} hora de anticipación).");
        }
    }
}
