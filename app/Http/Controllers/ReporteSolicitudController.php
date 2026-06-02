<?php

namespace App\Http\Controllers;

use App\Models\CaTipoSolicitud;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteSolicitudController extends Controller
{
    public function index(Request $request)
    {
        $esAdmin = $request->routeIs('admin.*');

        $filtros = [
            'estado'      => $request->get('estado'),
            'solicitante' => $request->get('solicitante'),
            'autorizador' => $esAdmin ? $request->get('autorizador') : null,
            'fecha'       => $esAdmin ? $request->get('fecha') : null,
            'hora'        => $esAdmin ? $request->get('hora') : null,
            'tipo'        => $request->get('tipo'),
            'desde'       => $request->get('desde'),
            'hasta'       => $request->get('hasta'),
        ];

        $query = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante', 'autorizador', 'solicitudVisitantes.qr']);

        if (!$esAdmin) {
            $query->where('id_autorizador', Auth::user()->idSam());
        }

        $query->filtrarReporteSolicitudes($filtros);

        $solicitudes = $query
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(25)
            ->withQueryString();

        $tipos = CaTipoSolicitud::orderBy('nombre')->get();

        $rutaReportes  = $esAdmin ? route('admin.todas-solicitudes') : route('autorizador.reportes');
        $rutaRegresar  = $esAdmin ? route('admin.reportes') : route('autorizador.index');
        $tituloPagina  = $esAdmin ? 'Reporte de Solicitudes' : 'Mis solicitudes autorizadas';

        return view('reportes.solicitudes', compact(
            'solicitudes', 'filtros', 'tipos', 'esAdmin', 'rutaReportes', 'rutaRegresar', 'tituloPagina'
        ));
    }
}
