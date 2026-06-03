<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Models/Evento.php
 * Creación:    28/05/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, modelo de evento grupal con relación a QR
 * ID: 2 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar método generarFolio() con formato EVT-XXXX-XXXX
 * ID: 3 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar campos tolerancia_antes y tolerancia_despues al fillable
 * ID: 4 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar campo tipo_evento al fillable
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table      = 'evento';
    protected $primaryKey = 'id_evento';
    public    $timestamps = false;

    protected $fillable = [
        'folio',
        'tipo_evento',
        'descripcion',
        'lugar',
        'fecha_evento',
        'numero_personas',
        'correo_responsable',
        'nombre_responsable',
        'id_organizador',
        'id_estado_solicitud',
        'id_qr',
        'fecha_creacion',
        'tolerancia_antes',
        'tolerancia_despues',
    ];

    public function qr()
    {
        return $this->belongsTo(QR::class, 'id_qr', 'id_qr');
    }

    public static function generarFolio(): string
    {
        $folio = 'EVT-' . strtoupper(substr(uniqid(), -4)) . '-' . rand(1000, 9999);
        while (self::where('folio', $folio)->exists()) {
            $folio = 'EVT-' . strtoupper(substr(uniqid(), -4)) . '-' . rand(1000, 9999);
        }
        return $folio;
    }
}