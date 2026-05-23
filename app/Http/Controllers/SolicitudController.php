<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitudRequest;
use App\Models\CaMotivoVisita;
use App\Models\CaTipoSolicitud;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\Visitante;
use Illuminate\Support\Facades\Auth;

class SolicitudController extends Controller
{
    private function idEmpleado(): int
    {
        return Auth::user()->idSam();
    }

    // ─── Listado ──────────────────────────────────────────────────

    public function index()
    {
        $solicitudes = Solicitud::where('id_solicitante', $this->idEmpleado())
            ->with(['estado', 'tipo'])
            ->orderBy('fecha_creacion', 'desc')
            ->paginate(10);

        return view('solicitudes.index', compact('solicitudes'));
    }

    // ─── Formulario ───────────────────────────────────────────────

    public function create()
    {
        $tipos   = CaTipoSolicitud::all();
        $motivos = CaMotivoVisita::activos()->orderBy('nombre')->get();

        // Si no carga el catálogo de motivos
        if ($motivos->isEmpty()) {
            return view('solicitudes.create', compact('tipos', 'motivos'))
                ->with('error_motivos', 'No fue posible cargar los motivos de visita.');
        }

        return view('solicitudes.create', compact('tipos', 'motivos'));
    }

    // ─── Guardar ──────────────────────────────────────────────────

    public function store(StoreSolicitudRequest $request)
    {
        $solicitud = Solicitud::create([
            'folio'               => Solicitud::generarFolio(),
            'fecha_inicio'        => $request->fecha_inicio,
            'lugar_encuentro'     => $request->lugar_encuentro,
            'motivo_visita'       => $request->motivo_visita,
            'id_tipo_solicitud'   => $request->id_tipo_solicitud,
            'tolerancia_antes'    => $request->tolerancia_antes,
            'tolerancia_despues'  => $request->tolerancia_antes,
            'numero_visitantes'   => count($request->visitante_correo),
            'id_estado_solicitud' => 1,
            'id_solicitante'      => $this->idEmpleado(),
        ]);

        foreach ($request->visitante_correo as $index => $correo) {
            $visitante = Visitante::firstOrCreate(
                ['correo_personal' => $correo],
                [
                    'nombre'    => $request->visitante_nombre[$index],
                    'apellidos' => $request->visitante_apellidos[$index],
                ]
            );

            SolicitudVisitante::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_visitante' => $visitante->id_visitante,
            ]);
        }

        return redirect()->route('solicitudes.index')
            ->with('success', "Solicitud creada correctamente. Folio: {$solicitud->folio}");
    }

    // ─── Detalle ──────────────────────────────────────────────────

    public function show($id)
    {
        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitudVisitantes.qr'])
            ->findOrFail($id);

        return view('solicitudes.show', compact('solicitud'));
    }

    // ─── Cancelar ─────────────────────────────────────────────────

    public function cancelar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

        if (!$solicitud->esCancelable()) {
            return redirect()->route('solicitudes.index')
                ->with('error', 'Esta solicitud no puede cancelarse en su estado actual.');
        }

        // Cancelar todos los QR activos asociados
        foreach ($solicitud->solicitudVisitantes as $sv) {
            if ($sv->qr && $sv->qr->id_estadoQr === 1) {
                $sv->qr->update(['id_estadoQr' => 4]); // 4 = Cancelado
            }
        }

        $solicitud->update([
            'id_estado_solicitud' => 4,
            'cancelado_por'       => $this->idEmpleado(),
            'fecha_cancelacion'   => now(),
        ]);

        return redirect()->route('solicitudes.index')
            ->with('success', 'Solicitud cancelada correctamente.');
    }

    // ─── Eliminar ─────────────────────────────────────────────────

    public function destroy($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if (!in_array($solicitud->id_estado_solicitud, [3, 4])) {
            return redirect()->route('solicitudes.index')
                ->with('error', 'Solo se pueden eliminar solicitudes canceladas o rechazadas.');
        }

        $solicitud->visitantes()->detach();
        $solicitud->delete();

        return redirect()->route('solicitudes.index')
            ->with('success', 'Solicitud eliminada correctamente.');
    }

    public function edit($id)   { return redirect()->route('solicitudes.show', $id); }
    public function update(\Illuminate\Http\Request $r, $id) { return redirect()->route('solicitudes.show', $id); }
}