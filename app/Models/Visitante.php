<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitante extends Model
{
    protected $table = 'visitante';
    protected $primaryKey = 'id_visitante';
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'apellidos',
        'correo_personal',
        'id_estado_visitante',
    ];
}