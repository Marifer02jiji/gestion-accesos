<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Models/CaTipoSolicitud.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, catálogo de tipos de solicitud (Proveedor, Institucional, Personal, Consulta)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaTipoSolicitud extends Model
{
    protected $table = 'ca_TipoSolicitud';
    protected $primaryKey = 'id_tipo_solicitud';
    public $timestamps = false;
    protected $fillable = ['nombre'];
}