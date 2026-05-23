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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QR;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VigilanteApiController extends Controller
{
    public function visitasHoy()
    {
        $visitas = Solicitud::with(['visitantes', 'estado'])
            ->where('id_estado_solicitud', 2)
            ->whereDate('fecha_inicio', today())
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        return response()->json([
            'message' => 'Visitas del día obtenidas correctamente.',
            'data'    => $visitas,
        ]);
    }

    public function escanear(Request $request)
    {
        $request->validate([
            'codigo_qr' => 'required|string',
        ]);

        $qr = QR::with([
            'solicitudVisitante.visitante',
            'solicitudVisitante.solicitud',
        ])->where('codigo_numerico', $request->codigo_qr)->first();

        if (!$qr) {
            return response()->json([
                'message' => 'No se encontraron registros.',
                'data'    => null,
            ], 404);
        }

        $ahora = now();
        if ($ahora < $qr->vigencia_inicio || $ahora > $qr->vigencia_final) {
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

    public function registrarEntrada(Request $request)
    {
        $request->validate([
            'id_qr' => 'required|integer',
        ]);

        $qr = QR::with('solicitudVisitante.visitante')->findOrFail($request->id_qr);

        RegistroAcceso::create([
            'hora_llegada_institucion' => now(),
            'id_vigilante_entrada'     => $request->user()->id,
            'id_qr'                    => $qr->id_qr,
        ]);

        $qr->update(['id_estadoQr' => 3]);

        return response()->json([
            'message' => 'Entrada registrada correctamente.',
            'data'    => [
                'nombre' => $qr->solicitudVisitante->visitante->nombre . ' ' .
                            $qr->solicitudVisitante->visitante->apellidos,
                'hora'   => now()->format('H:i:s'),
            ],
        ]);
    }

    public function registrarSalida(Request $request)
    {
        $request->validate([
            'id_qr' => 'required|integer',
        ]);

        $qr = QR::with('solicitudVisitante.visitante')->findOrFail($request->id_qr);

        $registro = RegistroAcceso::where('id_qr', $request->id_qr)
            ->whereNull('hora_salida_institucion')
            ->first();

        if ($registro) {
            $registro->update([
                'hora_salida_institucion' => now(),
                'id_vigilante_salida'     => $request->user()->id,
            ]);
        }

        return response()->json([
            'message' => 'Salida registrada correctamente.',
            'data'    => [
                'nombre' => $qr->solicitudVisitante->visitante->nombre . ' ' .
                            $qr->solicitudVisitante->visitante->apellidos,
                'hora'   => now()->format('H:i:s'),
            ],
        ]);
    }

    public function historial(Request $request)
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