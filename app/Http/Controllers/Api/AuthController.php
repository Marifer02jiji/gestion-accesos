<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Http/Controllers/Api/AuthController.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, login API con validación SAM SHA-256 y token Sanctum
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Buscar usuario por name, retornar rol singular y notificaciones no leídas
 * ID: 3 | Fecha: 19/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Fix solo asignar rol si isEmpty para no sobreescribir roles manuales
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\User;
use App\Services\AutorizacionVisitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

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

        // Buscar por name, que corresponde al usuario SAM.
        // Esto evita duplicar usuarios cuando inician con usuario o correo.
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
            $user->update([
                'id_empleado_sam' => $empleado->id_empleado,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Asignación de roles sin borrar roles existentes
        |--------------------------------------------------------------------------
        | Todo usuario SAM puede ser solicitante.
        | Si además es jefe o pertenece a un departamento autorizador,
        | también se le agrega el rol autorizador.
        |
        | IMPORTANTE:
        | No usamos syncRoles() porque eso podría borrar roles manuales.
        */

        Role::firstOrCreate([
            'name'       => 'solicitante',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name'       => 'autorizador',
            'guard_name' => 'web',
        ]);

        if (!$user->hasRole('solicitante')) {
            $user->assignRole('solicitante');
        }

        $esJefe = (int) $empleado->jefe === 1;

        $departamentosAutorizadores = DB::connection('sam')
            ->table('departamento')
            ->where(function ($q) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ['%recursos humanos%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%recursos materiales%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%comunicacion y difusion%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%comunicación y difusión%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%desarrollo academico%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%desarrollo académico%']);
            })
            ->pluck('id_departamento')
            ->toArray();

        $esDeptoAutorizador = in_array(
            (int) $empleado->id_departamento,
            $departamentosAutorizadores,
            true
        );

        $esAutorizadorPorMatriz = app(AutorizacionVisitaService::class)
            ->usuarioEsAutorizadorConfigurado($usuarioInput);

        if (
            ($esJefe || $esDeptoAutorizador || $esAutorizadorPorMatriz)
            && !$user->hasRole('autorizador')
        ) {
            $user->assignRole('autorizador');
        }

        // Recargar roles después de asignar.
        $user->load('roles');

        $roles = $user->getRoleNames();

        // Si tiene rol autorizador, lo dejamos como rol principal.
        // Pero en "roles" se siguen mandando todos.
        $rolFinal = $roles->contains('autorizador')
            ? 'autorizador'
            : ($roles->first() ?? 'solicitante');

        $token = $user->createToken('flutter-token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'data'    => [
                'token'           => $token,
                'id'              => $user->id,
                'id_empleado_sam' => $empleado->id_empleado,
                'name'            => trim($empleado->nombre . ' ' . $empleado->apellidoPa),
                'email'           => $user->email,
                'usuario_sam'     => $usuarioInput,
                'rol'             => $rolFinal,
                'rol_api'         => $rolFinal,
                'roles'           => $roles,
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

}
