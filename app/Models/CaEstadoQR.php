<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoQR extends Model
{
    protected $table      = 'estadoqr'; // ajusta al nombre real de tu tabla
    protected $primaryKey = 'id_estadoQr';
    public    $timestamps = false;

    // Constantes para no usar "magic strings" en el controlador
    const ACTIVO  = 1; // QR válido, visitante no ha entrado aún
    const DENTRO  = 2; // Visitante ya está dentro
    const SALIO   = 3; // Visitante ya salió
    const EXPIRADO = 4;
}