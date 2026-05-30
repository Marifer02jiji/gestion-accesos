<?php

// =============================================================================
// Proyecto  : Sistema de Gestión de Accesos y Visitas
// Archivo   : VigilanteApiController.php
// Versión   : 3.2.0
// Descripción: Fix salida — validar vigencia solo si no tiene entrada activa
// =============================================================================

namespace App\Http\Controllers\Api;

use App\Mail\EnviarQRMail;
use App\Models\QR;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\Visitante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class VigilanteApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validador = Validator::make($request->all(), [
            'telefono' => ['required', 'string', 'digits:10'],
            'area'     => ['required', 'string', 'max:100'],
        ]);

        if ($validador->fails()) {
            return response()->json([
                'message' => 'Los datos provistos son invalidos.',
                'errors'  => $validador->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'Identificacion registrada.',
            'data'    => [
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

    public function registrarConsulta(Request $request): JsonResponse
    {
        $validador = Validator::make($request->all(), [
            'nombre_visitante'    => ['required', 'string', 'min:2', 'max:100'],
            'apellidos_visitante' => ['required', 'string', 'min:2', 'max:100'],
            'correo_visitante'    => ['required', 'email', 'max:150'],
            'lugar_destino'       => ['required', 'string', 'max:100'],
        ]);

        if ($validador->fails()) {
            return response()->json([
                'message' => 'Los datos provistos son invalidos.',
                'errors'  => $validador->errors(),
            ], 422);
        }

        $lugaresPermitidos = [
            'División de Comunicación y Difusión',
            'Desarrollo Académico',
        ];

        if (!in_array($request->lugar_destino, $lugaresPermitidos, true)) {
            return response()->json(['message' => 'El lugar destino no es valido.'], 422);
        }

        try {
            $resultado = DB::transaction(function () use ($request) {
                $visitante = Visitante::updateOrCreate(
                    ['correo_personal' => $request->correo_visitante],
                    [
                        'nombre'              => trim($request->nombre_visitante),
                        'apellidos'           => trim($request->apellidos_visitante),
                        'id_estado_visitante' => null,
                    ]
                );

                $folio = Solicitud::generarFolio();

                $solicitud = Solicitud::create([
                    'folio'               => $folio,
                    'fecha_inicio'        => now(),
                    'tolerancia_antes'    => 0,
                    'tolerancia_despues'  => 120,
                    'lugar_encuentro'     => $request->lugar_destino,
                    'numero_visitantes'   => 1,
                    'motivo_visita'       => 'Visita espontanea de consulta',
                    'id_estado_solicitud' => 2,
                    'id_tipo_solicitud'   => 4,
                    'id_autorizador'      => null,
                    'id_solicitante'      => 0,
                ]);

                $solicitudVisitante = SolicitudVisitante::create([
                    'id_visitante' => $visitante->id_visitante,
                    'id_solicitud' => $solicitud->id_solicitud,
                ]);

                $qr = QR::create([
                    'codigo_numerico'        => $folio,
                    'vigencia_inicio'        => now(),
                    'vigencia_final'         => now()->addHours(2),
                    'prorroga_tolerancia'    => 0,
                    'id_estadoQr'            => 1,
                    'id_solicitud_visitante' => $solicitudVisitante->id_solicitud_visitante,
                ]);

                $correoEnviado = false;
                try {
                    Mail::to($visitante->correo_personal)->send(new EnviarQRMail($qr));
                    $correoEnviado = true;
                } catch (\Throwable $e) {
                    Log::error('Error enviando QR de consulta: ' . $e->getMessage());
                }

                return [
                    'folio'               => $solicitud->folio,
                    'codigo_qr'           => $folio,
                    'nombre_visitante'     => $visitante->nombre,
                    'apellidos_visitante'  => $visitante->apellidos,
                    'nombre_completo'      => trim($visitante->nombre . ' ' . $visitante->apellidos),
                    'correo_visitante'     => $visitante->correo_personal,
                    'lugar_destino'        => $solicitud->lugar_encuentro,
                    'correo_enviado'       => $correoEnviado,
                ];
            });

            return response()->json([
                'message' => $resultado['correo_enviado']
                    ? 'Visita de consulta registrada. QR enviado al correo.'
                    : 'Visita de consulta registrada, pero no se pudo enviar el correo.',
                'data' => $resultado,
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No fue posible registrar la visita de consulta.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function visitasHoy(): JsonResponse
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


        
    public function escanear(Request $request): JsonResponse
    {
        $validador = Validator::make($request->all(), [
            'codigo_qr' => ['required', 'string'],
            'telefono'  => ['required', 'string', 'digits:10'],
            'area'      => ['nullable', 'string', 'max:100'],
        ]);

        if ($validador->fails()) {
            return response()->json(['message' => 'Datos invalidos.', 'errors' => $validador->errors()], 422);
        }

        $telefono = $request->input('telefono');
        $area     = $request->input('area', '');

        $qr = QR::where('codigo_numerico', $request->codigo_qr)
            ->with(['solicitudVisitante.visitante', 'solicitudVisitante.solicitud'])
            ->first();

        if (!$qr) {
            return response()->json(['data' => $this->respuestaRechazo(null, 'Codigo QR no encontrado.')], 200);
        }

        if ((int) $qr->id_estadoQr === 4) {
            return response()->json(['data' => $this->respuestaRechazo($qr, 'Este codigo QR fue cancelado.')], 200);
        }

        // Buscar si tiene entrada activa ANTES de validar vigencia
        $registroActivo = RegistroAcceso::where('id_qr', $qr->id_qr)
            ->whereNotNull('hora_llegada_institucion')
            ->whereNull('hora_salida_institucion')
            ->orderByDesc('id_registro')
            ->first();

        $accionDisponible = $registroActivo ? 'salida' : 'entrada';

        // Solo validar vigencia si va a ENTRAR
        if ($accionDisponible === 'entrada') {
            if (now()->lt($qr->vigencia_inicio) || now()->gt($qr->vigencia_final)) {
                return response()->json([
                    'data' => $this->respuestaRechazo($qr, 'El QR ha expirado o aun no es valido.')
                ], 200);
            }
        }

        $solicitud = $qr->solicitudVisitante->solicitud;

        try {
            if ($accionDisponible === 'entrada') {
                RegistroAcceso::create([
                    'id_qr'                    => $qr->id_qr,
                    'hora_llegada_institucion' => now(),
                    'telefono_vigilante'       => $telefono,
                    'area_vigilante'           => $area,
                ]);
                $qr->update(['id_estadoQr' => 2]);

                // Cambiar estado solicitud a "En Institución" (id=5)
                $solicitud->update(['id_estado_solicitud' => 5]);

                // Notificar al solicitante
                \App\Models\Notificacion::create([
                    'id_empleado'  => $solicitud->id_solicitante,
                    'id_solicitud' => $solicitud->id_solicitud,
                    'tipo'         => 'entrada',
                    'mensaje'      => "Tu visitante entró a la institución. Folio: {$solicitud->folio}",
                    'leida'        => false,
                ]);

            } else {
                $registroActivo->update([
                    'hora_salida_institucion' => now(),
                    'telefono_vigilante'      => $telefono,
                    'area_vigilante'          => $area,
                ]);
                $qr->update(['id_estadoQr' => 3]);

                // Cambiar estado solicitud a "Finalizada" (id=8)
                $solicitud->update(['id_estado_solicitud' => 8]);

                // Notificar al solicitante
                \App\Models\Notificacion::create([
                    'id_empleado'  => $solicitud->id_solicitante,
                    'id_solicitud' => $solicitud->id_solicitud,
                    'tipo'         => 'salida',
                    'mensaje'      => "Tu visitante salió de la institución. Folio: {$solicitud->folio}",
                    'leida'        => false,
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'data' => $this->respuestaRechazo($qr, 'Error interno al registrar el acceso.')
            ], 200);
        }

        $visitante = $qr->solicitudVisitante->visitante;

        return response()->json([
            'data' => [
                'id_qr'             => $qr->id_qr,
                'acceso_concedido'  => true,
                'accion_disponible' => $accionDisponible,
                'motivo_rechazo'    => null,
                'visitante'         => [
                    'nombre'          => $visitante->nombre,
                    'apellidos'       => $visitante->apellidos,
                    'correo_personal' => $visitante->correo_personal,
                ],
                'solicitud'         => [
                    'motivo_visita'   => $solicitud->motivo_visita,
                    'vigencia_inicio' => $qr->vigencia_inicio,
                    'vigencia_final'  => $qr->vigencia_final,
                ],
            ],
        ], 200);
    }




    public function registrarEntrada(Request $request): JsonResponse
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
            return response()->json(['message' => 'Este visitante ya tiene una entrada activa sin salida.'], 422);
        }

        RegistroAcceso::create([
            'hora_llegada_institucion' => now(),
            'id_qr'                    => $qr->id_qr,
            'telefono_vigilante'       => $request->telefono,
            'area_vigilante'           => $request->area,
        ]);

        $qr->update(['id_estadoQr' => 2]);

        return response()->json(['message' => 'Entrada registrada correctamente.']);
    }

    public function registrarSalida(Request $request): JsonResponse
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
            return response()->json(['message' => 'No se encontro entrada registrada para este QR.'], 404);
        }

        $registro->update([
            'hora_salida_institucion' => now(),
            'telefono_vigilante'      => $request->telefono,
            'area_vigilante'          => $request->area,
        ]);

        QR::where('id_qr', $request->id_qr)->update(['id_estadoQr' => 3]);

        return response()->json(['message' => 'Salida registrada correctamente.']);
    }

    public function historial(): JsonResponse
    {
        $registros = RegistroAcceso::with(['qr.solicitudVisitante.visitante'])
            ->orderByDesc('hora_llegada_institucion')
            ->paginate(15);

        $data = $registros->map(function ($r) {
            $visitante = $r->qr?->solicitudVisitante?->visitante;
            return [
                'id_registro'              => $r->id_registro,
                'visitante'                => $visitante ? $visitante->nombre . ' ' . $visitante->apellidos : 'Desconocido',
                'hora_llegada_institucion' => $r->hora_llegada_institucion,
                'hora_salida_institucion'  => $r->hora_salida_institucion,
                'area_vigilante'           => $r->area_vigilante,
            ];
        });

        return response()->json([
            'data'         => $data,
            'current_page' => $registros->currentPage(),
            'last_page'    => $registros->lastPage(),
        ]);
    }

    private function respuestaRechazo(?QR $qr, string $motivo): array
    {
        $visitante = $qr?->solicitudVisitante?->visitante;
        $solicitud = $qr?->solicitudVisitante?->solicitud;

        return [
            'id_qr'             => $qr?->id_qr ?? 0,
            'acceso_concedido'  => false,
            'accion_disponible' => 'entrada',
            'motivo_rechazo'    => $motivo,
            'visitante'         => [
                'nombre'          => $visitante?->nombre ?? '',
                'apellidos'       => $visitante?->apellidos ?? '',
                'correo_personal' => $visitante?->correo_personal ?? '',
            ],
            'solicitud'         => [
                'motivo_visita'   => $solicitud?->motivo_visita ?? '',
                'vigencia_inicio' => $qr?->vigencia_inicio ?? '',
                'vigencia_final'  => $qr?->vigencia_final ?? '',
            ],
        ];
    }
}