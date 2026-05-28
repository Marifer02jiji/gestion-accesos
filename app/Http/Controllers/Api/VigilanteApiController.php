<?php
// =============================================================================
// Proyecto  : Sistema de Gestión de Accesos y Visitas
// Archivo   : VigilanteApiController.php
// Módulo    : App\Http\Controllers\Api
// Autor     : Omega Company
// Fecha     : 2026-05-28
// Versión   : 2.1.0
// Descripción: Controlador público para módulo de vigilante.
//              El vigilante NO existe en ninguna tabla.
//              Su teléfono y área solo se almacenan en registroacceso.
//              También permite registrar visitas espontáneas de consulta.
// =============================================================================

namespace App\Http\Controllers\Api;

use App\Models\QR;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VigilanteApiController extends Controller
{
    // =========================================================================
    // "LOGIN" — solo valida formato, no consulta BD
    // POST /api/vigilante/login
    // Body: { "telefono": "1234567890", "area": "Entrada vehicular 1" }
<<<<<<< HEAD
    //
    // Flutter guarda teléfono y área localmente.
    // No se crea ningún registro aquí.
    // =========================================================================
    
public function login(Request $request)
{
    $request->validate([
        'telefono' => 'required|digits:10',
        'area'     => 'required|string|max:100',
    ]);
=======
    // =========================================================================
    public function login(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'telefono' => ['required', 'string', 'digits:10'],
            'area'     => ['required', 'string', 'max:100'],
        ]);

        if ($validador->fails()) {
            return response()->json([
                'message' => 'Los datos provistos son inválidos.',
                'errors'  => $validador->errors(),
            ], 422);
        }

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
    // REGISTRAR VISITA DE CONSULTA
    // POST /api/vigilante/consulta
    // Body:
    // {
    //   "nombre_visitante": "Ana López García",
    //   "correo_visitante": "ana@gmail.com",
    //   "lugar_destino": "Desarrollo Académico"
    // }
    // =========================================================================
    public function registrarConsulta(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'nombre_visitante' => ['required', 'string', 'min:5', 'max:150'],
            'correo_visitante' => ['required', 'email', 'max:150'],
            'lugar_destino'    => ['required', 'string', 'max:100'],
        ]);

        if ($validador->fails()) {
            return response()->json([
                'message' => 'Los datos provistos son inválidos.',
                'errors'  => $validador->errors(),
            ], 422);
        }

        $lugaresPermitidos = [
            'División de Comunicación y Difusión',
            'Desarrollo Académico',
        ];

        if (!in_array($request->lugar_destino, $lugaresPermitidos)) {
            return response()->json([
                'message' => 'El lugar destino no es válido.',
            ], 422);
        }

        try {
            $resultado = DB::transaction(function () use ($request) {
                $nombreCompleto = trim($request->nombre_visitante);
                $partesNombre = preg_split('/\s+/', $nombreCompleto);

                $nombre = array_shift($partesNombre);
                $apellidos = trim(implode(' ', $partesNombre));

                if ($apellidos === '') {
                    $apellidos = 'No especificado';
                }

                $visitante = Visitante::create([
                    'nombre'              => $nombre,
                    'apellidos'           => $apellidos,
                    'correo_personal'     => $request->correo_visitante,
                    'id_estado_visitante' => 1,
                ]);

                $folio = Solicitud::generarFolio();

                $solicitud = Solicitud::create([
                    'folio'               => $folio,
                    'fecha_inicio'        => now(),
                    'tolerancia_antes'    => 0,
                    'tolerancia_despues'  => 120,
                    'lugar_encuentro'     => $request->lugar_destino,
                    'numero_visitantes'   => 1,
                    'motivo_visita'       => 'Visita espontánea de consulta',
                    'id_estado_solicitud' => 2,
                    'id_tipo_solicitud'   => 3,
                    'id_autorizador'      => null,
                    'id_solicitante'      => null,
                ]);

                $solicitudVisitante = SolicitudVisitante::create([
                    'id_visitante' => $visitante->id_visitante,
                    'id_solicitud' => $solicitud->id_solicitud,
                ]);

                $codigoQr = $folio;

                QR::create([
                    'codigo_numerico'        => $codigoQr,
                    'vigencia_inicio'        => now(),
                    'vigencia_final'         => now()->addHours(2),
                    'prorroga_tolerancia'    => 0,
                    'id_estadoQr'            => 1,
                    'id_solicitud_visitante' => $solicitudVisitante->id_solicitud_visitante,
                ]);

                return [
                    'folio'            => $solicitud->folio,
                    'codigo_qr'        => $codigoQr,
                    'nombre_visitante' => trim($visitante->nombre . ' ' . $visitante->apellidos),
                    'correo_visitante' => $visitante->correo_personal,
                    'lugar_destino'    => $solicitud->lugar_encuentro,
                ];
            });

            return response()->json([
                'message' => 'Visita de consulta registrada correctamente.',
                'data'    => $resultado,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No fue posible registrar la visita de consulta.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
>>>>>>> 3c09282 (Agrega registro de visitas de consulta)

    return response()->json([
        'data' => [
            'token'           => '',
            'rol'             => 'vigilante',
            'name'            => 'Vigilante',
            'email'           => $request->telefono,
            'departamento'    => $request->area,
            'id_empleado_sam' => 0,
            'id_departamento' => 0,
            'rol_api'         => 'vigilante',
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
    
// VigilanteApiController.php

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
        return response()->json([
            'message' => 'Código QR no encontrado. Verifica que el código sea correcto.'
        ], 404);
    }

    if ($qr->id_estadoQr == 4) {
        return response()->json([
            'message' => 'Este código QR fue cancelado y ya no es válido.'
        ], 422);
    }

    // ── CORRECCIÓN PRINCIPAL: comparar con Carbon correctamente ──
    $ahora  = now();
    $inicio = \Carbon\Carbon::parse($qr->vigencia_inicio);
    $fin    = \Carbon\Carbon::parse($qr->vigencia_final);

    if ($ahora->lt($inicio)) {
        return response()->json([
            'message' => "Este QR aún no es válido. Válido desde: {$inicio->format('d/m/Y H:i')}."
        ], 422);
    }

    if ($ahora->gt($fin)) {
        return response()->json([
            'message' => "Este QR expiró el {$fin->format('d/m/Y H:i')}."
        ], 422);
    }

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
            'acceso_concedido'  => true,          // ← añadir este campo
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
    // =========================================================================
    public function registrarEntrada(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'id_qr'    => ['required', 'integer'],
            'telefono' => ['required', 'string', 'digits:10'],
            'area'     => ['required', 'string', 'max:100'],
        ]);

        if ($validador->fails()) {
            return response()->json([
                'message' => 'Los datos provistos son inválidos.',
                'errors'  => $validador->errors(),
            ], 422);
        }

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
        $validador = Validator::make($request->all(), [
            'id_qr'    => ['required', 'integer'],
            'telefono' => ['required', 'string', 'digits:10'],
            'area'     => ['required', 'string', 'max:100'],
        ]);

        if ($validador->fails()) {
            return response()->json([
                'message' => 'Los datos provistos son inválidos.',
                'errors'  => $validador->errors(),
            ], 422);
        }

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
                'id_registro'              => $r->id_registro,
                'visitante'                => $visitante
                    ? $visitante->nombre . ' ' . $visitante->apellidos
                    : 'Desconocido',
                'hora_llegada_institucion' => $r->hora_llegada_institucion,
                'hora_salida_institucion'  => $r->hora_salida_institucion,
                'caseta_entrada'           => $r->caseta_entrada,
                'caseta_salida'            => $r->caseta_salida,
            ];
        });

        return response()->json([
            'data'         => $data,
            'current_page' => $registros->currentPage(),
            'last_page'    => $registros->lastPage(),
        ]);
    }
}