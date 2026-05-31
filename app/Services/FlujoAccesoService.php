<?php

namespace App\Services;

use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Orden de estados de visita en campus:
 * Autorizada(2) → En Institución(5) → En Encuentro(6) → En Tránsito a Salida(7) → Finalizada(8)
 */
class FlujoAccesoService
{
    public const ESTADO_AUTORIZADA          = 2;
    public const ESTADO_EN_INSTITUCION      = 5;
    public const ESTADO_EN_ENCUENTRO        = 6;
    public const ESTADO_EN_TRANSITO_SALIDA  = 7;
    public const ESTADO_FINALIZADA          = 8;

    public function registrarLlegadaEncuentro(Solicitud $solicitud, ?RegistroAcceso $registro = null): void
    {
        $estado = (int) $solicitud->id_estado_solicitud;

        if ($estado !== self::ESTADO_EN_INSTITUCION) {
            throw new \InvalidArgumentException(
                'No se puede registrar llegada al encuentro sin estar En Institución.'
            );
        }

        $ahora = now();

        DB::table('solicitud')
            ->where('id_solicitud', $solicitud->id_solicitud)
            ->update([
                'id_estado_solicitud'    => self::ESTADO_EN_ENCUENTRO,
                'hora_llegada_encuentro' => $ahora,
            ]);

        if ($registro) {
            DB::table('registroacceso')
                ->where('id_registro', $registro->id_registro)
                ->update(['hora_llegada_encuentro' => $ahora]);
        }

        $solicitud->refresh();
    }

    public function registrarSalidaEncuentro(Solicitud $solicitud, ?RegistroAcceso $registro = null): void
    {
        $estado = (int) $solicitud->id_estado_solicitud;

        if ($estado !== self::ESTADO_EN_ENCUENTRO) {
            throw new \InvalidArgumentException(
                'No se puede registrar salida del encuentro sin estar En Encuentro.'
            );
        }

        $ahora = now();

        DB::table('solicitud')
            ->where('id_solicitud', $solicitud->id_solicitud)
            ->update([
                'id_estado_solicitud'   => self::ESTADO_EN_TRANSITO_SALIDA,
                'hora_salida_encuentro' => $ahora,
            ]);

        if ($registro) {
            DB::table('registroacceso')
                ->where('id_registro', $registro->id_registro)
                ->update(['hora_salida_encuentro' => $ahora]);
        }

        $solicitud->refresh();
    }

    /**
     * Salida vigilante: exige En Tránsito a Salida, salvo excepción con entrada institucional.
     *
     * @return string[] etiquetas de estados autocompletados
     */
    public function prepararSalidaVigilante(Solicitud $solicitud, RegistroAcceso $registro): array
    {
        if (!$registro->hora_llegada_institucion) {
            throw new \InvalidArgumentException(
                'No hay entrada institucional registrada para este QR.'
            );
        }

        $estado = (int) $solicitud->id_estado_solicitud;
        $autocompletados = [];

        if ($estado === self::ESTADO_FINALIZADA) {
            throw new \InvalidArgumentException('Esta visita ya fue finalizada.');
        }

        if ($estado === self::ESTADO_EN_TRANSITO_SALIDA) {
            return $autocompletados;
        }

        if ($estado === self::ESTADO_EN_INSTITUCION) {
            $this->registrarLlegadaEncuentro($solicitud, $registro);
            $autocompletados[] = 'en_encuentro';
            $estado = self::ESTADO_EN_ENCUENTRO;
        }

        if ($estado === self::ESTADO_EN_ENCUENTRO) {
            $this->registrarSalidaEncuentro($solicitud, $registro);
            $autocompletados[] = 'en_transito_salida';
            return $autocompletados;
        }

        throw new \InvalidArgumentException(
            'La salida del campus requiere que el visitante esté En Tránsito a Salida.'
        );
    }

    public function marcarEnInstitucion(Solicitud $solicitud): void
    {
        DB::table('solicitud')
            ->where('id_solicitud', $solicitud->id_solicitud)
            ->update(['id_estado_solicitud' => self::ESTADO_EN_INSTITUCION]);

        $solicitud->refresh();
    }

    public function marcarFinalizada(Solicitud $solicitud): void
    {
        DB::table('solicitud')
            ->where('id_solicitud', $solicitud->id_solicitud)
            ->update(['id_estado_solicitud' => self::ESTADO_FINALIZADA]);

        $solicitud->refresh();
    }

    public function registroActivoPorQr(int $idQr): ?RegistroAcceso
    {
        return RegistroAcceso::where('id_qr', $idQr)
            ->whereNotNull('hora_llegada_institucion')
            ->whereNull('hora_salida_institucion')
            ->orderByDesc('id_registro')
            ->first();
    }

    public function formatearVisitaActiva(Solicitud $solicitud): array
    {
        $solicitud->loadMissing(['visitantes', 'estado', 'solicitudVisitantes.qr.registroAcceso']);

        $visitante = $solicitud->visitantes->first();
        $nombre    = $visitante
            ? trim($visitante->nombre . ' ' . $visitante->apellidos)
            : 'Visitante';

        $registro = null;
        foreach ($solicitud->solicitudVisitantes as $sv) {
            if (!$sv->qr) {
                continue;
            }
            $candidato = RegistroAcceso::where('id_qr', $sv->qr->id_qr)
                ->orderByDesc('id_registro')
                ->first();
            if ($candidato) {
                $registro = $candidato;
                break;
            }
        }

        return [
            'id_solicitud'              => $solicitud->id_solicitud,
            'folio'                     => $solicitud->folio,
            'nombre_visitante'          => $nombre,
            'lugar_destino'             => $solicitud->lugar_encuentro,
            'estado'                    => $solicitud->estado->nombre ?? '',
            'id_estado_solicitud'       => (int) $solicitud->id_estado_solicitud,
            'hora_llegada_campus'       => $registro?->hora_llegada_institucion,
            'hora_llegada_area'         => $solicitud->hora_llegada_encuentro
                ?? $registro?->hora_llegada_encuentro,
            'hora_salida_area'          => $solicitud->hora_salida_encuentro
                ?? $registro?->hora_salida_encuentro,
            'hora_salida_campus'        => $registro?->hora_salida_institucion,
            'tiempo_permanencia_minutos'=> null,
        ];
    }

    public function logAutocompletado(int $idSolicitud, array $estados): void
    {
        if ($estados === []) {
            return;
        }

        Log::info('Flujo acceso: estados autocompletados antes de salida', [
            'id_solicitud' => $idSolicitud,
            'estados'      => $estados,
        ]);
    }
}
