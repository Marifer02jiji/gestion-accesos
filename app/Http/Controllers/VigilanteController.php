<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Http/Controllers/VigilanteController.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, escaneo de QR web para entrada y salida
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Implementar identificar() y salirSesion() para vigilante sin login
 * ID: 3 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Fix registrar salida aunque QR haya vencido
 */

namespace App\Http\Controllers;

use App\Models\QR;
use App\Models\RegistroAcceso;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class VigilanteController extends Controller
{
    public function identificar(Request $request)
    {
        $request->validate([
            'telefono' => 'required|string|digits:10',
            'area'     => 'required|string|max:100',
        ]);

        session([
            'vigilante_telefono' => $request->telefono,
            'vigilante_area'     => $request->area,
        ]);

        return redirect()->route('vigilante.index');
    }

    public function salirSesion()
    {
        session()->forget(['vigilante_telefono', 'vigilante_area']);
        return redirect()->route('vigilante.index');
    }

    public function index()
    {
        $visitasHoy = Solicitud::with(['visitantes', 'solicitudVisitantes.qr'])
            ->where('id_estado_solicitud', 2)
            ->whereDate('fecha_inicio', today())
            ->get();

        return view('vigilante.index', compact('visitasHoy'));
    }

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

        if (now()->lt($qr->vigencia_inicio)) {
            $inicio = \Carbon\Carbon::parse($qr->vigencia_inicio)->format('d/m/Y H:i');

            return redirect()->route('vigilante.index')
                ->with('error', "El acceso aún no está permitido. La ventana inicia a las {$inicio} hrs.");
        }

        if (now()->gt($qr->vigencia_final)) {
            $fin = \Carbon\Carbon::parse($qr->vigencia_final)->format('d/m/Y H:i');

            return redirect()->route('vigilante.index')
                ->with('error', "El tiempo de acceso ha expirado. La ventana terminó a las {$fin} hrs.");
        }

        // Verificar si el visitante está en la lista de exclusión
        $visitante = $qr->solicitudVisitante->visitante;
        $enExclusion = \App\Models\ListaExclusion::where('id_visitante', $visitante->id_visitante)
            ->exists();

        if ($enExclusion) {
            // Deshabilitar el QR
            $qr->update(['id_estadoQr' => 4]);
            
            return redirect()->route('vigilante.index')
                ->with('error', "El visitante {$visitante->nombre} {$visitante->apellidos} está en la lista de exclusión. Acceso denegado.");
        }

        return view('vigilante.resultado', compact('qr'));
    }

    public function registrarEntrada(Request $request)
    {
        $request->validate(['id_qr' => 'required|integer']);

        $qr = QR::findOrFail($request->id_qr);

        RegistroAcceso::registrarEntradaInstitucional(
            $qr->id_qr,
            (string) session('vigilante_telefono'),
            (string) session('vigilante_area')
        );

        $qr->update(['id_estadoQr' => 3]);

        return redirect()->route('vigilante.index')
            ->with('success', 'Entrada registrada correctamente.');
    }

    public function registrarSalida(Request $request)
    {
        $request->validate(['id_qr' => 'required|integer']);

        $registro = RegistroAcceso::where('id_qr', $request->id_qr)
            ->whereNull('hora_salida_institucion')
            ->orderBy('id_registro', 'desc')
            ->first();

        if (!$registro) {
            return redirect()->route('vigilante.index')
                ->with('error', 'No se encontró entrada registrada para este QR.');
        }

        // Obtener la solicitud para verificar estados
        $solicitud = $registro->qr->solicitudVisitante->solicitud;
        $visitante = $registro->qr->solicitudVisitante->visitante;

        RegistroAcceso::registrarSalidaInstitucional(
            $registro,
            (string) session('vigilante_telefono'),
            (string) session('vigilante_area')
        );

        if (
            $solicitud->esVisitaEstandar()
            && (!$solicitud->hora_llegada_encuentro || !$solicitud->hora_salida_encuentro)
        ) {
            $solicitud->update(['encuentro_sin_marcar_solicitante' => true]);

            \App\Models\Notificacion::create([
                'id_empleado'  => $solicitud->id_solicitante,
                'id_solicitud' => $solicitud->id_solicitud,
                'tipo'         => 'alerta',
                'mensaje'      => "El visitante {$visitante->nombre} {$visitante->apellidos} salió sin marcar los estados de llegada/salida del encuentro. Folio: {$solicitud->folio}",
                'leida'        => false,
            ]);
        }

        return redirect()->route('vigilante.index')
            ->with('success', 'Salida registrada correctamente.');
    }

    public function historial()
    {
        $registros = RegistroAcceso::with(['qr.solicitudVisitante.visitante'])
            ->orderBy('hora_llegada_institucion', 'desc')
            ->paginate(15);

        return view('vigilante.historial', compact('registros'));
    }
}