<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table      = 'solicitud';
    protected $primaryKey = 'id_solicitud';
    public    $timestamps = false;

    protected $fillable = [
        'folio',
        'fecha_inicio',
        'tolerancia_antes',
        'tolerancia_despues',
        'lugar_encuentro',
        'numero_visitantes',
        'motivo_visita',
        'id_estado_solicitud',
        'id_tipo_solicitud',
        'id_autorizador',
        'id_solicitante',
        'cancelado_por',
        'fecha_cancelacion',
    ];

    // ─── Relaciones ──────────────────────────────────────────────

    public function estado()
    {
        return $this->belongsTo(CaEstadoSolicitud::class, 'id_estado_solicitud', 'id_estado');
    }

    public function tipo()
    {
        return $this->belongsTo(CaTipoSolicitud::class, 'id_tipo_solicitud', 'id_tipo_solicitud');
    }

    public function visitantes()
    {
        return $this->belongsToMany(
            Visitante::class,
            'solicitud_visitante',
            'id_solicitud',
            'id_visitante'
        );
    }

    public function solicitudVisitantes()
    {
        return $this->hasMany(SolicitudVisitante::class, 'id_solicitud', 'id_solicitud');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'id_solicitante', 'id_empleado_sam');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    // Genera folio único VIS-XXXX-XXXX
    public static function generarFolio(): string
    {
        do {
            $parte1 = strtoupper(substr(str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT), 0, 4));
            $parte2 = strtoupper(substr(str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT), 0, 4));
            $folio  = "VIS-{$parte1}-{$parte2}";
        } while (self::where('folio', $folio)->exists());

        return $folio;
    }

    public function esCancelable(): bool
    {
        return in_array($this->id_estado_solicitud, [1, 2]);
    }
}