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
        $esVistaAutorizador = $request->routeIs('autorizador.reportes');
        $esAdmin            = !$esVistaAutorizador;

        $filtros = [
            'estado'      => $request->get('estado'),
            'solicitante' => $request->get('solicitante'),
            'correo'      => $esVistaAutorizador ? $request->get('correo') : null,
            'autorizador' => $esAdmin ? $request->get('autorizador') : null,
            'fecha'       => $esVistaAutorizador ? $request->get('fecha') : null,
            'tipo'        => $request->get('tipo'),
            'desde'       => $request->get('desde'),
            'hasta'       => $request->get('hasta'),
        ];

        $query = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitante', 'autorizador', 'solicitudVisitantes.qr']);

        if ($esVistaAutorizador) {
            $idSam  = Auth::user()->idSam();
            $idUser = Auth::user()->id;
            $query->where(function ($q) use ($idSam, $idUser) {
                $q->where('id_autorizador', $idSam);
                if ((int) $idUser !== (int) $idSam) {
                    $q->orWhere('id_autorizador', $idUser);
                }
            });
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
            'solicitudes',
            'filtros',
            'tipos',
            'esAdmin',
            'esVistaAutorizador',
            'rutaReportes',
            'rutaRegresar',
            'tituloPagina'
        ));
    }
}
