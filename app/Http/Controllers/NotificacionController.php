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
        Notificacion::where('id_empleado', Auth::id())
            ->where('leida', false)
            ->update(['leida' => true]);

        return redirect()->route('notificaciones.index')
            ->with('success', 'Todas las notificaciones marcadas como leídas.');
    }
}