<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Models/ListaExclusion.php
 * Creación:    07/05/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, modelo de lista de exclusión con relación a visitante
 * ID: 2 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar fecha_bloqueo y motivo_exclusion al fillable
 */

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
