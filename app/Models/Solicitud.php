<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Models/Solicitud.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, modelo de solicitud de visita con relaciones
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar método generarFolio() con formato VIS-XXXX-XXXX
 * ID: 3 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar método esCancelable() para validar estados cancelables
 * ID: 4 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar columnas hora_llegada_encuentro y hora_salida_encuentro
 */

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
        'hora_llegada_encuentro',
        'hora_salida_encuentro',
        'reenvios_qr',
        'encuentro_sin_marcar_solicitante',
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

    public function autorizador()
    {
        return $this->belongsTo(User::class, 'id_autorizador', 'id_empleado_sam');
    }

    public function resolverAutorizador(): ?User
    {
        if (!$this->id_autorizador) {
            return null;
        }

        if ($this->relationLoaded('autorizador') && $this->autorizador) {
            return $this->autorizador;
        }

        $porSam = User::where('id_empleado_sam', $this->id_autorizador)->first();
        if ($porSam) {
            return $porSam;
        }

        return User::find($this->id_autorizador);
    }

    public function nombreAutorizador(): string
    {
        return $this->infoAutorizador()['texto'];
    }

    /**
     * @return array{texto: string, motivo: string|null}
     */
    public function infoAutorizador(): array
    {
        $estado = (int) $this->id_estado_solicitud;

        if (!$this->id_autorizador) {
            if (in_array($estado, [2, 5, 6, 7, 8], true)) {
                return [
                    'texto'  => '—',
                    'motivo' => 'Autorizada sin registrar al autorizador en el sistema',
                ];
            }

            return ['texto' => '—', 'motivo' => null];
        }

        $user = $this->resolverAutorizador();
        if ($user) {
            return ['texto' => $user->name, 'motivo' => null];
        }

        return [
            'texto'  => '—',
            'motivo' => 'El autorizador ya no está vinculado a un usuario del sistema',
        ];
    }

    public function esVisitaEstandar(): bool
    {
        $this->loadMissing('tipo');

        $id = (int) $this->id_tipo_solicitud;
        if (in_array($id, [3, 4], true)) {
            return false;
        }

        $nombre = strtolower($this->tipo->nombre ?? '');

        return !str_contains($nombre, 'consulta') && !str_contains($nombre, 'evento');
    }

    public function solicitanteNoMarcoEncuentro(): bool
    {
        return (bool) $this->encuentro_sin_marcar_solicitante;
    }

    // ─── Helpers ─────────────────────────────────────────────────

    // Folio de la solicitud: XXXX-XXXX (solo números)
    public static function generarFolio(): string
    {
        do {
            $parte1 = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $parte2 = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $folio  = "{$parte1}-{$parte2}";
        } while (self::where('folio', $folio)->exists());

        return $folio;
    }

    public function esCancelable(): bool
    {
        return in_array($this->id_estado_solicitud, [1, 2]);
    }

    public function scopeFiltrarPendientesAutorizador($query, array $filtros)
    {
        if (!empty($filtros['solicitante'])) {
            $term = $filtros['solicitante'];
            $query->whereHas('solicitante', function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%');
            });
        }

        if (!empty($filtros['correo'])) {
            $term = $filtros['correo'];
            $query->where(function ($q) use ($term) {
                $q->whereHas('visitantes', function ($v) use ($term) {
                    $v->where('correo_personal', 'like', '%' . $term . '%');
                })->orWhereHas('solicitante', function ($s) use ($term) {
                    $s->where('email', 'like', '%' . $term . '%');
                });
            });
        }

        if (!empty($filtros['fecha'])) {
            $query->whereDate('fecha_inicio', $filtros['fecha']);
        }

        if (!empty($filtros['hora'])) {
            $query->whereRaw("DATE_FORMAT(fecha_inicio, '%H:%i') = ?", [$filtros['hora']]);
        }

        return $query;
    }

    public function scopeFiltrarReporteSolicitudes($query, array $filtros)
    {
        if (!empty($filtros['estado'])) {
            $query->where('id_estado_solicitud', $filtros['estado']);
        }

        if (!empty($filtros['solicitante'])) {
            $term = $filtros['solicitante'];
            $query->whereHas('solicitante', function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%');
            });
        }

        if (!empty($filtros['correo'])) {
            $term = $filtros['correo'];
            $query->whereHas('visitantes', function ($v) use ($term) {
                $v->where('correo_personal', 'like', '%' . $term . '%');
            });
        }

        if (!empty($filtros['autorizador'])) {
            $term = $filtros['autorizador'];
            $query->whereHas('autorizador', function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%');
            });
        }

        if (!empty($filtros['tipo'])) {
            $query->where('id_tipo_solicitud', $filtros['tipo']);
        }

        if (!empty($filtros['fecha'])) {
            $query->whereDate('fecha_inicio', $filtros['fecha']);
        }

        if (!empty($filtros['hora'])) {
            $query->whereRaw("DATE_FORMAT(fecha_inicio, '%H:%i') = ?", [$filtros['hora']]);
        }

        if (!empty($filtros['desde'])) {
            $query->whereDate('fecha_inicio', '>=', $filtros['desde']);
        }

        if (!empty($filtros['hasta'])) {
            $query->whereDate('fecha_inicio', '<=', $filtros['hasta']);
        }

        return $query;
    }
}