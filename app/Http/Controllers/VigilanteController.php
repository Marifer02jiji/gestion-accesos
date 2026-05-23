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

namespace App\Http\Controllers;

use App\Http\Requests\StoreVigilanteEscanearRequest;
use App\Models\QR;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VigilanteController extends Controller
{
    public function index()
    {
        $visitasHoy = Solicitud::with(['visitantes', 'estado'])
            ->where('id_estado_solicitud', 2)
            ->whereDate('fecha_inicio', today())
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        return view('vigilante.index', compact('visitasHoy'));
    }

    public function escanear(StoreVigilanteEscanearRequest $request)
    {
        $qr = QR::with([
            'solicitudVisitante.visitante',
            'solicitudVisitante.solicitud.estado',
        ])
            ->where('codigo_numerico', $request->codigo_qr)
            ->first();

        if (!$qr) {
            return back()->with('error', 'No se encontraron registros.');
        }

        $ahora = now();
        if ($ahora < $qr->vigencia_inicio || $ahora > $qr->vigencia_final) {
            return back()->with('error', 'El código QR ha expirado.');
        }

        return view('vigilante.resultado', compact('qr'));
    }

    public function registrarEntrada(Request $request)
    {
        $qr = QR::with('solicitudVisitante.visitante')->findOrFail($request->id_qr);

        RegistroAcceso::create([
            'hora_llegada_institucion' => now(),
            'id_vigilante_entrada'     => Auth::id(),
            'id_qr'                    => $qr->id_qr,
        ]);

        $qr->update(['id_estadoQr' => 3]);

        $nombre = $qr->solicitudVisitante->visitante->nombre . ' ' .
                  $qr->solicitudVisitante->visitante->apellidos;

        return view('vigilante.confirmacion', [
            'tipo'   => 'entrada',
            'nombre' => $nombre,
        ]);
    }

    public function registrarSalida(Request $request)
    {
        $qr = QR::with('solicitudVisitante.visitante')->findOrFail($request->id_qr);

        $registro = RegistroAcceso::where('id_qr', $request->id_qr)
            ->whereNull('hora_salida_institucion')
            ->first();

        if ($registro) {
            $registro->update([
                'hora_salida_institucion' => now(),
                'id_vigilante_salida'     => Auth::id(),
            ]);
        }

        $nombre = $qr->solicitudVisitante->visitante->nombre . ' ' .
                  $qr->solicitudVisitante->visitante->apellidos;

        return view('vigilante.confirmacion', [
            'tipo'   => 'salida',
            'nombre' => $nombre,
        ]);
    }

    public function historial()
    {
        $registros = RegistroAcceso::with(['qr.solicitudVisitante.visitante'])
            ->orderBy('hora_llegada_institucion', 'desc')
            ->paginate(10);

        return view('vigilante.historial', compact('registros'));
    }
}