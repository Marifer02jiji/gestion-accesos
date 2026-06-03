<?php

namespace App\Services;

use App\Models\ListaExclusion;
use App\Models\Visitante;
use Illuminate\Http\JsonResponse;

class ListaExclusionVisitaService
{
    public const CODIGO_EXCLUSION_LISTA = 'EXCLUSION_LISTA';

    public function estaEnExclusion(?Visitante $visitante): bool
    {
        if (!$visitante?->id_visitante) {
            return false;
        }

        return ListaExclusion::where('id_visitante', $visitante->id_visitante)->exists();
    }

    public function datosPorCorreo(string $correo): array
    {
        $correoNorm = strtolower(trim($correo));
        $visitante  = Visitante::where('correo_personal', $correoNorm)->first();

        if (!$visitante) {
            return [
                'existe'             => false,
                'nombre'             => '',
                'apellidos'          => '',
                'correo'             => $correoNorm,
                'en_lista_exclusion' => false,
            ];
        }

        return [
            'existe'             => true,
            'nombre'             => (string) $visitante->nombre,
            'apellidos'          => (string) $visitante->apellidos,
            'correo'             => (string) $visitante->correo_personal,
            'en_lista_exclusion' => $this->estaEnExclusion($visitante),
        ];
    }

    public function nombresExcluidosEnPayload(array $visitantes): array
    {
        $excluidos = [];

        foreach ($visitantes as $v) {
            $correo = strtolower(trim((string) ($v['correo'] ?? '')));
            if ($correo === '') {
                continue;
            }

            $visitante = Visitante::where('correo_personal', $correo)->first();
            if (!$visitante || !$this->estaEnExclusion($visitante)) {
                continue;
            }

            $nombre = trim(
                ((string) ($v['nombre'] ?? '')) !== ''
                    ? (string) $v['nombre']
                    : (string) $visitante->nombre
            );
            $apellidos = trim(
                ((string) ($v['apellidos'] ?? '')) !== ''
                    ? (string) $v['apellidos']
                    : (string) $visitante->apellidos
            );
            $etiqueta = trim("{$nombre} {$apellidos}");
            if ($etiqueta === '') {
                $etiqueta = $correo;
            }

            if (!in_array($etiqueta, $excluidos, true)) {
                $excluidos[] = $etiqueta;
            }
        }

        return $excluidos;
    }

    public function respuesta422SiExcluidos(array $visitantes, string $mensajePlantilla): ?JsonResponse
    {
        $excluidos = $this->nombresExcluidosEnPayload($visitantes);
        if ($excluidos === []) {
            return null;
        }

        return response()->json([
            'message'              => sprintf($mensajePlantilla, implode(', ', $excluidos)),
            'codigo'               => self::CODIGO_EXCLUSION_LISTA,
            'visitantes_excluidos' => $excluidos,
        ], 422);
    }

    public function respuesta422VisitanteConsulta(Visitante $visitante): ?JsonResponse
    {
        if (!$this->estaEnExclusion($visitante)) {
            return null;
        }

        $nombre = trim($visitante->nombre . ' ' . $visitante->apellidos);

        return response()->json([
            'message' => "No se puede registrar la visita. {$nombre} esta en la lista de exclusion.",
            'codigo'  => self::CODIGO_EXCLUSION_LISTA,
        ], 422);
    }
}
