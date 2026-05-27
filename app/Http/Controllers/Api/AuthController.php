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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario'  => 'required|string',
            'password' => 'required|string',
        ]);

        // Normalizar: el SAM guarda el usuario CON dominio
        $usuarioInput = $request->usuario;
        if (!str_contains($usuarioInput, '@toluca.tecnm.mx')) {
            $usuarioInput .= '@toluca.tecnm.mx';
        }

        // Buscar empleado en SAM
        $empleado = Empleado::where('usuario', $usuarioInput)
            ->where('estatus', 'Activo')
            ->first();

        // Validación híbrida
        $valido = false;
        if ($empleado) {
            if (Hash::check($request->password, $empleado->password)) {
                $valido = true; // bcrypt
            } elseif ($empleado->password === hash('sha256', $request->password)) {
                $valido = true; // SHA-256
            } elseif ($empleado->password === $request->password) {
                $valido = true; // texto plano
            }
        }

        if (!$valido) {
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

        // Roles
        $esJefe = (int) $empleado->jefe === 1;
        $departamentosAutorizadores = DB::connection('sam')
            ->table('departamento')
            ->where(function ($query) {
                $query->whereRaw('LOWER(nombre) LIKE ?', ['%recursos humanos%'])
                      ->orWhereRaw('LOWER(nombre) LIKE ?', ['%recursos materiales%'])
                      ->orWhereRaw('LOWER(nombre) LIKE ?', ['%divisiones de comunicación y difusión%'])
                      ->orWhereRaw('LOWER(nombre) LIKE ?', ['%desarrollo académico%']);
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
                'name'            => $empleado->nombre . ' ' . $empleado->apellidoPa,
                'email'           => $user->email,
                'rol'             => $rolNuevo,
                'id_departamento' => $empleado->id_departamento,
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
