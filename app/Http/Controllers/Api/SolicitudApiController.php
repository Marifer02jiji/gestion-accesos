<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Rules\AnticipacionMinimaVisita;
use App\Models\Notificacion;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\Visitante;
use App\Mail\EnviarQRMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SolicitudApiController extends Controller
{
    private function idEmpleado(): int
    {
        return auth()->user()->idSam();
    }

    private function formatearSolicitudParaMovil($solicitud)
    {
        $solicitud->nombre_solicitante = $solicitud->solicitante->name
            ?? $solicitud->solicitante->nombre
            ?? 'Sin nombre';

        $solicitud->correo_solicitante = $solicitud->solicitante->email ?? '';

        $solicitud->tipo_visita = $solicitud->tipo->nombre
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

                if ($hora < 6 || $hora >= 21) {
                    $fail('Las visitas solo pueden agendarse entre las 6:00 AM y las 9:00 PM.');
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
            $visitante = Visitante::firstOrCreate(
                ['correo_personal' => $v['correo']],
                ['nombre' => $v['nombre'], 'apellidos' => $v['apellidos']]
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
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

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

        return response()->json(['message' => 'Solicitud cancelada correctamente.', 'data' => $solicitud]);
    }

    public function qr($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 2) {
            return response()->json(['message' => 'La solicitud no esta autorizada.', 'data' => null], 422);
        }

        $qrs = $solicitud->solicitudVisitantes->map(fn($sv) => $sv->qr)->filter()->values();

        return response()->json(['message' => 'QR obtenido correctamente.', 'data' => $qrs]);
    }

    public function enviarQR($id)
    {
        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitudVisitantes.qr', 'solicitudVisitantes.visitante', 'solicitante'])->findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 2) {
            return response()->json(['message' => 'La solicitud debe estar autorizada para enviar el QR.', 'data' => null], 422);
        }

        if ($this->solicitudYaVencio($solicitud)) {
            return response()->json(['message' => 'No se puede enviar el QR, la vigencia de la visita ya paso.', 'data' => null], 422);
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
                Log::error('Error enviando QR API: ' . $e->getMessage());
            }
        }

        if ($enviados === 0) {
            return response()->json(['message' => 'No se pudo enviar el QR.', 'data' => ['enviados' => 0, 'errores' => $errores]], 500);
        }

        return response()->json(['message' => "QR enviado correctamente a {$enviados} visitante(s).", 'data' => ['enviados' => $enviados, 'errores' => $errores]]);
    }

    public function reenviarQR($id)
    {
        $solicitud = Solicitud::with(['solicitudVisitantes.qr', 'solicitudVisitantes.visitante'])->findOrFail($id);

        if (($solicitud->reenvios_qr ?? 0) >= 3) {
            return response()->json(['message' => 'Se alcanzo el limite de 3 reenvios.', 'data' => ['reenvios_restantes' => 0]], 422);
        }

        if ($this->solicitudYaVencio($solicitud)) {
            return response()->json(['message' => 'No se puede reenviar el QR, la vigencia ya paso.', 'data' => null], 422);
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
        $solicitud  = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);
        $minutos    = (int) $request->input('minutos_extra', 60);
        $extendidos = 0;

        if ($minutos <= 0) {
            return response()->json(['message' => 'Los minutos extra deben ser mayores a cero.', 'data' => null], 422);
        }

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
    }

    public function activas(Request $request)
    {
        $solicitudes = Solicitud::where('id_solicitante', $request->user()->idSam())
            ->where('id_estado_solicitud', 2)
            ->with(['visitantes', 'estado', 'tipo'])
            ->get()
            ->filter(fn($s) => !$this->solicitudYaVencio($s))
            ->values();

        $data = $solicitudes->map(fn($s) => [
            'id_solicitud'    => $s->id_solicitud,
            'folio'           => $s->folio,
            'fecha_inicio'    => $s->fecha_inicio,
            'lugar_encuentro' => $s->lugar_encuentro,
            'motivo_visita'   => $s->motivo_visita,
            'estado'          => $s->estado->nombre ?? '',
            'tipo'            => $s->tipo->nombre ?? '',
            'visitantes'      => $s->visitantes->map(fn($v) => [
                'nombre'          => $v->nombre,
                'apellidos'       => $v->apellidos,
                'correo_personal' => $v->correo_personal,
            ]),
        ]);

        return response()->json(['message' => 'Visitas activas obtenidas correctamente.', 'data' => $data]);
    }

    public function pendientes(Request $request)
    {
        $this->cancelarPendientesVencidas();

        $filtro = strtolower($request->get('filtro', 'pendientes'));
        $query  = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante']);

        match ($filtro) {
            'autorizadas', 'aprobadas' => $query->where('id_estado_solicitud', 2),
            'rechazadas'               => $query->where('id_estado_solicitud', 3),
            'canceladas'               => $query->where('id_estado_solicitud', 4),
            'todas', 'todos'           => null,
            default                    => $query->where('id_estado_solicitud', 1),
        };

        $solicitudes = $query->orderBy('fecha_creacion', 'desc')->paginate(10);
        $solicitudes->getCollection()->transform(fn($s) => $this->formatearSolicitudParaMovil($s));

        return response()->json(['message' => 'Solicitudes obtenidas correctamente.', 'data' => $solicitudes]);
    }

    public function autorizar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.visitante')->findOrFail($id);

        if ((int) $solicitud->id_estado_solicitud !== 1) {
            return response()->json(['message' => 'Esta solicitud ya fue procesada.', 'data' => null], 422);
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

