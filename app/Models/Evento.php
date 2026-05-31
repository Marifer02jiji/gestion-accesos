<?php

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