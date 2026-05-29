<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Rules\AnticipacionMinimaVisita;
use App\Models\Notificacion;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\Visitante;
use Illuminate\Http\Request;

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

        $solicitud->correo_solicitante = $solicitud->solicitante->email
            ?? '';

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
            default => 'Sin tipo',
        };
    }

    public function index(Request $request)
    {
        $query = Solicitud::where('id_solicitante', $this->idEmpleado())
            ->with([
                'estado',
                'tipo',
                'visitantes',
                'solicitante',
            ]);

        $estado = $request->get('estado');

        if ($estado) {
            $estado = strtolower($estado);

            $mapaEstados = [
                'pendiente'  => 1,
                'autorizada' => 2,
                'rechazada'  => 3,
                'cancelada'  => 4,
            ];

            if (isset($mapaEstados[$estado])) {
                $query->where('id_estado_solicitud', $mapaEstados[$estado]);
            }
        }

        $solicitudes = $query
            ->orderBy('fecha_creacion', 'desc')
            ->paginate(10);

        $solicitudes->getCollection()->transform(function ($solicitud) {
            return $this->formatearSolicitudParaMovil($solicitud);
        });

        return response()->json([
            'message' => 'Solicitudes obtenidas correctamente.',
            'data'    => $solicitudes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_inicio'           => ['required', 'date', new AnticipacionMinimaVisita(1)],
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
                [
                    'nombre'    => $v['nombre'],
                    'apellidos' => $v['apellidos'],
                ]
            );

            SolicitudVisitante::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_visitante' => $visitante->id_visitante,
            ]);
        }

        $solicitud = Solicitud::with([
            'estado',
            'tipo',
            'visitantes',
            'solicitante',
        ])->findOrFail($solicitud->id_solicitud);

        $this->formatearSolicitudParaMovil($solicitud);

        return response()->json([
            'message' => 'Solicitud creada correctamente.',
            'data'    => $solicitud,
        ], 201);
    }

    public function show($id)
    {
        $solicitud = Solicitud::with([
            'estado',
            'tipo',
            'visitantes',
            'solicitudVisitantes.qr',
            'solicitante',
        ])->findOrFail($id);

        $this->formatearSolicitudParaMovil($solicitud);

        return response()->json([
            'message' => 'Solicitud obtenida correctamente.',
            'data'    => $solicitud,
        ]);
    }

    public function cancelar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

        if (!$solicitud->esCancelable()) {
            return response()->json([
                'message' => 'Esta solicitud no puede cancelarse en su estado actual.',
                'data'    => null,
            ], 422);
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

        return response()->json([
            'message' => 'Solicitud cancelada correctamente.',
            'data'    => $solicitud,
        ]);
    }

    public function qr($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 2) {
            return response()->json([
                'message' => 'La solicitud no está autorizada.',
                'data'    => null,
            ], 422);
        }

        $qrs = $solicitud->solicitudVisitantes
            ->map(function ($sv) {
                return $sv->qr;
            })
            ->filter()
            ->values();

        return response()->json([
            'message' => 'QR obtenido correctamente.',
            'data'    => $qrs,
        ]);
    }

    public function enviarQR($id)
{
    $solicitud = Solicitud::with([
        'estado',
        'tipo',
        'visitantes',
        'solicitudVisitantes.qr',
        'solicitante',
    ])->findOrFail($id);

    if ($solicitud->id_estado_solicitud !== 2) {
        return response()->json([
            'message' => 'Solo se puede enviar el QR cuando la solicitud está autorizada.',
            'data'    => null,
        ], 422);
    }

    $qrs = $solicitud->solicitudVisitantes
        ->map(function ($sv) {
            return $sv->qr;
        })
        ->filter()
        ->values();

    if ($qrs->isEmpty()) {
        return response()->json([
            'message' => 'No se encontró un QR asociado a esta solicitud.',
            'data'    => null,
        ], 404);
    }

    $this->formatearSolicitudParaMovil($solicitud);

    return response()->json([
        'message' => 'QR listo para compartir con el visitante.',
        'data'    => [
            'solicitud' => $solicitud,
            'qrs'       => $qrs,
        ],
    ]);
}

    public function pendientes(Request $request)
    {
        $filtro = $request->get('filtro', 'pendientes');

        $query = Solicitud::with([
            'estado',
            'tipo',
            'visitantes',
            'solicitante',
        ]);

        match ($filtro) {
            'aprobadas'  => $query->where('id_estado_solicitud', 2),
            'rechazadas' => $query->where('id_estado_solicitud', 3),
            'todos'      => null,
            default      => $query->where('id_estado_solicitud', 1),
        };

        $solicitudes = $query
            ->orderBy('fecha_creacion', 'desc')
            ->paginate(10);

        $solicitudes->getCollection()->transform(function ($solicitud) {
            return $this->formatearSolicitudParaMovil($solicitud);
        });

        return response()->json([
            'message' => 'Solicitudes obtenidas correctamente.',
            'data'    => $solicitudes,
        ]);
    }

    public function autorizar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.visitante')->findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 1) {
            return response()->json([
                'message' => 'Esta solicitud ya fue procesada.',
                'data'    => null,
            ], 422);
        }

        $solicitud->update(['id_estado_solicitud' => 2]);

        foreach ($solicitud->solicitudVisitantes as $sv) {
            if ($sv->qr) {
                continue;
            }

            $inicio = date(
                'Y-m-d H:i:s',
                strtotime($solicitud->fecha_inicio . ' -' . $solicitud->tolerancia_antes . ' minutes')
            );

            $fin = date(
                'Y-m-d H:i:s',
                strtotime($solicitud->fecha_inicio . ' +' . $solicitud->tolerancia_despues . ' minutes')
            );

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

        $solicitud = Solicitud::with([
            'estado',
            'tipo',
            'visitantes',
            'solicitante',
        ])->findOrFail($id);

        $this->formatearSolicitudParaMovil($solicitud);

        return response()->json([
            'message' => 'Solicitud autorizada correctamente.',
            'data'    => $solicitud,
        ]);
    }

    public function rechazar($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 1) {
            return response()->json([
                'message' => 'Esta solicitud ya fue procesada.',
                'data'    => null,
            ], 422);
        }

        $solicitud->update(['id_estado_solicitud' => 3]);

        Notificacion::create([
            'id_empleado'  => $solicitud->id_solicitante,
            'id_solicitud' => $solicitud->id_solicitud,
            'tipo'         => 'rechazada',
            'mensaje'      => "Tu solicitud {$solicitud->folio} fue rechazada.",
            'leida'        => false,
        ]);

        $solicitud = Solicitud::with([
            'estado',
            'tipo',
            'visitantes',
            'solicitante',
        ])->findOrFail($id);

        $this->formatearSolicitudParaMovil($solicitud);

        return response()->json([
            'message' => 'Solicitud rechazada correctamente.',
            'data'    => $solicitud,
        ]);
    }




