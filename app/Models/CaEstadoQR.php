<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaEstadoQR extends Model
{
    protected $table = 'ca_estadoQR';
    protected $primaryKey = 'id_estadoQR';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}