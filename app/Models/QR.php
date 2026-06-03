<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Models/QR.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, modelo QR con relación a solicitud_visitante
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar método generarCodigo() con formato VIS-XXXX-XXXX único
 * ID: 3 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar relación registroAcceso() hasMany
 * ID: 4 | Fecha: 31/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar soporte para id_solicitud_visitante nullable para eventos
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QR extends Model
{
    protected $table = 'QR';
    protected $primaryKey = 'id_qr';
    public $timestamps = false;

    protected $fillable = [
        'codigo_numerico',
        'vigencia_inicio',
        'vigencia_final',
        'prorroga_tolerancia',
        'id_estadoQr',
        'id_solicitud_visitante',
    ];

    public function solicitudVisitante()
    {
        return $this->belongsTo(SolicitudVisitante::class, 'id_solicitud_visitante', 'id_solicitud_visitante');
    }

    // Código QR de acceso: VIS-XXXX-XXXX
    public static function generarCodigo(): string
    {
        do {
            $parte1 = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $parte2 = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $codigo = "VIS-{$parte1}-{$parte2}";
        } while (self::where('codigo_numerico', $codigo)->exists());

        return $codigo;
    }


    public function registroAcceso()
    {
        return $this->hasMany(RegistroAcceso::class, 'id_qr', 'id_qr');
    }
}