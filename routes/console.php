<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     routes/console.php
 * Creación:    19/05/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Configuración inicial del scheduler para notificar tolerancia vencida y cierre de institución cada minuto
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('notificar:tolerancia-vencida')->everyMinute();
Schedule::command('notificar:cierre-institucion')->everyMinute();
