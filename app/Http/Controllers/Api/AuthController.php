<?php

/**
 * Empresa: OMEGA
 * Proyecto: Sistema de Gestión de Accesos
 * Creación: 07/05/2026
 * Creado por: Desarrollador
 *
 * Changelog:
 * ID: 1 | Fecha: 07/05/2026 | Descripción: Creación inicial
 * ID: 2 | Fecha: 25/05/2026 | Descripción: Asignación automática de rol
 * ID: 3 | Fecha: 26/05/2026 | Descripción: Fix búsqueda usuario SAM con dominio
 *                                           Fix password oculto en modelo Empleado
 *                                           Rol granular para app móvil e inclusión de rol_api
 * ID: 4 | Fecha: 27/05/2026 | Descripción: Ajuste nombres de departamentos autorizadores
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario'  => 'required|string',
            'password' => 'required|string',
        ]);

        // SAM guarda solo el nombre de usuario sin dominio (ej: "mauro")
        // Si Flutter manda "mauro@toluca.tecnm.mx", extraemos solo "mauro"
        $usuarioInput = $request->usuario;
        if (str_contains($usuarioInput, '@')) {
            $usuarioInput = explode('@', $usuarioInput)[0];
        }

        // Buscar empleado en SAM con el usuario limpio (sin dominio)
        $empleado = Empleado::where('usuario', $usuarioInput)
            ->where('estatus', 'Activo')
            ->first();

        if (!$empleado) {
            return response()->json([
                'message' => 'Las credenciales no coinciden con nuestros registros.',
                'data'    => null,
            ], 401);
        }

        // Validar contraseña (SHA-256, formato del SAM)
        // getAttributes() omite el $hidden del modelo
        $passwordSam = $empleado->getAttributes()['password'] ?? null;
        if (!$passwordSam || $passwordSam !== hash('sha256', $request->password)) {
            return response()->json([
                'message' => 'Las credenciales no coinciden con nuestros registros.',
                'data'    => null,
            ], 401);
        }

        // El User local de Laravel necesita email con dominio
        $emailLocal = $usuarioInput . '@toluca.tecnm.mx';

        // Crear o buscar usuario local en Laravel
        $user = User::firstOrCreate(
            ['email' => $emailLocal],
            [
                'name'            => $empleado->nombre . ' ' . $empleado->apellidoPa,
                'email'           => $emailLocal,
                'password'        => bcrypt($request->password),
                'id_empleado_sam' => $empleado->id_empleado,
            ]
        );

        if (!$user->id_empleado_sam) {
            $user->update(['id_empleado_sam' => $empleado->id_empleado]);
        }

        // Determinar rol: autorizador si es jefe O pertenece a depto autorizador
        $esJefe = (int) $empleado->jefe === 1;

        $departamentosAutorizadores = DB::connection('sam')
            ->table('departamento')
            ->where(function ($q) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ['%recursos humanos%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%recursos materiales%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%comunicacion y difusion%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%desarrollo academico%']);
            })
            ->pluck('id_departamento')
            ->toArray();

        $esDeptoAutorizador = in_array((int) $empleado->id_departamento, $departamentosAutorizadores, true);
        $rolNuevo = ($esJefe || $esDeptoAutorizador) ? 'autorizador' : 'solicitante';

        // Asegura que el rol exista antes de asignarlo (evita crash de Spatie)
        \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => $rolNuevo, 'guard_name' => 'web']
        );

        // syncRoles reemplaza cualquier rol anterior
        $user->syncRoles([$rolNuevo]);

        $token = $user->createToken('flutter-token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'data'    => [
                'token'           => $token,
                'id'              => $user->id,
                'id_empleado_sam' => $empleado->id_empleado,
                'name'            => $empleado->nombre . ' ' . $empleado->apellidoPa,
                'email'           => $user->email,
                'rol'             => $rolNuevo,
                'rol_api'         => $rolNuevo,
                'id_departamento' => $empleado->id_departamento,
                'departamento'    => '',
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

        $notificaciones = Notificacion::where('id_empleado', $user->idSam())
            ->orderBy('fecha_creado', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'Notificaciones obtenidas correctamente.',
            'data'    => $notificaciones,
        ]);
    }
}