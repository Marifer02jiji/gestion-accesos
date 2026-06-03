<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Http/Controllers/NotificacionController.php
 * Creación: 07/05/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder del Área
 *
 * Changelog:
 * ID: 1 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, listar notificaciones del usuario autenticado
 * ID: 2 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Implementar marcarLeida y marcarTodasLeidas
 * ID: 3 | Fecha: 01/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar eliminar notificación individual y eliminar todas
 * ID: 4 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Links a solicitudes según tipo de notificación en la vista
 */

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function index()
    {
                                                            /*Auth::id()*/
        $notificaciones = Notificacion::where('id_empleado', Auth::user()->idSam())
            ->orderBy('fecha_creado', 'desc')
            ->paginate(10);

        return view('notificaciones.index', compact('notificaciones'));
    }

    public function marcarLeida($id)
    {
        $notificacion = Notificacion::findOrFail($id);
        $notificacion->update(['leida' => true]);

        return redirect()->route('notificaciones.index')
            ->with('success', 'Notificación marcada como leída.');
    }

    public function marcarTodasLeidas()
    {
        Notificacion::where('id_empleado', Auth::user()->idSam())
            ->where('leida', false)
            ->update(['leida' => true]);

        return redirect()->route('notificaciones.index')
            ->with('success', 'Todas las notificaciones marcadas como leídas.');
    }



    public function eliminar($id)
    {
        Notificacion::where('id_notificaciones', $id)
            ->where('id_empleado', Auth::user()->idSam())
            ->firstOrFail()
            ->delete();

        return redirect()->route('notificaciones.index')
            ->with('success', 'Notificación eliminada.');
    }

    public function eliminarTodas()
    {
        Notificacion::where('id_empleado', Auth::user()->idSam())->delete();

        return redirect()->route('notificaciones.index')
            ->with('success', 'Todas las notificaciones eliminadas.');
    }

}