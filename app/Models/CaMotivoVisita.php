<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaMotivoVisita extends Model
{
    protected $table      = 'ca_motivo_visita';
    protected $primaryKey = 'id_motivo';
    public    $timestamps = false;

    protected $fillable = ['nombre', 'activo'];

    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }
}