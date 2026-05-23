<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaTipoSolicitud extends Model
{
    protected $table = 'ca_TipoSolicitud';
    protected $primaryKey = 'id_tipo_solicitud';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}