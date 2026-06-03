<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Models/Notificacion.php
 * Creación: 07/05/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder del Área
 *
 * Changelog:
 * ID: 1 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, modelo de notificación con tipos pendiente, autorizada, rechazada, entrada, salida, encuentro
 * ID: 2 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar tipos tolerancia_vencida y cierre_institucion para scheduler
 * ID: 3 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar notificación especial cuando vigilante registra salida sin estados intermedios del anfitrión
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'Notificacion';
    protected $primaryKey = 'id_notificaciones';
    public $timestamps = false;

    protected $fillable = [
        'id_empleado',
        'id_solicitud',
        'tipo',
        'mensaje',
        'leida',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud', 'id_solicitud');
    }
}