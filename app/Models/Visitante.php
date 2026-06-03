<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Models/Visitante.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, modelo de visitante con nombre, apellidos y correo
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Usar firstOrCreate y updateOrCreate para evitar duplicados por correo
 */

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