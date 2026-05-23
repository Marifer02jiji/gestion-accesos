<?php

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
