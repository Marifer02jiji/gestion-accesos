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
 * Fix password oculto en modelo Empleado
 * Rol granular para app móvil e inclusión de rol_api
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

        // Normalizar usuario con dominio
        $usuarioInput = $request->usuario;
        if (!str_contains($usuarioInput, '@toluca.tecnm.mx')) {
            $usuarioInput .= '@toluca.tecnm.mx';
        }

        // Buscar empleado en SAM
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

        // Crear o buscar usuario local
        $user = User::firstOrCreate(
            ['email' => $usuarioInput],
            [
                'name'            => $empleado->nombre . ' ' . $empleado->apellidoPa,
                'email'           => $usuarioInput,
                'password'        => bcrypt($request->password),
                'id_empleado_sam' => $empleado->id_empleado,
            ]
        );

        if (!$user->id_empleado_sam) {
            $user->update(['id_empleado_sam' => $empleado->id_empleado]);
        }

        // Determinar rol API
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
                'rol'             => $rolNuevo,      // 'autorizador' o 'solicitante'
                'rol_api'         => $rolNuevo,      // Consumido por Flutter para mapear el puesto
                'id_departamento' => $empleado->id_departamento,
                'departamento'    => '',             // Campo complementario opcional
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