<?php

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
}