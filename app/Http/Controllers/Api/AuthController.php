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
 * ID: 4 | Fecha: 27/05/2026 | Descripción: Ajuste nombres de departamentos autorizadores
 * ID: 5 | Fecha: 29/05/2026 | Descripción: Fix búsqueda por name — respeta roles manuales
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

        $usuarioInput = $request->usuario;
        if (str_contains($usuarioInput, '@')) {
            $usuarioInput = explode('@', $usuarioInput)[0];
        }

        $empleado = Empleado::where('usuario', $usuarioInput)
            ->where('estatus', 'Activo')
            ->first();

        if (!$empleado) {
            return response()->json([
                'message' => 'Las credenciales no coinciden con nuestros registros.',
                'data'    => null,
            ], 401);
        }

        $passwordSam = $empleado->getAttributes()['password'] ?? null;
        if (!$passwordSam || $passwordSam !== hash('sha256', $request->password)) {
            return response()->json([
                'message' => 'Las credenciales no coinciden con nuestros registros.',
                'data'    => null,
            ], 401);
        }

        // Buscar por name (usuario SAM) — respeta el email y rol que ya tenga
        $user = User::where('name', $usuarioInput)->first();

        if (!$user) {
            $user = User::create([
                'name'            => $usuarioInput,
                'email'           => $usuarioInput . '@toluca.tecnm.mx',
                'password'        => bcrypt($request->password),
                'id_empleado_sam' => $empleado->id_empleado,
            ]);
        }

        if (!$user->id_empleado_sam) {
            $user->update(['id_empleado_sam' => $empleado->id_empleado]);
        }

        // Solo asignar rol si no tiene ninguno — respeta roles manuales
        if ($user->roles->isEmpty()) {
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
            $rol = ($esJefe || $esDeptoAutorizador) ? 'autorizador' : 'solicitante';

            \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $rol, 'guard_name' => 'web']
            );

            $user->assignRole($rol);
        }

        // Usar el rol real de BD para la respuesta
        $rolFinal = $user->getRoleNames()->first() ?? 'solicitante';

        $token = $user->createToken('flutter-token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'data'    => [
                'token'           => $token,
                'id'              => $user->id,
                'id_empleado_sam' => $empleado->id_empleado,
                'name'            => $empleado->nombre . ' ' . $empleado->apellidoPa,
                'email'           => $user->email,
                'rol'             => $rolFinal,
                'rol_api'         => $rolFinal,
               
                'roles'           => $user->getRoleNames(),
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