// ****************************************


    // Enviar QR por correo
    public function enviarQR($id)
    {
        $solicitud = Solicitud::with(['solicitudVisitantes.qr', 'solicitudVisitantes.visitante'])
            ->findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 2) {
            return response()->json([
                'message' => 'La solicitud debe estar autorizada para enviar el QR.',
                'data'    => null,
            ], 422);
        }

        // Validar que la visita no haya pasado
        if (now() > \Carbon\Carbon::parse($solicitud->fecha_inicio)) {
            return response()->json([
                'message' => 'No se puede enviar el QR, la fecha de la visita ya paso.',
                'data'    => null,
            ], 422);
        }

        $enviados = 0;
        $errores  = 0;

        foreach ($solicitud->solicitudVisitantes as $sv) {
            $qr     = $sv->qr;
            $correo = $sv->visitante->correo_personal ?? null;

            if (!$qr || !$correo) continue;

            try {
                \Illuminate\Support\Facades\Mail::to($correo)
                    ->send(new \App\Mail\EnviarQRMail($qr));
                $enviados++;
            } catch (\Throwable $e) {
                $errores++;
                \Illuminate\Support\Facades\Log::error('Error enviando QR API: ' . $e->getMessage());
            }
        }

        if ($enviados === 0) {
            return response()->json([
                'message' => 'No se pudo enviar el QR.',
                'data'    => null,
            ], 500);
        }

        return response()->json([
            'message' => "QR enviado correctamente a {$enviados} visitante(s).",
            'data'    => ['enviados' => $enviados, 'errores' => $errores],
        ]);
    }

    // Reenviar QR (máximo 3 veces)
    public function reenviarQR($id)
    {
        $solicitud = Solicitud::with(['solicitudVisitantes.qr', 'solicitudVisitantes.visitante'])
            ->findOrFail($id);

        if (($solicitud->reenvios_qr ?? 0) >= 3) {
            return response()->json([
                'message' => 'Se alcanzo el limite de 3 reenvios.',
                'data'    => ['reenvios_restantes' => 0],
            ], 422);
        }

        if (now() > \Carbon\Carbon::parse($solicitud->fecha_inicio)) {
            return response()->json([
                'message' => 'No se puede reenviar el QR, la fecha de la visita ya paso.',
                'data'    => null,
            ], 422);
        }

        $enviados = 0;

        foreach ($solicitud->solicitudVisitantes as $sv) {
            $qr     = $sv->qr;
            $correo = $sv->visitante->correo_personal ?? null;

            if (!$qr || !$correo) continue;

            try {
                \Illuminate\Support\Facades\Mail::to($correo)
                    ->send(new \App\Mail\EnviarQRMail($qr));
                $enviados++;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error reenviando QR API: ' . $e->getMessage());
            }
        }

        $solicitud->increment('reenvios_qr');
        $restantes = 3 - $solicitud->reenvios_qr;

        return response()->json([
            'message' => "QR reenviado. Reenvios restantes: {$restantes}",
            'data'    => ['reenvios_restantes' => $restantes],
        ]);
    }

    // Extender QR vencido
    public function extenderQR(Request $request, $id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);
        $minutos   = $request->input('minutos_extra', 60);
        $extendidos = 0;

        foreach ($solicitud->solicitudVisitantes as $sv) {
            if ($sv->qr && in_array($sv->qr->id_estadoQr, [1, 2])) {
                $sv->qr->update([
                    'vigencia_final' => \Carbon\Carbon::parse($sv->qr->vigencia_final)->addMinutes($minutos),
                    'id_estadoQr'    => 3, // Extendido
                ]);
                $extendidos++;
            }
        }

        return response()->json([
            'message' => "QR extendido {$minutos} minutos para {$extendidos} visitante(s).",
            'data'    => ['minutos_extra' => $minutos, 'extendidos' => $extendidos],
        ]);
    }

    // Visitas activas del solicitante
    public function activas(Request $request)
    {
        $idEmpleado = $request->user()->idSam();

        $solicitudes = Solicitud::where('id_solicitante', $idEmpleado)
            ->where('id_estado_solicitud', 2)
            ->where('fecha_inicio', '>=', now())
            ->with(['visitantes', 'estado', 'tipo'])
            ->get();

        $data = $solicitudes->map(function ($s) {
            return [
                'id_solicitud'     => $s->id_solicitud,
                'folio'            => $s->folio,
                'fecha_inicio'     => $s->fecha_inicio,
                'lugar_encuentro'  => $s->lugar_encuentro,
                'motivo_visita'    => $s->motivo_visita,
                'estado'           => $s->estado->nombre ?? '',
                'tipo'             => $s->tipo->nombre ?? '',
                'visitantes'       => $s->visitantes->map(fn($v) => [
                    'nombre'          => $v->nombre,
                    'apellidos'       => $v->apellidos,
                    'correo_personal' => $v->correo_personal,
                ]),
            ];
        });

        return response()->json([
            'message' => 'Visitas activas obtenidas correctamente.',
            'data'    => $data,
        ]);
    }


//************************************************
    



}