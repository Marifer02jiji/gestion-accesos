<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionApiController extends Controller
{
    private function idEmpleado(): int
    {
        return auth()->user()->idSam();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 20), 1), 50);

        $notificaciones = Notificacion::where('id_empleado', $this->idEmpleado())
            ->orderBy('fecha_creado', 'desc')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Notificaciones obtenidas correctamente.',
            'data'    => $notificaciones,
        ]);
    }

    public function marcarLeida(int $id): JsonResponse
    {
        $notificacion = Notificacion::where('id_notificaciones', $id)
            ->where('id_empleado', $this->idEmpleado())
            ->firstOrFail();

        $notificacion->update(['leida' => true]);

        return response()->json([
            'message' => 'Notificación marcada como leída.',
            'data'    => $notificacion,
        ]);
    }

    public function marcarTodasLeidas(): JsonResponse
    {
        Notificacion::where('id_empleado', $this->idEmpleado())
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json([
            'message' => 'Todas las notificaciones marcadas como leídas.',
            'data'    => null,
        ]);
    }

    public function eliminar(int $id): JsonResponse
    {
        Notificacion::where('id_notificaciones', $id)
            ->where('id_empleado', $this->idEmpleado())
            ->firstOrFail()
            ->delete();

        return response()->json([
            'message' => 'Notificación eliminada.',
            'data'    => null,
        ]);
    }

    public function eliminarTodas(): JsonResponse
    {
        Notificacion::where('id_empleado', $this->idEmpleado())->delete();

        return response()->json([
            'message' => 'Todas las notificaciones eliminadas.',
            'data'    => null,
        ]);
    }
}
