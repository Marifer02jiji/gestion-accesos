<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AutorizadorController extends Controller
{
    private function idEmpleado(): int
    {
        return Auth::user()->idSam();
    }

    private function subordinados(): array
    {
        return DB::connection('sam')
            ->table('empleados')
            ->where('jefe', $this->idEmpleado())
            ->pluck('id_empleado')
            ->toArray();
    }

    private function empleadosDepartamento(): array
    {
        $dept = DB::connection('sam')
            ->table('empleados')
            ->where('id_empleado', $this->idEmpleado())
            ->value('id_departamento');

        if (!$dept) return [];

        return DB::connection('sam')
            ->table('empleados')
            ->where('id_departamento', $dept)
            ->pluck('id_empleado')
            ->toArray();
    }

    public function index(Request $request)
    {
        $filtro               = $request->get('filtro', 'pendientes');
        $solicitantesVisibles = array_unique(array_merge(
            $this->subordinados(),
            $this->empleadosDepartamento()
        ));

        $query = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante'])
            ->whereIn('id_solicitante', $solicitantesVisibles)
            ->where('id_solicitante', '!=', $this->idEmpleado());

        match($filtro) {
            'aprobadas'  => $query->where('id_estado_solicitud', 2),
            'rechazadas' => $query->where('id_estado_solicitud', 3),
            'todos'      => null,
            default      => $query->where('id_estado_solicitud', 1),
        };

        $solicitudes = $query->orderBy('fecha_creacion', 'desc')->paginate(10);

        return view('autorizador.index', compact('solicitudes', 'filtro'));
    }

    public function autorizar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.visitante')->findOrFail($id);

        // Validar permiso
        $solicitantesVisibles = array_unique(array_merge(
            $this->subordinados(),
            $this->empleadosDepartamento()
        ));

        if (!in_array($solicitud->id_solicitante, $solicitantesVisibles)) {
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

        // Validar permiso
        $solicitantesVisibles = array_unique(array_merge(
            $this->subordinados(),
            $this->empleadosDepartamento()
        ));

        if (!in_array($solicitud->id_solicitante, $solicitantesVisibles)) {
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
}