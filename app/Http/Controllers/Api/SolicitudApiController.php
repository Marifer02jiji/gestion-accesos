<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\Visitante;
use Illuminate\Http\Request;

class SolicitudApiController extends Controller
{
    // Obtener ID del empleado SAM del usuario autenticado
    private function idEmpleado(): int
    {
        return auth()->user()->idSam();
    }

    // Listar solicitudes del solicitante autenticado
    public function index()
    {
        $solicitudes = Solicitud::where('id_solicitante', $this->idEmpleado())
            ->with(['estado', 'tipo', 'visitantes'])
            ->orderBy('fecha_creacion', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'Solicitudes obtenidas correctamente.',
            'data'    => $solicitudes,
        ]);
    }

    // Crear nueva solicitud
    public function store(Request $request)
    {
        $request->validate([
            'fecha_inicio'          => 'required|date|after:now',
            'lugar_encuentro'       => 'required|string|max:100',
            'motivo_visita'         => 'required|string|max:255',
            'id_tipo_solicitud'     => 'required|exists:ca_TipoSolicitud,id_tipo_solicitud',
            'tolerancia_antes'      => 'required|in:15,30',
            'tolerancia_despues'    => 'required|in:15,30',
            'visitantes'            => 'required|array|min:1',
            'visitantes.*.nombre'   => 'required|string|max:100',
            'visitantes.*.apellidos'=> 'required|string|max:100',
            'visitantes.*.correo'   => 'required|email|max:150',
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

        return response()->json([
            'message' => 'Solicitud creada correctamente.',
            'data'    => $solicitud->load(['estado', 'tipo', 'visitantes']),
        ], 201);
    }

    // Ver detalle de solicitud
    public function show($id)
    {
        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitudVisitantes.qr'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Solicitud obtenida correctamente.',
            'data'    => $solicitud,
        ]);
    }

    // Cancelar solicitud
    public function cancelar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

        if (!$solicitud->esCancelable()) {
            return response()->json([
                'message' => 'Esta solicitud no puede cancelarse en su estado actual.',
                'data'    => null,
            ], 422);
        }

        // Cancelar QRs activos
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

    // Ver QR de solicitud autorizada
    public function qr($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 2) {
            return response()->json([
                'message' => 'La solicitud no está autorizada.',
                'data'    => null,
            ], 422);
        }

        $qrs = $solicitud->solicitudVisitantes->map(function($sv) {
            return $sv->qr;
        })->filter();

        return response()->json([
            'message' => 'QR obtenido correctamente.',
            'data'    => $qrs,
        ]);
    }

    // Listar solicitudes pendientes (autorizador)
    public function pendientes(Request $request)
    {
        $filtro = $request->get('filtro', 'pendientes');

        $query = Solicitud::with(['estado', 'tipo', 'visitantes']);

        match($filtro) {
            'aprobadas'  => $query->where('id_estado_solicitud', 2),
            'rechazadas' => $query->where('id_estado_solicitud', 3),
            'todos'      => null,
            default      => $query->where('id_estado_solicitud', 1),
        };

        $solicitudes = $query->orderBy('fecha_creacion', 'desc')->paginate(10);

        return response()->json([
            'message' => 'Solicitudes obtenidas correctamente.',
            'data'    => $solicitudes,
        ]);
    }

    // Autorizar solicitud
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
            if ($sv->qr) continue;

            $inicio = date('Y-m-d H:i:s', strtotime($solicitud->fecha_inicio . ' -' . $solicitud->tolerancia_antes . ' minutes'));
            $fin    = date('Y-m-d H:i:s', strtotime($solicitud->fecha_inicio . ' +' . $solicitud->tolerancia_despues . ' minutes'));

            $parte1 = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $parte2 = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $codigo = "VIS-{$parte1}-{$parte2}";

            QR::create([
                'codigo_numerico'        => $codigo,
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

        return response()->json([
            'message' => 'Solicitud autorizada correctamente.',
            'data'    => $solicitud,
        ]);
    }

    // Rechazar solicitud
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

        return response()->json([
            'message' => 'Solicitud rechazada correctamente.',
            'data'    => $solicitud,
        ]);
    }
}