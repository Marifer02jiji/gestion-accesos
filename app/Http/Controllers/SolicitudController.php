<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitudRequest;
use App\Models\CaTipoSolicitud;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\Visitante;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SolicitudController extends Controller
{
    private function idEmpleado(): int
    {
        return Auth::user()->idSam();
    }

    public function index()
    {
        $solicitudes = Solicitud::where('id_solicitante', $this->idEmpleado())
            ->with(['estado', 'tipo', 'visitantes'])
            ->orderBy('fecha_inicio', 'asc')
            ->paginate(10);

        return view('solicitudes.index', compact('solicitudes'));
    }

    public function create()
    {
        $tipos = CaTipoSolicitud::all();
        return view('solicitudes.create', compact('tipos'));
    }

    public function store(StoreSolicitudRequest $request)
    {
        $solicitud = Solicitud::create([
            'folio'               => Solicitud::generarFolio(),
            'fecha_inicio'        => $request->fecha_inicio,
            'lugar_encuentro'     => $request->lugar_encuentro,
            'motivo_visita'       => $request->motivo_visita,
            'id_tipo_solicitud'   => $request->id_tipo_solicitud,
            'tolerancia_antes'    => $request->tolerancia_antes,
            'tolerancia_despues'  => $request->tolerancia_despues,
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

    public function show($id)
    {
        $solicitud = Solicitud::with(['estado', 'tipo', 'visitantes', 'solicitudVisitantes.qr'])
            ->findOrFail($id);

        return view('solicitudes.show', compact('solicitud'));
    }

    public function cancelar($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

        if (!$solicitud->esCancelable()) {
            return redirect()->route('solicitudes.index')
                ->with('error', 'Esta solicitud no puede cancelarse en su estado actual.');
        }

        foreach ($solicitud->solicitudVisitantes as $sv) {
            if ($sv->qr && $sv->qr->id_estadoQr === 1) {
                $sv->qr->update(['id_estadoQr' => 4]);
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

    public function enviarQR($id)
    {
        $solicitud = Solicitud::with(['solicitudVisitantes.qr', 'solicitudVisitantes.visitante'])
            ->findOrFail($id);

        // Solo solicitudes autorizadas
        if ($solicitud->id_estado_solicitud !== 2) {
            return redirect()->route('solicitudes.show', $id)
                ->with('error', 'Solo se puede enviar el QR cuando la solicitud esta autorizada.');
        }

        // Validar que la visita no haya expirado (fecha + tolerancia_despues)
        $fechaExpiracion = \Carbon\Carbon::parse($solicitud->fecha_inicio)
            ->addMinutes($solicitud->tolerancia_despues ?? 15);

        if (now() > $fechaExpiracion) {
            return redirect()->route('solicitudes.show', $id)
                ->with('error', 'No se puede enviar el QR, la visita ya expiro.');
        }

        $enviados = 0;
        $errores  = 0;

        foreach ($solicitud->solicitudVisitantes as $sv) {
            $qr     = $sv->qr;
            $correo = $sv->visitante->correo_personal ?? null;

            if (!$qr || !$correo) continue;

            try {
                Mail::to($correo)->send(new \App\Mail\EnviarQRMail($qr));
                $enviados++;
            } catch (\Throwable $e) {
                $errores++;
                Log::error('Error enviando QR: ' . $e->getMessage());
            }
        }

        if ($enviados > 0 && $errores === 0) {
            return redirect()->route('solicitudes.show', $id)
                ->with('success', "QR enviado correctamente a {$enviados} visitante(s).");
        }

        if ($enviados > 0 && $errores > 0) {
            return redirect()->route('solicitudes.show', $id)
                ->with('success', "QR enviado a {$enviados} visitante(s). {$errores} no pudieron enviarse.");
        }

        return redirect()->route('solicitudes.show', $id)
            ->with('error', 'No se pudo enviar el QR. Revisa los logs en storage/logs/laravel.log');
    }

    public function destroy($id)
    {
        $solicitud = Solicitud::with('solicitudVisitantes.qr')->findOrFail($id);

        $fechaPasada = now() > \Carbon\Carbon::parse($solicitud->fecha_inicio);

        if (!in_array($solicitud->id_estado_solicitud, [3, 4, 8]) && !$fechaPasada) {
            return redirect()->route('solicitudes.index')
                ->with('error', 'Solo se pueden eliminar solicitudes canceladas, rechazadas, finalizadas o con fecha pasada.');
        }

        foreach ($solicitud->solicitudVisitantes as $sv) {
            if ($sv->qr) {
                // 1. Eliminar registros de acceso primero
                \App\Models\RegistroAcceso::where('id_qr', $sv->qr->id_qr)->delete();
                // 2. Eliminar QR
                $sv->qr->delete();
            }
        }

        // 3. Eliminar solicitud_visitante
        $solicitud->solicitudVisitantes()->delete();

        // 4. Eliminar notificaciones
        \App\Models\Notificacion::where('id_solicitud', $id)->delete();

        // 5. Eliminar solicitud
        $solicitud->delete();

        return redirect()->route('solicitudes.index')
            ->with('success', 'Solicitud eliminada correctamente.');
    }

    public function registrarLlegadaEncuentro($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 5) {
            return redirect()->route('solicitudes.show', $id)
                ->with('error', 'La visita debe estar En Institución para registrar llegada al encuentro.');
        }

        $solicitud->update([
            'id_estado_solicitud' => 6,
            'hora_llegada_encuentro' => now(),
        ]);

        // Notificar al solicitante
        \App\Models\Notificacion::create([
            'id_empleado'  => $this->idEmpleado(),
            'id_solicitud' => $solicitud->id_solicitud,
            'tipo'         => 'encuentro',
            'mensaje'      => "El visitante llegó al lugar de encuentro. Folio: {$solicitud->folio}",
            'leida'        => false,
        ]);

        return redirect()->route('solicitudes.show', $id)
            ->with('success', 'Llegada al encuentro registrada correctamente.');
    }

    public function registrarSalidaEncuentro($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 6) {
            return redirect()->route('solicitudes.show', $id)
                ->with('error', 'La visita debe estar En Encuentro para registrar salida.');
        }

        $solicitud->update([
            'id_estado_solicitud' => 7,
            'hora_salida_encuentro' => now(),
        ]);

        return redirect()->route('solicitudes.show', $id)
            ->with('success', 'Salida del encuentro registrada correctamente.');
    }




    public function historial()
    {
        $solicitudes = Solicitud::where('id_solicitante', $this->idEmpleado())
            ->where('id_estado_solicitud', 8) // Finalizada
            ->with(['visitantes', 'solicitudVisitantes.qr.registroAcceso'])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);

        return view('solicitudes.historial', compact('solicitudes'));
    }




    public function edit($id) { return redirect()->route('solicitudes.show', $id); }
    public function update(\Illuminate\Http\Request $r, $id) { return redirect()->route('solicitudes.show', $id); }
}