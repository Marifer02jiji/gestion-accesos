<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Console/Commands/NotificarCierreInstitucion.php
 * Creación:    19/05/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, scheduler cada minuto para notificar visitantes dentro fuera de horario institucional
 */

namespace App\Console\Commands;

use App\Models\Notificacion;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotificarCierreInstitucion extends Command
{
    protected $signature   = 'notificar:cierre-institucion';
    protected $description = 'Notifica si hay visitantes dentro después de hora de cierre';

    public function handle()
    {
        $ahora     = Carbon::now();
        $diaSemana = $ahora->dayOfWeek;
        $hora      = $ahora->hour;

        // Verificar si es hora de cierre
        $esCierre = false;

        if ($diaSemana >= 1 && $diaSemana <= 5 && $hora >= 21) {
            $esCierre = true; // Lunes a viernes después de 9pm
        } elseif ($diaSemana === 6 && $hora >= 14) {
            $esCierre = true; // Sábado después de 2pm
        }

        if (!$esCierre) {
            $this->info('No es hora de cierre.');
            return;
        }

        // Buscar visitantes que siguen dentro
        $registros = RegistroAcceso::whereNotNull('hora_llegada_institucion')
            ->whereNull('hora_salida_institucion')
            ->with('qr.solicitudVisitante.solicitud')
            ->get();

        foreach ($registros as $registro) {
            $solicitud = $registro->qr?->solicitudVisitante?->solicitud;
            if (!$solicitud) continue;

            $yaNotificado = Notificacion::where('id_solicitud', $solicitud->id_solicitud)
                ->where('tipo', 'cierre_institucion')
                ->exists();

            if ($yaNotificado) continue;

            Notificacion::create([
                'id_empleado'  => $solicitud->id_solicitante,
                'id_solicitud' => $solicitud->id_solicitud,
                'tipo'         => 'cierre_institucion',
                'mensaje'      => "La institución está cerrando y tu visitante ({$solicitud->folio}) aún no ha salido.",
                'leida'        => false,
            ]);
        }

        $this->info("Notificaciones de cierre enviadas: {$registros->count()}");
    }
}