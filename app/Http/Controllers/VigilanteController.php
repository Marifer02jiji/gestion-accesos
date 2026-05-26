<?php

namespace App\Http\Controllers;

use App\Models\QR;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class VigilanteController extends Controller
{
    // Guardar datos del vigilante en sesión
    public function identificar(Request $request)
    {
        $request->validate([
            'telefono' => 'required|string|max:15',
            'area'     => 'required|string|max:100',
        ]);

        session([
            'vigilante_telefono' => $request->telefono,
            'vigilante_area'     => $request->area,
        ]);

        return redirect()->route('vigilante.index');
    }

    // Cerrar sesión de vigilante
    public function salirSesion()
    {
        session()->forget(['vigilante_telefono', 'vigilante_area']);
        return redirect()->route('vigilante.index');
    }

    // Vista principal
    public function index()
    {
        $visitasHoy = Solicitud::with(['visitantes', 'solicitudVisitantes.qr'])
            ->where('id_estado_solicitud', 2)
            ->whereDate('fecha_inicio', today())
            ->get();

        return view('vigilante.index', compact('visitasHoy'));
    }

    // Escanear QR
    public function escanear(Request $request)
    {
        $request->validate(['codigo_qr' => 'required|string']);

        $qr = QR::where('codigo_numerico', $request->codigo_qr)
            ->with('solicitudVisitante.visitante', 'solicitudVisitante.solicitud')
            ->first();

        if (!$qr) {
            return redirect()->route('vigilante.index')
                ->with('error', 'Código QR no encontrado.');
        }

        if ($qr->id_estadoQr == 4) {
            return redirect()->route('vigilante.index')
                ->with('error', 'Este código QR fue cancelado.');
        }

        if (now() < $qr->vigencia_inicio || now() > $qr->vigencia_final) {
            return redirect()->route('vigilante.index')
                ->with('error', 'El código QR ha expirado o aún no es válido.');
        }

        return view('vigilante.resultado', compact('qr'));
    }

    // Registrar entrada
    public function registrarEntrada(Request $request)
    {
        $request->validate(['id_qr' => 'required|integer']);

        $qr = QR::findOrFail($request->id_qr);

        $registro = RegistroAcceso::create([
            'hora_llegada_institucion' => now(),
            'id_qr'                    => $qr->id_qr,
            'telefono_vigilante'       => session('vigilante_telefono'),
            'area_vigilante'           => session('vigilante_area'),
        ]);

        $qr->update(['id_estadoQr' => 3]); // Usado

        return redirect()->route('vigilante.index')
            ->with('success', 'Entrada registrada correctamente.');
    }

    // Registrar salida
    public function registrarSalida(Request $request)
    {
        $request->validate(['id_qr' => 'required|integer']);

        $registro = RegistroAcceso::where('id_qr', $request->id_qr)
            ->whereNull('hora_salida_institucion')
            ->latest()
            ->first();

        if (!$registro) {
            return redirect()->route('vigilante.index')
                ->with('error', 'No se encontró entrada registrada para este QR.');
        }

        $registro->update([
            'hora_salida_institucion' => now(),
            'telefono_vigilante'      => session('vigilante_telefono'),
            'area_vigilante'          => session('vigilante_area'),
        ]);

        return redirect()->route('vigilante.index')
            ->with('success', 'Salida registrada correctamente.');
    }

    // Historial
    public function historial()
    {
        $registros = RegistroAcceso::with(['qr.solicitudVisitante.visitante'])
            ->orderBy('hora_llegada_institucion', 'desc')
            ->paginate(15);

        return view('vigilante.historial', compact('registros'));
    }
}