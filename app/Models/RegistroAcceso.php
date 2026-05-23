<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroAcceso extends Model
{
    protected $table = 'RegistroAcceso';
    protected $primaryKey = 'id_registro';
    public $timestamps = false;

    protected $fillable = [
        'hora_llegada_institucion',
        'hora_llegada_encuentro',
        'hora_salida_encuentro',
        'hora_salida_institucion',
        'observaciones',
        'id_vigilante_entrada',
        'id_vigilante_salida',
        'id_qr',
    ];

    public function qr()
    {
        return $this->belongsTo(QR::class, 'id_qr', 'id_qr');
    }
}