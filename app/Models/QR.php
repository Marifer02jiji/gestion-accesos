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
}