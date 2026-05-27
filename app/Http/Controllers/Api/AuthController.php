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

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario'  => 'required|string',
            'password' => 'required|string',
        ]);

        $usuarioInput = $request->usuario;

        if (str_contains($usuarioInput, '@toluca.tecnm.mx')) {
            $usuarioInput = str_replace('@toluca.tecnm.mx', '', $usuarioInput);
        }

        $empleado = Empleado::where('usuario', $usuarioInput)
            ->where('estatus', 'Activo')
            ->first();

        if (!$empleado || $empleado->password !== hash('sha256', $request->password)) {
            return response()->json([
                'message' => 'Las credenciales no coinciden con nuestros registros.',
                'data'    => null,
            ], 401);
        }

        $user = User::firstOrCreate(
            ['email' => $empleado->usuario . '@toluca.tecnm.mx'],
            [
                'name'            => $empleado->nombre . ' ' . $empleado->apellidoPa,
                'email'           => $empleado->usuario . '@toluca.tecnm.mx',
                'password'        => bcrypt($request->password),
                'id_empleado_sam' => $empleado->id_empleado,
            ]
        );

        if (!$user->id_empleado_sam) {
            $user->update(['id_empleado_sam' => $empleado->id_empleado]);
        }

        // Determinar si el empleado es autorizador
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