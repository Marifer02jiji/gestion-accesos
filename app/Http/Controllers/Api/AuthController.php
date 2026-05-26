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
 * ID: 2 | Fecha: 25/05/2026 | Modificado por: Desarrollador | Descripción: Asignación automática de rol, id_empleado_sam y nombre completo
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario'  => 'required|string',
            'password' => 'required|string',
        ]);

        // Obtener usuario ingresado
        $usuarioInput = $request->usuario;

        // Si viene con dominio institucional, quitarlo para buscar en SAM
        if (str_contains($usuarioInput, '@toluca.tecnm.mx')) {
            $usuarioInput = str_replace('@toluca.tecnm.mx', '', $usuarioInput);
        }

        // Buscar empleado en SAM
        $empleado = Empleado::where('usuario', $usuarioInput)
            ->where('estatus', 'Activo')
            ->first();

        // Validar credenciales
        if (!$empleado || $empleado->password !== hash('sha256', $request->password)) {
            return response()->json([
                'message' => 'Las credenciales no coinciden con nuestros registros.',
                'data'    => null,
            ], 401);
        }

        // Crear o buscar usuario local
        $user = User::firstOrCreate(
            ['email' => $empleado->usuario . '@toluca.tecnm.mx'],
            [
                'name'            => $empleado->nombre . ' ' . $empleado->apellidoPa,
                'email'           => $empleado->usuario . '@toluca.tecnm.mx',
                'password'        => bcrypt($request->password),
                'id_empleado_sam' => $empleado->id_empleado,
            ]
        );

        // Actualizar id_empleado_sam si no lo tenía
        if (!$user->id_empleado_sam) {
            $user->update(['id_empleado_sam' => $empleado->id_empleado]);
        }

        // Asignar rol según credenciales del SAM (solo si no tiene rol)
        if ($user->roles->isEmpty()) {
            $rol = match($empleado->credenciales) {
                'Administrador master' => 'autorizador',
                default                => 'solicitante',
            };
            $user->assignRole($rol);
        }

        // Generar token
        $token = $user->createToken('flutter-token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'              => $user->id,
                    'name'            => $user->name,
                    'email'           => $user->email,
                    'id_empleado_sam' => $user->id_empleado_sam,
                    'roles'           => $user->getRoleNames(),
                    'nombre_completo' => $empleado->nombre . ' ' . $empleado->apellidoPa . ' ' . $empleado->apellidoMa,
                    'credenciales'    => $empleado->credenciales,
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
            'data'    => null,
        ]);
    }

    public function perfil(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Perfil obtenido correctamente.',
            'data'    => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'id_empleado_sam' => $user->id_empleado_sam,
                'roles'           => $user->getRoleNames(),
            ],
        ]);
    }

    public function notificaciones(Request $request)
    {
        $user = $request->user();

        // Usar id_empleado_sam para filtrar notificaciones correctamente
        $notificaciones = Notificacion::where('id_empleado', $user->idSam())
            ->orderBy('fecha_creado', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'Notificaciones obtenidas correctamente.',
            'data'    => $notificaciones,
        ]);
    }
}