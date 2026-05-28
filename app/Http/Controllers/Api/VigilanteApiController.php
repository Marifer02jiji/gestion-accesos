<?php
// =============================================================================
// Proyecto  : Sistema de Gestión de Accesos y Visitas
// Archivo   : VigilanteApiController.php
// Módulo    : App\Http\Controllers\Api
// Autor     : Omega Company
// Fecha     : 2026-05-27
// Versión   : 2.0.0
// Descripción: El vigilante NO existe en ninguna tabla.
//              Su teléfono y área solo se almacenan en registroacceso
//              para identificar quién registró cada entrada/salida.
//              No hay autenticación — todas las rutas son públicas.
// =============================================================================

namespace App\Http\Controllers\Api;

use App\Models\QR;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VigilanteApiController extends Controller
{
    // =========================================================================
    // "LOGIN" — solo valida formato, no consulta BD
    // POST /api/vigilante/login
    // Body: { "telefono": "1234567890", "area": "Entrada vehicular 1" }
    //
    // Flutter guarda teléfono y área localmente.
    // No se crea ningún registro aquí.
    // =========================================================================
// =========================================================================
    // "LOGIN" — solo valida formato, no consulta BD
    // POST /api/vigilante/login
    // =========================================================================
    public function login(Request $request)
    {
        // Forzamos la validación manual para evitar redirecciones raras (Error 500)
        $validador = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'telefono' => ['required', 'string', 'digits:10'],
            'area'     => ['required', 'string', 'max:100'],
        ]);

        // Si los datos enviados desde Flutter no cumplen con el formato, mandamos la respuesta correcta
        if ($validador->fails()) {
            return response()->json([
                'message' => 'Los datos provistos son inválidos.',
                'errors'  => $validador->errors()
            ], 422);
        }

        // Si la validación pasa, devolvemos el payload para Flutter
        return response()->json([
            'message' => 'Identificación registrada.',
            'data' => [
                'token'           => 'vigilante-local',
                'rol'             => 'vigilante',
                'nombre'          => 'Vigilante',
                'name'            => 'Vigilante',
                'email'           => '',
                'departamento'    => $request->area,
                'id_empleado_sam' => 0,
                'id_departamento' => 0,
                'rol_api'         => 'vigilante',
                'telefono'        => $request->telefono,
                'area'            => $request->area,
            ],
        ]);
    }    

    // =========================================================================
    // VISITAS DEL DÍA
    // GET /api/vigilante/visitas-hoy
    // =========================================================================
    public function visitasHoy()
    {
        $visitas = Solicitud::with(['visitantes', 'solicitudVisitantes.qr'])
            ->where('id_estado_solicitud', 2)
            ->whereDate('fecha_inicio', today())
            ->get()
            ->map(function ($solicitud) {
                $registro = RegistroAcceso::whereHas(
                    'qr.solicitudVisitante.solicitud',
                    fn($q) => $q->where('id_solicitud', $solicitud->id_solicitud)
                )
                    ->orderByDesc('id_registro')
                    ->first();

                $estado = 'autorizada';
                if ($registro?->hora_llegada_institucion && !$registro?->hora_salida_institucion) {
                    $estado = 'dentro';
                } elseif ($registro?->hora_salida_institucion) {
                    $estado = 'salio';
                }

                return [
                    'id_solicitud'    => $solicitud->id_solicitud,
                    'folio'           => $solicitud->folio,
                    'lugar_encuentro' => $solicitud->lugar_encuentro,
                    'hora_inicio'     => $solicitud->fecha_inicio,
                    'estado'          => $estado,
                    'visitantes'      => $solicitud->visitantes->map(fn($v) => [
                        'nombre'    => $v->nombre,
                        'apellidos' => $v->apellidos,
                    ]),
                ];
            });

        return response()->json(['data' => $visitas]);
    }

    // =========================================================================
    // ESCANEAR QR
    // POST /api/vigilante/escanear
    // Body: { "codigo_qr": "VIS-0000-0000" }
    // =========================================================================
    public function escanear(Request $request)
    {
        $request->validate(['codigo_qr' => 'required|string']);

        $qr = QR::where('codigo_numerico', $request->codigo_qr)
            ->with([
                'solicitudVisitante.visitante',
                'solicitudVisitante.solicitud',
            ])
            ->first();

        if (!$qr) {
            return response()->json(['message' => 'Código QR no encontrado.'], 404);
        }

        if ($qr->id_estadoQr == 4) {
            return response()->json(['message' => 'Este código QR fue cancelado.'], 422);
        }

        if (now() < $qr->vigencia_inicio || now() > $qr->vigencia_final) {
            return response()->json(['message' => 'El QR ha expirado o aún no es válido.'], 422);
        }

        // Determinar si la acción disponible es entrada o salida
        $registro = RegistroAcceso::where('id_qr', $qr->id_qr)
            ->orderByDesc('id_registro')
            ->first();

        $accionDisponible = 'entrada';
        if ($registro?->hora_llegada_institucion && !$registro?->hora_salida_institucion) {
            $accionDisponible = 'salida';
        }

        $visitante = $qr->solicitudVisitante->visitante;
        $solicitud = $qr->solicitudVisitante->solicitud;

        return response()->json([
            'data' => [
                'id_qr'             => $qr->id_qr,
                'accion_disponible' => $accionDisponible,
                'visitante' => [
                    'nombre'          => $visitante->nombre,
                    'apellidos'       => $visitante->apellidos,
                    'correo_personal' => $visitante->correo_personal,
                ],
                'solicitud' => [
                    'motivo_visita'   => $solicitud->motivo_visita,
                    'vigencia_inicio' => $qr->vigencia_inicio,
                    'vigencia_final'  => $qr->vigencia_final,
                ],
            ],
        ]);
    }

    // =========================================================================
    // REGISTRAR ENTRADA
    // POST /api/vigilante/entrada
    // Body: { "id_qr": 1, "telefono": "1234567890", "area": "Entrada vehicular 1" }
    // El teléfono y área vienen de Flutter (guardados localmente al identificarse)
    // =========================================================================
    public function registrarEntrada(Request $request)
    {
        $request->validate([
            'id_qr'    => 'required|integer',
            'telefono' => 'required|string|digits:10',
            'area'     => 'required|string|max:100',
        ]);

        $qr = QR::findOrFail($request->id_qr);

        $entradaActiva = RegistroAcceso::where('id_qr', $qr->id_qr)
            ->whereNotNull('hora_llegada_institucion')
            ->whereNull('hora_salida_institucion')
            ->exists();

        if ($entradaActiva) {
            return response()->json([
                'message' => 'Este visitante ya tiene una entrada activa sin salida.',
            ], 422);
        }

        RegistroAcceso::create([
            'hora_llegada_institucion'   => now(),
            'id_qr'                      => $qr->id_qr,
            'telefono_vigilante_entrada' => $request->telefono,
            'caseta_entrada'             => $request->area,
        ]);

        $qr->update(['id_estadoQr' => 3]);

        return response()->json(['message' => 'Entrada registrada correctamente.']);
    }

    // =========================================================================
    // REGISTRAR SALIDA
    // POST /api/vigilante/salida
    // Body: { "id_qr": 1, "telefono": "1234567890", "area": "Entrada vehicular 1" }
    // =========================================================================
    public function registrarSalida(Request $request)
    {
        $request->validate([
            'id_qr'    => 'required|integer',
            'telefono' => 'required|string|digits:10',
            'area'     => 'required|string|max:100',
        ]);

        $registro = RegistroAcceso::where('id_qr', $request->id_qr)
            ->whereNull('hora_salida_institucion')
            ->orderByDesc('id_registro')
            ->first();

        if (!$registro) {
            return response()->json([
                'message' => 'No se encontró entrada registrada para este QR.',
            ], 404);
        }

        $registro->update([
            'hora_salida_institucion'   => now(),
            'telefono_vigilante_salida' => $request->telefono,
            'caseta_salida'             => $request->area,
        ]);

        return response()->json(['message' => 'Salida registrada correctamente.']);
    }

    // =========================================================================
    // HISTORIAL
    // GET /api/vigilante/historial
    // =========================================================================
    public function historial()
    {
        $registros = RegistroAcceso::with(['qr.solicitudVisitante.visitante'])
            ->orderByDesc('hora_llegada_institucion')
            ->paginate(15);

        $data = $registros->map(function ($r) {
            $visitante = $r->qr?->solicitudVisitante?->visitante;
            return [
                'id_registro'             => $r->id_registro,
                'visitante'               => $visitante
                    ? $visitante->nombre . ' ' . $visitante->apellidos
                    : 'Desconocido',
                'hora_llegada_institucion'=> $r->hora_llegada_institucion,
                'hora_salida_institucion' => $r->hora_salida_institucion,
                'caseta_entrada'          => $r->caseta_entrada,
                'caseta_salida'           => $r->caseta_salida,
            ];
        });

        return response()->json([
            'data'         => $data,
            'current_page' => $registros->currentPage(),
            'last_page'    => $registros->lastPage(),
        ]);
    }
}