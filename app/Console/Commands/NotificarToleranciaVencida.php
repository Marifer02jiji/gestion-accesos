<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Console/Commands/NotificarToleranciaVencida.php
 * Creación:    19/05/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, scheduler cada minuto para notificar tolerancia vencida y cancelar solicitud automáticamente
 */

namespace App\Console\Commands;

use App\Models\Notificacion;
use App\Models\Solicitud;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotificarToleranciaVencida extends Command
{
    protected $signature   = 'notificar:tolerancia-vencida';
    protected $description = 'Notifica al solicitante cuando el visitante no llegó a tiempo';

    public function handle()
    {
        $ahora = Carbon::now();

        // Buscar solicitudes autorizadas cuya tolerancia ya venció (estado 2)
        $solicitudes = Solicitud::where('id_estado_solicitud', 2)
            ->get()
            ->filter(function ($s) use ($ahora) {
                $vencimiento = Carbon::parse($s->fecha_inicio)
                    ->addMinutes($s->tolerancia_despues ?? 15);
                return $ahora->gt($vencimiento);
            });

        foreach ($solicitudes as $solicitud) {
            // Verificar que no se haya notificado ya
            $yaNotificado = Notificacion::where('id_solicitud', $solicitud->id_solicitud)
                ->where('tipo', 'tolerancia_vencida')
                ->exists();

            if ($yaNotificado) continue;

            Notificacion::create([
                'id_empleado'  => $solicitud->id_solicitante,
                'id_solicitud' => $solicitud->id_solicitud,
                'tipo'         => 'tolerancia_vencida',
                'mensaje'      => "Tu visitante no llegó a tiempo. La tolerancia de la visita {$solicitud->folio} ha vencido.",
                'leida'        => false,
            ]);

            // Cancelar la solicitud automáticamente
            $solicitud->update([
                'id_estado_solicitud' => 4,
                'fecha_cancelacion'   => now(),
            ]);
        }

        $this->info("Tolerancias vencidas procesadas: {$solicitudes->count()}");
    }
}