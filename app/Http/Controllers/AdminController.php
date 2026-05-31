<?php

namespace App\Http\Controllers;

use App\Models\ListaExclusion;
use App\Models\Solicitud;
use App\Models\RegistroAcceso;
use App\Models\Visitante;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function reportes()
    {
        $totalSolicitudes   = Solicitud::count();
        $pendientes         = Solicitud::where('id_estado_solicitud', 1)->count();
        $autorizadas        = Solicitud::where('id_estado_solicitud', 2)->count();
        $rechazadas         = Solicitud::where('id_estado_solicitud', 3)->count();
        $canceladas         = Solicitud::where('id_estado_solicitud', 4)->count();
        $totalAccesos       = RegistroAcceso::count();
        $visitantesActivos  = RegistroAcceso::whereNull('hora_salida_institucion')->count();
        $ultimasSolicitudes = Solicitud::with(['estado', 'tipo', 'solicitante'])
            ->orderBy('fecha_creacion', 'desc')
            ->take(10)
            ->get();

        return view('admin.reportes', compact(
            'totalSolicitudes', 'pendientes', 'autorizadas',
            'rechazadas', 'canceladas', 'totalAccesos',
            'visitantesActivos', 'ultimasSolicitudes'
        ));
    }

    public function storeExclusion(Request $request)
    {
        $request->validate([
            'id_visitante'     => 'required|exists:visitante,id_visitante',
            'motivo_exclusion' => 'required|string|min:10',
        ]);

        ListaExclusion::create([
            'id_visitante'     => $request->id_visitante,
            'id_autorizador'   => auth()->id(),
            'motivo_exclusion' => $request->motivo_exclusion,
        ]);

        return redirect()->route('admin.exclusiones')
            ->with('success', 'Visitante agregado a la lista de exclusión correctamente.');
    }

    public function destroyExclusion($id)
    {
        $exclusion = ListaExclusion::findOrFail($id);
        $exclusion->delete();

        return redirect()->route('admin.exclusiones')
            ->with('success', 'Registro eliminado de la lista de exclusión correctamente.');
    }

    public function visitantesActivos()
    {
        $visitantes = RegistroAcceso::with([
            'qr.solicitudVisitante.visitante',
            'qr.solicitudVisitante.solicitud.solicitante',
        ])
            ->whereNull('hora_salida_institucion')
            ->whereNotNull('hora_llegada_institucion')
            ->orderBy('hora_llegada_institucion', 'desc')
            ->get();

        return view('admin.visitantes-activos', compact('visitantes'));
    }



        
        public function registrarSalida(Request $request)
    {
        $request->validate(['id_qr' => 'required|integer']);

        $registro = RegistroAcceso::where('id_qr', $request->id_qr)
            ->whereNull('hora_salida_institucion')
            ->orderBy('id_registro', 'desc')
            ->first();

        if (!$registro) {
            return redirect()->route('admin.visitantes-activos')
                ->with('error', 'No se encontró entrada registrada.');
        }

        $registro->update([
            'hora_salida_institucion' => now(),
            'telefono_vigilante'      => '0000000000',
            'area_vigilante'          => 'Admin — Salida forzada',
        ]);

        \App\Models\QR::where('id_qr', $request->id_qr)->update(['id_estadoQr' => 3]);

        return redirect()->route('admin.visitantes-activos')
            ->with('success', 'Salida registrada correctamente.');
    }

    public function exclusiones(Request $request)
    {
        $buscar = $request->get('buscar');
        $desde  = $request->get('desde');
        $hasta  = $request->get('hasta');

        $query = ListaExclusion::with('visitante')
            ->orderBy('fecha_bloqueo', 'desc');

        if ($buscar) {
            $query->whereHas('visitante', function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('apellidos', 'like', "%{$buscar}%")
                ->orWhere('correo_personal', 'like', "%{$buscar}%");
            })->orWhere('motivo_exclusion', 'like', "%{$buscar}%");
        }

        if ($desde) {
            $query->whereDate('fecha_bloqueo', '>=', $desde);
        }

        if ($hasta) {
            $query->whereDate('fecha_bloqueo', '<=', $hasta);
        }

        $exclusiones = $query->paginate(10)->withQueryString();

        $visitantes = Visitante::whereNotIn('id_visitante',
            ListaExclusion::pluck('id_visitante')
        )->get();

        return view('admin.exclusiones', compact('exclusiones', 'visitantes', 'buscar', 'desde', 'hasta'));
    }






}