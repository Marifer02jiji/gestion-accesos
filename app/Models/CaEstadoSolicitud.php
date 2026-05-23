<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaEstadoSolicitud extends Model
{
    protected $table = 'ca_estado_solicitud';
    protected $primaryKey = 'id_estado';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}