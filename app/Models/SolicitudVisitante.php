<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Models/SolicitudVisitante.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, modelo tabla intermedia solicitud-visitante
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar relaciones visitante() y solicitud() belongsTo
 * ID: 3 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar relación qr() hasOne
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudVisitante extends Model
{
    protected $table = 'solicitud_visitante';
    protected $primaryKey = 'id_solicitud_visitante';
    public $timestamps = false;

    protected $fillable = [
        'id_visitante',
        'id_solicitud',
    ];

    public function visitante()
    {
        return $this->belongsTo(Visitante::class, 'id_visitante', 'id_visitante');
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud', 'id_solicitud');
    }


public function qr()
{
    return $this->hasOne(QR::class, 'id_solicitud_visitante', 'id_solicitud_visitante');
}

}
