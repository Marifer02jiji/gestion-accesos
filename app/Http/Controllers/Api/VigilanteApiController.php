<?php

// =============================================================================
// Proyecto  : Sistema de Gestión de Accesos y Visitas
// Archivo   : VigilanteApiController.php
// Versión   : 3.3.0
// Descripción: Registro de acceso — columnas telefono_vigilante_entrada / caseta_entrada
// =============================================================================

namespace App\Http\Controllers\Api;

use App\Mail\EnviarQRMail;
use App\Models\QR;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use App\Services\FlujoAccesoService;
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
    private function flujoAcceso(): FlujoAccesoService
    {
        return new FlujoAccesoService();
    }

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
        $estadosVisibles = [
            FlujoAccesoService::ESTADO_AUTORIZADA,
            FlujoAccesoService::ESTADO_EN_INSTITUCION,
            FlujoAccesoService::ESTADO_EN_ENCUENTRO,
            FlujoAccesoService::ESTADO_EN_TRANSITO_SALIDA,
            FlujoAccesoService::ESTADO_FINALIZADA,
        ];

        $visitas = Solicitud::with(['visitantes', 'estado', 'solicitudVisitantes.qr'])
            ->whereIn('id_estado_solicitud', $estadosVisibles)
            ->whereDate('fecha_inicio', today())
            ->orderBy('fecha_inicio')
            ->get()
            ->map(function ($solicitud) {
                $registro = RegistroAcceso::whereHas(
                    'qr.solicitudVisitante.solicitud',
                    fn ($q) => $q->where('id_solicitud', $solicitud->id_solicitud)
                )
                    ->orderByDesc('id_registro')
                    ->first();

                $idEstado     = (int) $solicitud->id_estado_solicitud;
                $nombreEstado = trim($solicitud->estado->nombre ?? '');

                $entradaRegistrada = $registro?->hora_llegada_institucion !== null
                    || $idEstado >= FlujoAccesoService::ESTADO_EN_INSTITUCION;

                $salidaRegistrada = $registro?->hora_salida_institucion !== null
                    || $idEstado === FlujoAccesoService::ESTADO_FINALIZADA;

                return [
                    'id_solicitud'        => $solicitud->id_solicitud,
                    'folio'               => $solicitud->folio,
                    'motivo_visita'       => $solicitud->motivo_visita,
                    'lugar_encuentro'     => $solicitud->lugar_encuentro,
                    'hora_inicio'         => $solicitud->fecha_inicio,
                    'id_estado_solicitud' => $idEstado,
                    'estado'              => $nombreEstado,
                    'estado_nombre'       => $nombreEstado,
                    'entrada_registrada'  => $entradaRegistrada,
                    'salida_registrada'   => $salidaRegistrada,
                    'visitantes'          => $solicitud->visitantes->map(fn ($v) => [
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
            'telefono'  => ['required', 'digits:10'],
            'area'      => ['required', 'string', 'min:1', 'max:100'],
        ]);

        if ($validador->fails()) {
            return response()->json(['message' => 'Datos invalidos.', 'errors' => $validador->errors()], 422);
        }

        try {
            $telefonoEntrada = $this->resolverTelefonoVigilanteEntrada($request);
            $casetaEntrada   = $this->resolverCasetaEntrada($request);
            $telefonoSalida  = $this->resolverTelefonoVigilanteSalida($request);
            $casetaSalida    = $this->resolverCasetaSalida($request);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

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
        $estadosAutocompletados = [];

        try {
            if ($accionDisponible === 'entrada') {
                RegistroAcceso::registrarEntradaInstitucional(
                    $qr->id_qr,
                    $telefonoEntrada,
                    $casetaEntrada
                );
                $qr->update(['id_estadoQr' => 2]);
                $this->flujoAcceso()->marcarEnInstitucion($solicitud);

                \App\Models\Notificacion::create([
                    'id_empleado'  => $solicitud->id_solicitante,
                    'id_solicitud' => $solicitud->id_solicitud,
                    'tipo'         => 'entrada',
                    'mensaje'      => "Tu visitante entró a la institución. Folio: {$solicitud->folio}",
                    'leida'        => false,
                ]);
            } else {
                $estadosAutocompletados = $this->flujoAcceso()->prepararSalidaVigilante(
                    $solicitud,
                    $registroActivo
                );
                $this->flujoAcceso()->logAutocompletado(
                    $solicitud->id_solicitud,
                    $estadosAutocompletados
                );

                RegistroAcceso::registrarSalidaInstitucional(
                    $registroActivo,
                    $telefonoSalida,
                    $casetaSalida
                );
                $qr->update(['id_estadoQr' => 3]);
                $this->flujoAcceso()->marcarFinalizada($solicitud);

                \App\Models\Notificacion::create([
                    'id_empleado'  => $solicitud->id_solicitante,
                    'id_solicitud' => $solicitud->id_solicitud,
                    'tipo'         => 'salida',
                    'mensaje'      => "Tu visitante salió de la institución. Folio: {$solicitud->folio}",
                    'leida'        => false,
                ]);
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'data' => $this->respuestaRechazo($qr, $e->getMessage()),
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error al registrar acceso en escanear', [
                'id_qr'   => $qr->id_qr,
                'accion'  => $accionDisponible,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'data' => $this->respuestaRechazo($qr, 'Error interno al registrar el acceso.')
            ], 200);
        }

        $visitante = $qr->solicitudVisitante->visitante;
        $solicitud->refresh();
        $solicitud->load('estado');

        return response()->json([
            'data' => [
                'id_qr'                  => $qr->id_qr,
                'acceso_concedido'       => true,
                'accion_disponible'      => $accionDisponible,
                'motivo_rechazo'         => null,
                'id_estado_solicitud'    => (int) $solicitud->id_estado_solicitud,
                'estado_solicitud'       => $solicitud->estado->nombre ?? '',
                'estados_autocompletados'=> $estadosAutocompletados,
                'visitante'              => [
                    'nombre'          => $visitante->nombre,
                    'apellidos'       => $visitante->apellidos,
                    'correo_personal' => $visitante->correo_personal,
                ],
                'solicitud'              => [
                    'motivo_visita'   => $solicitud->motivo_visita,
                    'vigencia_inicio' => $qr->vigencia_inicio,
                    'vigencia_final'  => $qr->vigencia_final,
                    'lugar_encuentro' => $solicitud->lugar_encuentro,
                ],
            ],
        ], 200);
    }




    public function registrarEntrada(Request $request): JsonResponse
    {
        $request->validate([
            'id_qr'    => 'required|integer',
            'telefono' => 'required|digits:10',
            'area'     => 'required|string|min:1|max:100',
        ]);

        try {
            $telefono = $this->resolverTelefonoVigilanteEntrada($request);
            $caseta   = $this->resolverCasetaEntrada($request);

            Log::info('POST /vigilante/entrada — datos vigilante', [
                'id_qr'                      => $request->id_qr,
                'telefono_body'              => $request->input('telefono'),
                'telefono_vigilante_entrada' => $telefono,
                'caseta_entrada'             => $caseta,
            ]);

            $qr = QR::with('solicitudVisitante.solicitud')->findOrFail($request->id_qr);

            $entradaActiva = RegistroAcceso::where('id_qr', $qr->id_qr)
                ->whereNotNull('hora_llegada_institucion')
                ->whereNull('hora_salida_institucion')
                ->exists();

            if ($entradaActiva) {
                return response()->json([
                    'message' => 'Este visitante ya tiene una entrada activa sin salida.',
                ], 422);
            }

            RegistroAcceso::registrarEntradaInstitucional(
                $qr->id_qr,
                $telefono,
                $caseta
            );

            $qr->update(['id_estadoQr' => 2]);

            $solicitud = $qr->solicitudVisitante->solicitud ?? null;
            if ($solicitud) {
                $this->flujoAcceso()->marcarEnInstitucion($solicitud);
            }

            return response()->json(['message' => 'Entrada registrada correctamente.']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Error en POST /vigilante/entrada', [
                'id_qr'    => $request->id_qr,
                'telefono' => $request->input('telefono'),
                'area'     => $request->input('area'),
                'message'  => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'No fue posible registrar la entrada. Intente nuevamente.',
            ], 500);
        }
    }

    public function registrarSalida(Request $request): JsonResponse
    {
        $request->validate([
            'id_qr'    => 'required|integer',
            'telefono' => 'required|digits:10',
            'area'     => 'required|string|min:1|max:100',
        ]);

        $qr = QR::with('solicitudVisitante.solicitud')->findOrFail($request->id_qr);

        $registro = $this->flujoAcceso()->registroActivoPorQr($qr->id_qr);

        if (!$registro) {
            return response()->json(['message' => 'No se encontro entrada registrada para este QR.'], 404);
        }

        $solicitud = $qr->solicitudVisitante->solicitud;

        $autocompletados = [];

        try {
            $autocompletados = $this->flujoAcceso()->prepararSalidaVigilante($solicitud, $registro);
            $this->flujoAcceso()->logAutocompletado($solicitud->id_solicitud, $autocompletados);

            RegistroAcceso::registrarSalidaInstitucional(
                $registro,
                $this->resolverTelefonoVigilanteSalida($request),
                $this->resolverCasetaSalida($request)
            );

            $qr->update(['id_estadoQr' => 3]);
            $this->flujoAcceso()->marcarFinalizada($solicitud);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Salida registrada correctamente.',
            'data'    => [
                'estados_autocompletados' => $autocompletados,
                'id_estado_solicitud'     => FlujoAccesoService::ESTADO_FINALIZADA,
            ],
        ]);
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
                'caseta_entrada'           => $r->caseta_entrada,
            ];
        });

        return response()->json([
            'data'         => $data,
            'current_page' => $registros->currentPage(),
            'last_page'    => $registros->lastPage(),
        ]);
    }

    /**
     * Mapea el JSON { "telefono": "..." } al campo telefono_vigilante_entrada (NOT NULL).
     */
    private function resolverTelefonoVigilanteEntrada(Request $request): string
    {
        if ($request->filled('telefono_vigilante_entrada')) {
            return $this->normalizarTelefonoVigilante(
                (string) $request->input('telefono_vigilante_entrada')
            );
        }

        if ($request->filled('telefono')) {
            return $this->normalizarTelefonoVigilante((string) $request->input('telefono'));
        }

        throw new \InvalidArgumentException(
            'El telefono del vigilante es obligatorio (campo telefono en el body).'
        );
    }

    private function resolverCasetaEntrada(Request $request): string
    {
        if ($request->filled('caseta_entrada')) {
            return trim((string) $request->input('caseta_entrada'));
        }

        if ($request->filled('area')) {
            return trim((string) $request->input('area'));
        }

        throw new \InvalidArgumentException(
            'El area o caseta del vigilante es obligatoria (campo area en el body).'
        );
    }

    private function resolverTelefonoVigilanteSalida(Request $request): string
    {
        if ($request->filled('telefono_vigilante_salida')) {
            return $this->normalizarTelefonoVigilante(
                (string) $request->input('telefono_vigilante_salida')
            );
        }

        if ($request->filled('telefono')) {
            return $this->normalizarTelefonoVigilante((string) $request->input('telefono'));
        }

        throw new \InvalidArgumentException(
            'El telefono del vigilante es obligatorio (campo telefono en el body).'
        );
    }

    private function resolverCasetaSalida(Request $request): string
    {
        if ($request->filled('caseta_salida')) {
            return trim((string) $request->input('caseta_salida'));
        }

        if ($request->filled('area')) {
            return trim((string) $request->input('area'));
        }

        throw new \InvalidArgumentException(
            'El area o caseta del vigilante es obligatoria (campo area en el body).'
        );
    }

    private function normalizarTelefonoVigilante(string $valor): string
    {
        $digitos = preg_replace('/\D+/', '', $valor) ?? '';

        if (strlen($digitos) !== 10) {
            throw new \InvalidArgumentException(
                'El telefono del vigilante debe tener exactamente 10 digitos.'
            );
        }

        return $digitos;
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