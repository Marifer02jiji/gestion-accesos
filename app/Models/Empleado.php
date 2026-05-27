<?php

/**
 * Empresa: OMEGA
 * Proyecto: Sistema de Gestión de Accesos
 * Creación: 07/05/2026
 * Creado por: Desarrollador
 * Aprobado por: Líder del Área
 *
 * Changelog:
 * ID: 1 | Fecha: 07/05/2026 | Modificado por: Desarrollador | Descripción: Creación inicial
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $connection = 'sam';
    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;

    protected $fillable = [
        'usuario',
        'password',
        'credenciales',
        'estatus',
    ];

    // ← Se elimina $hidden para que el password sea accesible en el controlador
    // Si necesitas ocultarlo en respuestas JSON públicas, hazlo explícitamente ahí
}
/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $connection = 'sam';
    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;

    protected $fillable = [
        'usuario',
        'password',
        'credenciales',
        'estatus',
    ];

    protected $hidden = [
        'password',
    ];
}*/