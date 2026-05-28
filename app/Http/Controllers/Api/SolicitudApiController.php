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
}