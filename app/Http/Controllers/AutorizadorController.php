<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutorizadorController extends Controller
{
    // ─── Listado con filtros ──────────────────────────────────────

    public function index(Request $request)
    {
        $filtro = $request->get('filtro', 'pendientes');

        $query = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante']);

        match($filtro) {
            'aprobadas'  => $query->where('id_estado_solicitud', 2),
            'rechazadas' => $query->where('id_estado_solicitud', 3),
            'todos'      => null,
            default      => $query->where('id_estado_solicitud', 1),
        };

        $solicitudes = $query->orderBy('fecha_creacion', 'desc')->paginate(10);

        return view('autorizador.index', compact('solicitudes', 'filtro'));
    }

    // ─── Autorizar ────────────────────────────────────────────────

    public function autorizar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.visitante')->findOrFail($id);

        // Validar que siga pendiente (RF24 / RF28)
        if ($solicitud->id_estado_solicitud !== 1) {
            return redirect()->route('autorizador.index')
                ->with('error', 'Esta solicitud ya fue procesada.');
        }

        $solicitud->update(['id_estado_solicitud' => 2]);

        foreach ($solicitud->solicitudVisitantes as $sv) {
            // No generar QR si ya existe (evita duplicados)
            if ($sv->qr) continue;

            // Validar que la solicitud NO esté cancelada o rechazada (RF24)
            if (in_array($solicitud->id_estado_solicitud, [3, 4])) continue;

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

        // Notificar al solicitante (RF30 / RF59)
        Notificacion::create([
            'id_empleado'  => $solicitud->id_solicitante,
            'id_solicitud' => $solicitud->id_solicitud,
            'tipo'         => 'autorizada',
            'mensaje'      => "Tu solicitud {$solicitud->folio} ha sido autorizada. Ya puedes compartir el código QR.",
            'leida'        => false,
        ]);

        return redirect()->route('autorizador.index')
            ->with('success', 'Solicitud autorizada y QR generado correctamente.');
    }

    // ─── Rechazar ─────────────────────────────────────────────────

    public function rechazar($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 1) {
            return redirect()->route('autorizador.index')
                ->with('error', 'Esta solicitud ya fue procesada.');
        }

        $solicitud->update(['id_estado_solicitud' => 3]);

        Notificacion::create([
            'id_empleado'  => $solicitud->id_solicitante,
            'id_solicitud' => $solicitud->id_solicitud,
            'tipo'         => 'rechazada',
            'mensaje'      => "Tu solicitud {$solicitud->folio} fue rechazada.",
            'leida'        => false,
        ]);

        return redirect()->route('autorizador.index')
            ->with('success', 'Solicitud rechazada correctamente.');
    }

}