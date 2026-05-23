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
use App\Models\Notificacion;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SolicitudApiController extends Controller
{
    public function index(Request $request)
    {
        $solicitudes = Solicitud::where('id_solicitante', $request->user()->id)
            ->with(['estado', 'tipo', 'visitantes'])
            ->orderBy('fecha_creacion', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'Solicitudes obtenidas correctamente.',
            'data'    => $solicitudes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_inicio'          => 'required|date|after:now',
            'lugar_encuentro'       => 'required|string|max:100',
            'motivo_visita'         => 'required|string',
            'id_tipo_solicitud'     => 'required|integer',
            'tolerancia_antes'      => 'required|in:15,30',
            'visitantes'            => 'required|array|min:1',
            'visitantes.*.nombre'   => 'required|string',
            'visitantes.*.apellidos'=> 'required|string',
            'visitantes.*.correo'   => 'required|email',
        ]);

        $solicitud = Solicitud::create([
            'fecha_inicio'        => $request->fecha_inicio,
            'lugar_encuentro'     => $request->lugar_encuentro,
            'motivo_visita'       => $request->motivo_visita,
            'id_tipo_solicitud'   => $request->id_tipo_solicitud,
            'tolerancia_antes'    => $request->tolerancia_antes,
            'tolerancia_despues'  => $request->tolerancia_antes,
            'numero_visitantes'   => count($request->visitantes),
            'id_estado_solicitud' => 1,
            'id_solicitante'      => $request->user()->id,
        ]);

        foreach ($request->visitantes as $v) {
            $visitante = Visitante::firstOrCreate(
                ['correo_personal' => $v['correo']],
                [
                    'nombre'    => $v['nombre'],
                    'apellidos' => $v['apellidos'],
                ]
            );
            $solicitud->visitantes()->attach($visitante->id_visitante);
        }

        return response()->json([
            'message' => 'Solicitud creada correctamente.',
            'data'    => $solicitud->load(['estado', 'tipo', 'visitantes']),
        ], 201);
    }

    public function show($id)
    {
        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Solicitud obtenida correctamente.',
            'data'    => $solicitud,
        ]);
    }

    public function cancelar($id)
    {
        $solicitud = Solicitud::findOrFail($id);
        $solicitud->update(['id_estado_solicitud' => 4]);

        return response()->json([
            'message' => 'Solicitud cancelada correctamente.',
            'data'    => $solicitud,
        ]);
    }

    public function qr($id)
    {
        $sv = SolicitudVisitante::where('id_solicitud', $id)->first();
        $qr = QR::with(['solicitudVisitante.visitante'])
            ->where('id_solicitud_visitante', $sv->id_solicitud_visitante)
            ->first();

        return response()->json([
            'message' => 'QR obtenido correctamente.',
            'data'    => $qr,
        ]);
    }

    public function pendientes(Request $request)
    {
        $filtro = $request->get('filtro', 'pendientes');
        $query  = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante']);

        if ($filtro == 'pendientes') {
            $query->where('id_estado_solicitud', 1);
        } elseif ($filtro == 'aprobadas') {
            $query->where('id_estado_solicitud', 2);
        } elseif ($filtro == 'rechazadas') {
            $query->where('id_estado_solicitud', 3);
        }

        $solicitudes = $query->orderBy('fecha_creacion', 'desc')->paginate(10);

        return response()->json([
            'message' => 'Solicitudes obtenidas correctamente.',
            'data'    => $solicitudes,
        ]);
    }

    public function autorizar($id)
    {
        $solicitud = Solicitud::with('visitantes')->findOrFail($id);
        $solicitud->update(['id_estado_solicitud' => 2]);

        foreach ($solicitud->visitantes as $visitante) {
            $sv = SolicitudVisitante::where('id_solicitud', $solicitud->id_solicitud)
                ->where('id_visitante', $visitante->id_visitante)
                ->first();

            if ($sv) {
                $inicio = date('Y-m-d H:i:s', strtotime($solicitud->fecha_inicio . ' -' . $solicitud->tolerancia_antes . ' minutes'));
                $fin    = date('Y-m-d H:i:s', strtotime($solicitud->fecha_inicio . ' +' . $solicitud->tolerancia_despues . ' minutes'));

                QR::create([
                    'codigo_numerico'        => Str::uuid(),
                    'vigencia_inicio'        => $inicio,
                    'vigencia_final'         => $fin,
                    'id_estadoQr'            => 1,
                    'id_solicitud_visitante' => $sv->id_solicitud_visitante,
                ]);
            }
        }

        Notificacion::create([
            'id_empleado'  => $solicitud->id_solicitante,
            'id_solicitud' => $solicitud->id_solicitud,
            'tipo'         => 'autorizada',
            'mensaje'      => 'Tu solicitud de visita ha sido autorizada.',
            'leida'        => false,
        ]);

        return response()->json([
            'message' => 'Solicitud autorizada correctamente.',
            'data'    => $solicitud,
        ]);
    }

    public function rechazar($id)
    {
        $solicitud = Solicitud::findOrFail($id);
        $solicitud->update(['id_estado_solicitud' => 3]);

        Notificacion::create([
            'id_empleado'  => $solicitud->id_solicitante,
            'id_solicitud' => $solicitud->id_solicitud,
            'tipo'         => 'rechazada',
            'mensaje'      => 'Tu solicitud de visita ha sido rechazada.',
            'leida'        => false,
        ]);

        return response()->json([
            'message' => 'Solicitud rechazada correctamente.',
            'data'    => $solicitud,
        ]);
    }
}