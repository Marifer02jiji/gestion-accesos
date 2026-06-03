<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Http/Controllers/SolicitudController.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial del controlador CRUD de solicitudes
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Implementación de cancelar solicitud con cambio de estado QR
 * ID: 3 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar envío de QR por correo al visitante
 * ID: 4 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Implementar registrarLlegadaEncuentro y registrarSalidaEncuentro
 * ID: 5 | Fecha: 31/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar historial de visitas finalizadas
 * ID: 6 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Notificar a autorizadores al crear solicitud
 * ID: 7 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Envío de correo de cancelación al visitante con datos del anfitrión
 * ID: 8 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Fix cancelar disponible para estados Pendiente y Autorizada
 */

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitudRequest;
use App\Models\CaTipoSolicitud;
use App\Models\ListaExclusion;
use App\Models\Notificacion;
use App\Models\QR;
use App\Models\Solicitud;
use App\Models\SolicitudVisitante;
use App\Models\User;
use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SolicitudController extends Controller
{
    private function idEmpleado(): int
    {
        return Auth::user()->idSam();
    }

    private function notificarAutorizadores(Solicitud $solicitud): void
    {
        $autorizadores = User::whereHas('roles', function($q) {
            $q->where('name', 'autorizador');
        })->get();

        foreach ($autorizadores as $autorizador) {
            Notificacion::create([
                'id_empleado'  => $autorizador->idSam(),
                'id_solicitud' => $solicitud->id_solicitud,
                'tipo'         => 'pendiente',
                'mensaje'      => "Nueva solicitud pendiente de autorizar. Folio: {$solicitud->folio}",
                'leida'        => false,
            ]);
        }
    }

    public function index(Request $request)
    {
        $estado = $request->get('estado');
        $correo = $request->get('correo');
        $fecha  = $request->get('fecha');
        $desde  = $request->get('desde');
        $hasta  = $request->get('hasta');

        $query = Solicitud::where('id_solicitante', $this->idEmpleado())
            ->with(['estado', 'tipo', 'visitantes']);

        if ($estado) {
            $query->where('id_estado_solicitud', $estado);
        }

        $query->filtrarPendientesAutorizador([
            'correo' => $correo,
            'fecha'  => $fecha,
        ]);

        if ($desde) {
            $query->whereDate('fecha_inicio', '>=', $desde);
        }

        if ($hasta) {
            $query->whereDate('fecha_inicio', '<=', $hasta);
        }

        $solicitudes = $query
            ->orderByRaw("CASE WHEN id_estado_solicitud IN (1,2,5,6,7) THEN 0 ELSE 1 END")
            ->orderBy('fecha_inicio', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('solicitudes.index', compact('solicitudes', 'estado', 'correo', 'fecha', 'desde', 'hasta'));
    }

    public function create()
    {
        $tipos = CaTipoSolicitud::all();
        return view('solicitudes.create', compact('tipos'));
    }

    public function store(StoreSolicitudRequest $request)
    {
        // Verificar si algún visitante está en la lista de exclusión
        $visitantesEnExclusion = [];
        foreach ($request->visitante_correo as $index => $correo) {
            // Buscar visitante por correo
            $visitante = Visitante::where('correo_personal', $correo)->first();
            if ($visitante) {
                $enExclusion = ListaExclusion::where('id_visitante', $visitante->id_visitante)->exists();
                if ($enExclusion) {
                    $visitantesEnExclusion[] = trim($request->visitante_nombre[$index]) . ' ' . trim($request->visitante_apellidos[$index]);
                }
            }
        }

        // Si hay visitantes en exclusión, rechazar la solicitud con advertencia
        if (!empty($visitantesEnExclusion)) {
            return redirect()->route('solicitudes.create')
                ->with('error', 'No se puede crear la solicitud. Los siguientes visitantes están en lista de exclusión: ' . implode(', ', $visitantesEnExclusion));
        }

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
            $visitante = Visitante::updateOrCreate(
                ['correo_personal' => $correo],
                [
                    'nombre'    => trim($request->visitante_nombre[$index]),
                    'apellidos' => trim($request->visitante_apellidos[$index]),
                ]
            );

            SolicitudVisitante::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_visitante' => $visitante->id_visitante,
            ]);
        }

        $this->notificarAutorizadores($solicitud);

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
        $solicitud = Solicitud::with(['solicitudVisitantes.qr', 'solicitudVisitantes.visitante'])
            ->findOrFail($id);

        if (!$solicitud->esCancelable()) {
            return redirect()->route('solicitudes.index')
                ->with('error', 'Esta solicitud no puede cancelarse en su estado actual.');
        }

        // Cancelar QRs
        foreach ($solicitud->solicitudVisitantes as $sv) {
            if ($sv->qr && in_array($sv->qr->id_estadoQr, [1, 2])) {
                $sv->qr->update(['id_estadoQr' => 4]);
            }
        }

        $solicitud->update([
            'id_estado_solicitud' => 4,
            'cancelado_por'       => $this->idEmpleado(),
            'fecha_cancelacion'   => now(),
        ]);

        // Enviar correo a cada visitante
        $anfitrion = Auth::user()->name ?? 'el anfitrión';
        foreach ($solicitud->solicitudVisitantes as $sv) {
            $correo = $sv->visitante->correo_personal ?? null;
            if (!$correo) continue;
            try {
                Mail::to($correo)->send(new \App\Mail\SolicitudCanceladaMail(
                    $sv->visitante,
                    $solicitud,
                    $anfitrion
                ));
            } catch (\Throwable $e) {
                Log::error('Error enviando correo cancelacion: ' . $e->getMessage());
            }
        }

        return redirect()->route('solicitudes.index')
            ->with('success', 'Solicitud cancelada correctamente. Se notificó a los visitantes.');
    }

    public function enviarQR($id)
    {
        $solicitud = Solicitud::with(['solicitudVisitantes.qr', 'solicitudVisitantes.visitante'])
            ->findOrFail($id);

        if ($solicitud->id_estado_solicitud !== 2) {
            return redirect()->route('solicitudes.show', $id)
                ->with('error', 'Solo se puede enviar el QR cuando la solicitud esta autorizada.');
        }

        $fechaExpiracion = \Carbon\Carbon::parse($solicitud->fecha_inicio)
            ->addMinutes($solicitud->tolerancia_despues ?? 15);

        if (now() > $fechaExpiracion) {
            return redirect()->route('solicitudes.show', $id)
                ->with('error', 'No se puede enviar el QR, la visita ya expiro.');
        }

        // Verificar si algún visitante está en la lista de exclusión
        $visitantesEnExclusion = [];
        foreach ($solicitud->solicitudVisitantes as $sv) {
            $enExclusion = ListaExclusion::where('id_visitante', $sv->id_visitante)->exists();
            if ($enExclusion) {
                $visitantesEnExclusion[] = $sv->visitante->nombre . ' ' . $sv->visitante->apellidos;
            }
        }

        if (!empty($visitantesEnExclusion)) {
            return redirect()->route('solicitudes.show', $id)
                ->with('error', 'No se puede enviar el QR. Los siguientes visitantes están en lista de exclusión: ' . implode(', ', $visitantesEnExclusion));
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
                \App\Models\RegistroAcceso::where('id_qr', $sv->qr->id_qr)->delete();
                $sv->qr->delete();
            }
        }

        $solicitud->solicitudVisitantes()->delete();
        Notificacion::where('id_solicitud', $id)->delete();
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
            'id_estado_solicitud'    => 6,
            'hora_llegada_encuentro' => now(),
        ]);

        Notificacion::create([
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
            'id_estado_solicitud'   => 7,
            'hora_salida_encuentro' => now(),
        ]);

        return redirect()->route('solicitudes.show', $id)
            ->with('success', 'Salida del encuentro registrada correctamente.');
    }

    public function historial()
    {
        $solicitudes = Solicitud::where('id_solicitante', $this->idEmpleado())
            ->where('id_estado_solicitud', 8)
            ->with(['visitantes', 'solicitudVisitantes.qr'])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);

        return view('solicitudes.historial', compact('solicitudes'));
    }

    public function edit($id) { return redirect()->route('solicitudes.show', $id); }
    public function update(\Illuminate\Http\Request $r, $id) { return redirect()->route('solicitudes.show', $id); }
}