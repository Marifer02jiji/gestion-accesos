<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Services\AutorizacionVisitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutorizadorController extends Controller
{
    public function __construct(
        private readonly AutorizacionVisitaService $autorizacionVisita
    ) {
    }

    private function idEmpleado(): int
    {
        return Auth::user()->idSam();
    }

    private function solicitantesVisibles(): array
    {
        return $this->autorizacionVisita->idsSolicitantesAutorizables(
            $this->idEmpleado(),
            (string) (Auth::user()->name ?? '')
        );
    }

    private function aplicarFiltroSolicitantes($query)
    {
        $idPropio  = $this->idEmpleado();
        $visibles  = array_values(array_filter(
            $this->solicitantesVisibles(),
            fn ($id) => (int) $id > 0 && (int) $id !== $idPropio
        ));

        if (!empty($visibles)) {
            return $query->whereIn('id_solicitante', $visibles);
        }

        return $query->where('id_solicitante', '!=', $idPropio);
    }

    public function index(Request $request)
    {
        $solicitante = $request->get('solicitante');
        $correo      = $request->get('correo');
        $fecha       = $request->get('fecha');
        $hora        = $request->get('hora');

        $query = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante']);
        $query = $this->aplicarFiltroSolicitantes($query);
        $query->where('id_estado_solicitud', 1);
        $query->filtrarPendientesAutorizador([
            'solicitante' => $solicitante,
            'correo'      => $correo,
            'fecha'       => $fecha,
            'hora'        => $hora,
        ]);

        $solicitudes = $query
            ->orderBy('fecha_inicio', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('autorizador.index', compact('solicitudes', 'solicitante', 'correo', 'fecha', 'hora'));
    }

    public function autorizar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.visitante')->findOrFail($id);

        if (!$this->autorizacionVisita->puedeGestionarSolicitud(
            $this->idEmpleado(),
            (string) (Auth::user()->name ?? ''),
            (int) $solicitud->id_solicitante
        )) {
            return redirect()->route('autorizador.index')
                ->with('error', 'No tienes permiso para autorizar esta solicitud.');
        }

        if ($solicitud->id_estado_solicitud !== 1) {
            return redirect()->route('autorizador.index')
                ->with('error', 'Esta solicitud ya fue procesada.');
        }

        // Validar que la fecha no haya pasado
        if (now() > \Carbon\Carbon::parse($solicitud->fecha_inicio)) {
            return redirect()->route('autorizador.index')
                ->with('error', 'No se puede autorizar esta solicitud, la fecha de visita ya paso.');
        }

        $solicitud->update([
            'id_estado_solicitud' => 2,
            'id_autorizador'      => $this->idEmpleado(),
        ]);

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
            'mensaje'      => "Tu solicitud {$solicitud->folio} ha sido autorizada. Ya puedes compartir el codigo QR.",
            'leida'        => false,
        ]);

        return redirect()->route('autorizador.index')
            ->with('success', 'Solicitud autorizada y QR generado correctamente.');
    }

    public function rechazar($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if (!$this->autorizacionVisita->puedeGestionarSolicitud(
            $this->idEmpleado(),
            (string) (Auth::user()->name ?? ''),
            (int) $solicitud->id_solicitante
        )) {
            return redirect()->route('autorizador.index')
                ->with('error', 'No tienes permiso para rechazar esta solicitud.');
        }

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

    public function historial(Request $request)
    {
        $filtro = $request->get('filtro', 'todas');
        $desde  = $request->get('desde');
        $hasta  = $request->get('hasta');

        $query = Solicitud::where('id_autorizador', $this->idEmpleado())
            ->where('id_estado_solicitud', '!=', 1) // No incluir pendientes
            ->with(['estado', 'tipo', 'visitantes', 'solicitante']);

        // Aplicar filtro por estado
        switch($filtro) {
            case 'autorizadas':
                $query->where('id_estado_solicitud', 2);
                break;
            case 'finalizadas':
                $query->where('id_estado_solicitud', 8);
                break;
            case 'canceladas':
                $query->where('id_estado_solicitud', 4);
                break;
            case 'rechazadas':
                $query->where('id_estado_solicitud', 3);
                break;
            // 'todas' no aplica filtro adicional
        }

        // Aplicar filtro de fechas si existen
        if ($desde) {
            $query->whereDate('fecha_inicio', '>=', $desde);
        }

        if ($hasta) {
            $query->whereDate('fecha_inicio', '<=', $hasta);
        }

        $solicitudes = $query
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('autorizador.historial', compact('solicitudes', 'filtro', 'desde', 'hasta'));
    }
}