<?php
namespace App\Http\Controllers\Api;

<<<<<<< HEAD
namespace App\Http\Controllers\Api;
=======
/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Http/Controllers/Api/SolicitudApiController.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial API REST para solicitudes con Sanctum
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Validación de horarios L-V 7-21h, Sáb hasta 14h, no domingos
 * ID: 3 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Implementar enviarQR, reenviarQR y extenderQR
 * ID: 4 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar confirmarLlegada y confirmarSalida de encuentro
 * ID: 5 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Fix cancelar solicitud con envío de correo al visitante
 * ID: 6 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar campo correo_cancelacion en respuesta para Flutter
 * ID: 7 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Fix eliminar código duplicado fuera de métodos (ParseError línea 348)
 *
 * Descripción: API REST para la gestión móvil de solicitudes, permitiendo a los usuarios anfitriones y autorizadores operar desde la aplicación Flutter.
 */
>>>>>>> 90156fc (cambios de evt a vis)

use App\Http\Controllers\Controller;
use App\Rules\AnticipacionMinimaVisita;
use App\Models\Notificacion;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\Visitante;
use App\Mail\EnviarQRMail;
use App\Services\AutorizacionVisitaService;
use App\Services\FlujoAccesoService;
<<<<<<< HEAD
=======
use App\Services\ListaExclusionVisitaService;
>>>>>>> 90156fc (cambios de evt a vis)
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SolicitudApiController extends Controller
{
    public function __construct(
<<<<<<< HEAD
        private readonly AutorizacionVisitaService $autorizacionVisita
=======
        private readonly AutorizacionVisitaService $autorizacionVisita,
        private readonly ListaExclusionVisitaService $listaExclusion,
>>>>>>> 90156fc (cambios de evt a vis)
    ) {
    }

    private function extensionTiempo(): \App\Services\SolicitudExtensionTiempoService
    {
        return app(\App\Services\SolicitudExtensionTiempoService::class);
    }

    private function idEmpleado(): int
    {
        return auth()->user()->idSam();
    }

    private function usuarioSamAutorizador(): string
    {
        return (string) (auth()->user()->name ?? '');
    }

    private function estadosPermitenEnvioQr(): array
    {
        return [
            FlujoAccesoService::ESTADO_AUTORIZADA,
            FlujoAccesoService::ESTADO_EN_INSTITUCION,
            FlujoAccesoService::ESTADO_EN_ENCUENTRO,
            FlujoAccesoService::ESTADO_EN_TRANSITO_SALIDA,
        ];
    }

    private function solicitantesVisiblesAutorizador(): array
    {
        return $this->autorizacionVisita->idsSolicitantesAutorizables(
            $this->idEmpleado(),
            $this->usuarioSamAutorizador()
        );
    }

    private function aplicarFiltroSolicitantesAutorizador($query)
    {
        $idPropio  = $this->idEmpleado();
        $visibles  = $this->solicitantesVisiblesAutorizador();
        $filtrados = array_values(array_filter(
            array_map('intval', $visibles),
            fn ($id) => $id > 0 && $id !== $idPropio
        ));

        if (!empty($filtrados)) {
            return $query->whereIn('id_solicitante', $filtrados);
        }

        return $query->where('id_solicitante', '!=', $idPropio);
    }

    private function puedeGestionarComoAutorizador(Solicitud $solicitud): bool
    {
        $idPropio = $this->idEmpleado();

        if ((int) $solicitud->id_solicitante === $idPropio) {
            return false;
        }

        return $this->autorizacionVisita->puedeGestionarSolicitud(
            $this->idEmpleado(),
            $this->usuarioSamAutorizador(),
            (int) $solicitud->id_solicitante
        );
    }

    private function formatearSolicitudParaMovil($solicitud)
    {
        $solicitud->loadMissing(['visitantes', 'estado', 'tipo', 'solicitudVisitantes.qr']);

        $flujo  = new FlujoAccesoService();
        $visita = $flujo->formatearVisitaActiva($solicitud);

        $solicitud->id_estado_solicitud    = $visita['id_estado_solicitud'];
        $solicitud->estado_nombre          = $visita['estado'];
        $solicitud->hora_llegada_campus    = $visita['hora_llegada_campus'];
        $solicitud->hora_llegada_encuentro = $visita['hora_llegada_area'];
        $solicitud->hora_salida_encuentro  = $visita['hora_salida_area'];
        $solicitud->hora_salida_campus     = $visita['hora_salida_campus'];
        $solicitud->fecha_encuentro        = $solicitud->fecha_inicio;

        $solicitud->nombre_solicitante  = $solicitud->solicitante->name ?? $solicitud->solicitante->nombre ?? 'Sin nombre';
        $solicitud->usuario_solicitante = $solicitud->solicitante->name ?? '';

        if ((int) $solicitud->id_solicitante > 0) {
            try {
                $empleado = DB::connection('sam')
                    ->table('empleados')
                    ->where('id_empleado', $solicitud->id_solicitante)
                    ->first();

                if ($empleado) {
                    $nombreCompleto = trim(($empleado->nombre ?? '') . ' ' . ($empleado->apellidoPa ?? ''));
                    if ($nombreCompleto !== '') {
                        $solicitud->nombre_solicitante = $nombreCompleto;
                    }
                    if (!empty($empleado->usuario)) {
                        $solicitud->usuario_solicitante = $empleado->usuario;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('SAM no disponible para nombre solicitante: ' . $e->getMessage());
            }
        }

        $solicitud->correo_solicitante = $solicitud->solicitante->email ?? '';
        $solicitud->tipo_visita        = $solicitud->tipo->nombre
            ?? $solicitud->tipo->descripcion
            ?? $this->mapearTipoSolicitud($solicitud->id_tipo_solicitud);

        return $solicitud;
    }

    private function mapearTipoSolicitud($idTipo): string
    {
        return match ((int) $idTipo) {
            1 => 'Proveedor',
            2 => 'Institucional / Negocios',
            3 => 'Personal',
            4 => 'Consulta',
            default => 'Sin tipo',
        };
    }

    private function fechaVencimientoSolicitud(Solicitud $solicitud): Carbon
    {
        return Carbon::parse($solicitud->fecha_inicio)
            ->addMinutes((int) $solicitud->tolerancia_despues);
    }

    private function solicitudYaVencio(Solicitud $solicitud): bool
    {
        return now()->greaterThan($this->fechaVencimientoSolicitud($solicitud));
    }

    private function diaEncuentroPasado(Solicitud $solicitud): bool
    {
        return Carbon::parse($solicitud->fecha_inicio)
            ->startOfDay()
            ->lt(now()->startOfDay());
    }

    private function cancelarPendientesVencidas(): void
    {
        Solicitud::where('id_estado_solicitud', 1)
            ->get()
            ->each(function ($solicitud) {
                if ($this->solicitudYaVencio($solicitud)) {
                    $solicitud->update([
                        'id_estado_solicitud' => 4,
                        'fecha_cancelacion'   => now(),
                    ]);
                }
            });
    }

    public function index(Request $request)
    {
        $this->cancelarPendientesVencidas();

        $query = Solicitud::where('id_solicitante', $this->idEmpleado())
            ->with(['estado', 'tipo', 'visitantes', 'solicitante']);

        $estado = $request->get('estado');
        if ($estado) {
            $mapaEstados = [
                'pendiente'  => 1,
                'autorizada' => 2,
                'rechazada'  => 3,
                'cancelada'  => 4,
            ];
            $key = strtolower($estado);
            if (isset($mapaEstados[$key])) {
                $query->where('id_estado_solicitud', $mapaEstados[$key]);
            }
        }

        $solicitudes = $query->orderBy('fecha_creacion', 'desc')->paginate(10);
        $solicitudes->getCollection()->transform(fn($s) => $this->formatearSolicitudParaMovil($s));

        return response()->json([
            'message' => 'Solicitudes obtenidas correctamente.',
            'data'    => $solicitudes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_inicio' => ['required', 'date', new AnticipacionMinimaVisita(1), function($attribute, $value, $fail) {
                $fecha = Carbon::parse($value);
                $hora  = (int) $fecha->format('H');
                $dia   = (int) $fecha->dayOfWeek;

                if ($dia === 0) {
                    $fail('No se pueden agendar visitas los domingos.');
                    return;
                }

                if ($dia === 6 && $hora >= 14) {
                    $fail('Los sabados solo se permiten visitas hasta las 2:00 PM.');
                    return;
                }

                if ($hora < 7 || $hora >= 21) {
                    $fail('Las visitas solo pueden agendarse entre las 7:00 AM y las 9:00 PM.');
                    return;
                }
            }],
            'lugar_encuentro'        => 'required|string|max:100',
            'motivo_visita'          => 'required|string|max:255',
            'id_tipo_solicitud'      => 'required|exists:ca_TipoSolicitud,id_tipo_solicitud',
            'tolerancia_antes'       => 'required|in:15,30',
            'tolerancia_despues'     => 'required|in:15,30',
            'visitantes'             => 'required|array|min:1',
            'visitantes.*.nombre'    => 'required|string|max:100',
            'visitantes.*.apellidos' => 'required|string|max:100',
            'visitantes.*.correo'    => 'required|email|max:150',
        ]);

        if ($bloqueo = $this->listaExclusion->respuesta422SiExcluidos(
            $request->visitantes,
            'No se puede crear la solicitud. Los siguientes visitantes estan en lista de exclusion: %s'
        )) {
            return $bloqueo;
        }

        $solicitud = Solicitud::create([
            'folio'               => Solicitud::generarFolio(),
            'fecha_inicio'        => $request->fecha_inicio,
            'lugar_encuentro'     => $request->lugar_encuentro,
            'motivo_visita'       => $request->motivo_visita,
            'id_tipo_solicitud'   => $request->id_tipo_solicitud,
            'tolerancia_antes'    => $request->tolerancia_antes,
            'tolerancia_despues'  => $request->tolerancia_despues,
            'numero_visitantes'   => count($request->visitantes),
            'id_estado_solicitud' => 1,
            'id_solicitante'      => $this->idEmpleado(),
        ]);

        foreach ($request->visitantes as $v) {
            $correoNorm = strtolower(trim((string) $v['correo']));
            $visitante = Visitante::updateOrCreate(
                ['correo_personal' => $correoNorm],
                [
                    'nombre'    => trim($v['nombre']),
                    'apellidos' => trim($v['apellidos']),
                ]
            );
            SolicitudVisitante::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_visitante' => $visitante->id_visitante,
            ]);
        }

        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante'])->findOrFail($solicitud->id_solicitud);
        $this->formatearSolicitudParaMovil($solicitud);

        return response()->json(['message' => 'Solicitud creada correctamente.', 'data' => $solicitud], 201);
    }

    public function show($id)
    {
        $this->cancelarPendientesVencidas();

        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitudVisitantes.qr', 'solicitante'])->findOrFail($id);
        $this->formatearSolicitudParaMovil($solicitud);

        return response()->json(['message' => 'Solicitud obtenida correctamente.', 'data' => $solicitud]);
    }

    public function cancelar($id)
    {
        $solicitud = Solicitud::with(['solicitudVisitantes.qr', 'solicitudVisitantes.visitante'])->findOrFail($id);

        if (!$solicitud->esCancelable()) {
            return response()->json(['message' => 'Esta solicitud no puede cancelarse.', 'data' => null], 422);
        }

        foreach ($solicitud->solicitudVisitantes as $sv) {
            if ($sv->qr && $sv->qr->id_estadoQr === 1) {
                $sv->qr->update(['id_estadoQr' => 4]);
            }
        }

        $solicitud->update([
            'id_estado_solicitud' => 4,
            'cancelado_por'       => $this->idEmpleado(),
            'fecha_cancelacion'   => now(),
        ]);

        // Enviar correo al visitante
        $anfitrion = auth()->user()->name ?? 'el anfitrión';
        foreach ($solicitud->solicitudVisitantes as $sv) {
            $correo = $sv->visitante->correo_personal ?? null;
            if (!$correo) continue;
            try {
                \Illuminate\Support\Facades\Mail::to($correo)->send(
                    new \App\Mail\SolicitudCanceladaMail($sv->visitante, $solicitud, $anfitrion)
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error enviando correo cancelacion API: ' . $e->getMessage());
            }
        }

        $solicitud  = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante', 'solicitudVisitantes.qr'])->findOrFail($id);
        $solicitudData = $solicitud->toArray();
        $solicitudData['correo_cancelacion'] = true;

        return response()->json([
            'message'            => 'Solicitud cancelada correctamente.',
            'correo_cancelacion' => true,
            'data'               => $solicitudData,
        ]);
    }

    public function qr($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

        if (!in_array((int) $solicitud->id_estado_solicitud, $this->estadosPermitenEnvioQr(), true)) {
            return response()->json(['message' => 'La solicitud no esta autorizada o ya finalizo.', 'data' => null], 422);
        }

        $qrs = $solicitud->solicitudVisitantes->map(fn($sv) => $sv->qr)->filter()->values();

        return response()->json(['message' => 'QR obtenido correctamente.', 'data' => $qrs]);
    }

    public function enviarQR($id)
    {
        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitudVisitantes.qr', 'solicitudVisitantes.visitante', 'solicitante'])->findOrFail($id);

        if ((int) $solicitud->id_solicitante !== $this->idEmpleado()) {
            return response()->json(['message' => 'Solo el solicitante puede enviar el QR.', 'data' => null], 403);
        }

        if (!in_array((int) $solicitud->id_estado_solicitud, $this->estadosPermitenEnvioQr(), true)) {
            return response()->json(['message' => 'La solicitud debe estar autorizada para enviar el QR.', 'data' => null], 422);
        }

        if ($this->diaEncuentroPasado($solicitud)) {
            return response()->json(['message' => 'No se puede enviar el QR, la fecha del encuentro ya paso.', 'data' => null], 422);
        }

        $enviados = 0;
        $errores  = 0;

        foreach ($solicitud->solicitudVisitantes as $sv) {
            $qr = $sv->qr;
            if (!$qr) { $errores++; continue; }

            $correo = trim((string) ($sv->visitante->correo_personal ?? ''));
            if ($correo === '') { $errores++; continue; }

            try {
                Mail::to($correo)->send(new EnviarQRMail($qr));
                $enviados++;
            } catch (\Throwable $e) {
                $errores++;
                Log::error('Error enviando QR API: ' . $e->getMessage());
            }
        }

        if ($enviados === 0) {
            return response()->json([
                'message' => 'No se pudo enviar el QR.',
                'data'    => ['enviados' => 0, 'errores' => $errores],
            ], 500);
        }

        return response()->json(['message' => "QR enviado correctamente a {$enviados} visitante(s).", 'data' => ['enviados' => $enviados, 'errores' => $errores]]);
    }

    public function reenviarQR($id)
    {
        $solicitud = Solicitud::with(['solicitudVisitantes.qr', 'solicitudVisitantes.visitante'])->findOrFail($id);

        if ((int) $solicitud->id_solicitante !== $this->idEmpleado()) {
            return response()->json(['message' => 'Solo el solicitante puede reenviar el QR.', 'data' => null], 403);
        }

        if (!in_array((int) $solicitud->id_estado_solicitud, $this->estadosPermitenEnvioQr(), true)) {
            return response()->json(['message' => 'La solicitud debe estar autorizada para reenviar el QR.', 'data' => null], 422);
        }

        if (($solicitud->reenvios_qr ?? 0) >= 3) {
            return response()->json(['message' => 'Se alcanzo el limite de 3 reenvios.', 'data' => ['reenvios_restantes' => 0]], 422);
        }

        if ($this->diaEncuentroPasado($solicitud)) {
            return response()->json(['message' => 'No se puede reenviar el QR, la fecha del encuentro ya paso.', 'data' => null], 422);
        }

        $enviados = 0;
        $errores  = 0;

        foreach ($solicitud->solicitudVisitantes as $sv) {
            $qr     = $sv->qr;
            $correo = $sv->visitante->correo_personal ?? null;
            if (!$qr || !$correo) continue;
            try {
                Mail::to($correo)->send(new EnviarQRMail($qr));
                $enviados++;
            } catch (\Throwable $e) {
                $errores++;
                Log::error('Error reenviando QR API: ' . $e->getMessage());
            }
        }

        if ($enviados === 0) {
            return response()->json(['message' => 'No se pudo reenviar el QR.', 'data' => ['enviados' => 0, 'errores' => $errores]], 500);
        }

        $solicitud->increment('reenvios_qr');
        $solicitud->refresh();
        $restantes = 3 - ($solicitud->reenvios_qr ?? 0);

        return response()->json(['message' => "QR reenviado. Reenvios restantes: {$restantes}", 'data' => ['reenvios_restantes' => $restantes, 'enviados' => $enviados, 'errores' => $errores]]);
    }

    public function extenderQR(Request $request, $id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);
        $minutos   = (int) $request->input('minutos_extra', 60);

        if ($minutos <= 0) {
            return response()->json(['message' => 'Los minutos extra deben ser mayores a cero.', 'data' => null], 422);
        }

<<<<<<< HEAD
        $extendidos = 0;
        foreach ($solicitud->solicitudVisitantes as $sv) {
            if ($sv->qr && in_array($sv->qr->id_estadoQr, [1, 2])) {
                $sv->qr->update([
                    'vigencia_final'      => Carbon::parse($sv->qr->vigencia_final)->addMinutes($minutos),
                    'prorroga_tolerancia' => true,
                ]);
                $extendidos++;
            }
        }

        return response()->json(['message' => "QR extendido {$minutos} minutos para {$extendidos} visitante(s).", 'data' => ['minutos_extra' => $minutos, 'extendidos' => $extendidos]]);
=======
        try {
            $minutos = SolicitudExtensionTiempoService::normalizarMinutosExtra(
                (int) $request->input(
                    'minutos_extra',
                    SolicitudExtensionTiempoService::MINUTOS_EXTRA_DEFECTO
                )
            );
            $data = $this->extensionTiempo()->autorizar($solicitud, $minutos, $this->idEmpleado());

            return response()->json([
                'message' => "QR extendido {$minutos} minutos para {$data['extendidos']} visitante(s).",
                'data'    => $data,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => null], 422);
        }
    }

    public function rechazarExtension($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if ((int) $solicitud->id_solicitante !== $this->idEmpleado()) {
            return response()->json([
                'message' => 'Solo el solicitante puede rechazar la extension.',
                'data'    => null,
            ], 403);
        }

        try {
            $this->extensionTiempo()->rechazar($solicitud, $this->idEmpleado());

            return response()->json([
                'message' => 'Extension de tiempo denegada. Se notifico al visitante por correo.',
                'data'    => null,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => null], 422);
        }
>>>>>>> 90156fc (cambios de evt a vis)
    }

    public function activas(Request $request)
    {
        $flujo = new FlujoAccesoService();

        $solicitudes = Solicitud::where('id_solicitante', $request->user()->idSam())
            ->whereIn('id_estado_solicitud', [
                FlujoAccesoService::ESTADO_AUTORIZADA,
                FlujoAccesoService::ESTADO_EN_INSTITUCION,
                FlujoAccesoService::ESTADO_EN_ENCUENTRO,
                FlujoAccesoService::ESTADO_EN_TRANSITO_SALIDA,
            ])
            ->whereDate('fecha_inicio', today())
            ->with(['visitantes', 'estado', 'tipo', 'solicitudVisitantes.qr'])
            ->get()
            ->filter(function ($s) use ($flujo) {
                if ($flujo->registroEntradaActivoParaSolicitud($s)) return true;
                return !$this->solicitudYaVencio($s);
            })
            ->values();

        $data = $solicitudes->map(function ($s) use ($flujo) {
            if ($flujo->registroEntradaActivoParaSolicitud($s)
                && (int) $s->id_estado_solicitud < FlujoAccesoService::ESTADO_EN_INSTITUCION) {
                $flujo->marcarEnInstitucion($s);
            }
            return $flujo->formatearVisitaActiva($s);
        });

        return response()->json([
            'message' => 'Visitas activas obtenidas correctamente.',
            'data'    => $data,
        ]);
    }

    public function confirmarLlegada($id)
    {
        $solicitud = Solicitud::where('id_solicitante', auth()->user()->idSam())
            ->with('solicitudVisitantes.qr')
            ->findOrFail($id);

        $flujo    = new FlujoAccesoService();
        $registro = $flujo->registroEntradaActivoParaSolicitud($solicitud);

        if ($registro?->hora_llegada_institucion
            && (int) $solicitud->id_estado_solicitud < FlujoAccesoService::ESTADO_EN_INSTITUCION) {
            $flujo->marcarEnInstitucion($solicitud);
        }

        try {
            $flujo->registrarLlegadaEncuentro($solicitud, $registro);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $solicitud->refresh();

        return response()->json([
            'message' => 'Llegada al encuentro registrada correctamente.',
            'data'    => $flujo->formatearVisitaActiva($solicitud),
        ]);
    }

    public function confirmarSalida($id)
    {
        $solicitud = Solicitud::where('id_solicitante', auth()->user()->idSam())
            ->with('solicitudVisitantes.qr')
            ->findOrFail($id);

        $flujo    = new FlujoAccesoService();
        $registro = $flujo->registroEntradaActivoParaSolicitud($solicitud);

        if ((int) $solicitud->id_estado_solicitud < FlujoAccesoService::ESTADO_EN_ENCUENTRO
            && ($solicitud->hora_llegada_encuentro || $registro?->hora_llegada_encuentro)) {
            if ((int) $solicitud->id_estado_solicitud < FlujoAccesoService::ESTADO_EN_INSTITUCION) {
                $flujo->marcarEnInstitucion($solicitud);
            }
            if ((int) $solicitud->id_estado_solicitud < FlujoAccesoService::ESTADO_EN_ENCUENTRO) {
                $flujo->registrarLlegadaEncuentro($solicitud, $registro);
            }
        }

        try {
            $flujo->registrarSalidaEncuentro($solicitud, $registro);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $solicitud->refresh();

        return response()->json([
            'message' => 'Salida del encuentro registrada. Visitante en tránsito a salida.',
            'data'    => $flujo->formatearVisitaActiva($solicitud),
        ]);
    }

    public function pendientes(Request $request)
    {
        $this->cancelarPendientesVencidas();

        $query = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante']);
        $this->aplicarFiltroSolicitantesAutorizador($query);
        $query->where('id_estado_solicitud', 1);
        $query->filtrarPendientesAutorizador([
            'solicitante' => $request->get('solicitante'),
            'correo'      => $request->get('correo'),
            'fecha'       => $request->get('fecha'),
            'hora'        => $request->get('hora'),
        ]);

        $solicitudes = $query->orderBy('fecha_inicio', 'asc')->paginate(10);
        $solicitudes->getCollection()->transform(fn($s) => $this->formatearSolicitudParaMovil($s));

        return response()->json(['message' => 'Solicitudes obtenidas correctamente.', 'data' => $solicitudes]);
    }

    public function autorizar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.visitante')->findOrFail($id);

        if (!$this->puedeGestionarComoAutorizador($solicitud)) {
            return response()->json([
                'message' => 'No puede autorizar sus propias solicitudes ni solicitudes fuera de su ambito.',
                'data'    => null,
            ], 403);
        }

        if ((int) $solicitud->id_estado_solicitud !== 1) {
            return response()->json(['message' => 'Esta solicitud ya fue procesada.', 'data' => null], 422);
        }

        if ($this->diaEncuentroPasado($solicitud)) {
            return response()->json(['message' => 'No se puede autorizar: la fecha del encuentro ya paso.', 'data' => null], 422);
        }

        if ($this->solicitudYaVencio($solicitud)) {
            $solicitud->update(['id_estado_solicitud' => 4, 'fecha_cancelacion' => now()]);
            return response()->json(['message' => 'Esta solicitud ya vencio y fue cancelada automaticamente.', 'data' => null], 422);
        }

        $solicitud->update(['id_estado_solicitud' => 2, 'id_autorizador' => $this->idEmpleado()]);

        foreach ($solicitud->solicitudVisitantes as $sv) {
            if ($sv->qr) continue;

            $inicio = date('Y-m-d H:i:s', strtotime($solicitud->fecha_inicio . ' -' . $solicitud->tolerancia_antes . ' minutes'));
            $fin    = date('Y-m-d H:i:s', strtotime($solicitud->fecha_inicio . ' +' . $solicitud->tolerancia_despues . ' minutes'));

            QR::create([
                'codigo_numerico'        => QR::generarCodigo(),
                'vigencia_inicio'        => $inicio,
                'vigencia_final'         => $fin,
                'prorroga_tolerancia'    => false,
                'id_estadoQr'            => 1,
                'id_solicitud_visitante' => $sv->id_solicitud_visitante,
            ]);
        }

        Notificacion::create([
            'id_empleado'  => $solicitud->id_solicitante,
            'id_solicitud' => $solicitud->id_solicitud,
            'tipo'         => 'autorizada',
            'mensaje'      => "Tu solicitud {$solicitud->folio} ha sido autorizada.",
            'leida'        => false,
        ]);

        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante'])->findOrFail($id);
        $this->formatearSolicitudParaMovil($solicitud);

        return response()->json(['message' => 'Solicitud autorizada correctamente.', 'data' => $solicitud]);
    }

    public function rechazar($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if (!$this->puedeGestionarComoAutorizador($solicitud)) {
            return response()->json([
                'message' => 'No puede rechazar sus propias solicitudes ni solicitudes fuera de su ambito.',
                'data'    => null,
            ], 403);
        }

        if ((int) $solicitud->id_estado_solicitud !== 1) {
            return response()->json(['message' => 'Esta solicitud ya fue procesada.', 'data' => null], 422);
        }

        $solicitud->update(['id_estado_solicitud' => 3]);

        Notificacion::create([
            'id_empleado'  => $solicitud->id_solicitante,
            'id_solicitud' => $solicitud->id_solicitud,
            'tipo'         => 'rechazada',
            'mensaje'      => "Tu solicitud {$solicitud->folio} fue rechazada.",
            'leida'        => false,
        ]);

        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante'])->findOrFail($id);
        $this->formatearSolicitudParaMovil($solicitud);

        return response()->json(['message' => 'Solicitud rechazada correctamente.', 'data' => $solicitud]);
    }
}