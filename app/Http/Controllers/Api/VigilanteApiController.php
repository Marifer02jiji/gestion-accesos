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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QR;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class VigilanteApiController extends Controller
{
    // Visitas autorizadas del día
    public function visitasHoy()
    {
        $visitas = Solicitud::with(['visitantes', 'solicitudVisitantes.qr'])
            ->where('id_estado_solicitud', 2)
            ->whereDate('fecha_inicio', today())
            ->get();

        return response()->json([
            'message' => 'Visitas del día obtenidas correctamente.',
            'data'    => $visitas,
        ]);
    }

    // Escanear QR — solo valida, no registra
    public function escanear(Request $request)
    {
        $request->validate(['codigo_qr' => 'required|string']);

        $qr = QR::where('codigo_numerico', $request->codigo_qr)
            ->with('solicitudVisitante.visitante', 'solicitudVisitante.solicitud')
            ->first();

        if (!$qr) {
            return response()->json([
                'message' => 'No se encontraron registros.',
                'data'    => null,
            ], 404);
        }

        if ($qr->id_estadoQr == 4) {
            return response()->json([
                'message' => 'El código QR fue cancelado.',
                'data'    => null,
            ], 422);
        }

        if (now() < $qr->vigencia_inicio || now() > $qr->vigencia_final) {
            return response()->json([
                'message' => 'El código QR ha expirado.',
                'data'    => null,
            ], 422);
        }

        return response()->json([
            'message' => 'QR válido.',
            'data'    => $qr,
        ]);
    }

    // Registrar entrada del visitante
    public function registrarEntrada(Request $request)
    {
        $request->validate([
            'id_qr'              => 'required|integer',
            'telefono_vigilante' => 'nullable|string|max:15',
            'area_vigilante'     => 'nullable|string|max:100',
        ]);

        $qr = QR::findOrFail($request->id_qr);

        RegistroAcceso::create([
            'hora_llegada_institucion' => now(),
            'id_qr'                    => $qr->id_qr,
            'telefono_vigilante'       => $request->telefono_vigilante,
            'area_vigilante'           => $request->area_vigilante,
        ]);

        $qr->update(['id_estadoQr' => 3]); // Usado

        $visitante = $qr->solicitudVisitante->visitante ?? null;

        return response()->json([
            'message' => 'Entrada registrada correctamente.',
            'data'    => [
                'nombre' => $visitante ? $visitante->nombre . ' ' . $visitante->apellidos : 'Visitante',
                'hora'   => now()->format('H:i:s'),
            ],
        ]);
    }

    // Registrar salida del visitante
    public function registrarSalida(Request $request)
    {
        $request->validate([
            'id_qr'              => 'required|integer',
            'telefono_vigilante' => 'nullable|string|max:15',
            'area_vigilante'     => 'nullable|string|max:100',
        ]);

        $registro = RegistroAcceso::where('id_qr', $request->id_qr)
            ->whereNull('hora_salida_institucion')
            ->latest()
            ->first();

        if (!$registro) {
            return response()->json([
                'message' => 'No se encontró entrada registrada para este QR.',
                'data'    => null,
            ], 404);
        }

        $registro->update([
            'hora_salida_institucion' => now(),
            'telefono_vigilante'      => $request->telefono_vigilante ?? $registro->telefono_vigilante,
            'area_vigilante'          => $request->area_vigilante ?? $registro->area_vigilante,
        ]);

        $qr = QR::with('solicitudVisitante.visitante')->find($request->id_qr);
        $visitante = $qr->solicitudVisitante->visitante ?? null;

        return response()->json([
            'message' => 'Salida registrada correctamente.',
            'data'    => [
                'nombre' => $visitante ? $visitante->nombre . ' ' . $visitante->apellidos : 'Visitante',
                'hora'   => now()->format('H:i:s'),
            ],
        ]);
    }

    // Historial de accesos
    public function historial()
    {
        $registros = RegistroAcceso::with(['qr.solicitudVisitante.visitante'])
            ->orderBy('hora_llegada_institucion', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'Historial obtenido correctamente.',
            'data'    => $registros,
        ]);
    }
}