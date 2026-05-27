<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaExclusion extends Model
{
    protected $table = 'lista_exclusion';
    protected $primaryKey = 'id_lista_exclusion';
    public $timestamps = false;

    protected $fillable = [
        'id_visitante',
        'id_autorizador',
        'motivo_exclusion',
        'fecha_bloqueo',
    ];

    public function visitante()
    {
        return $this->belongsTo(Visitante::class, 'id_visitante', 'id_visitante');
    }
}
