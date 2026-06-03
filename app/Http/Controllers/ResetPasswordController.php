<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Http/Controllers/ResetPasswordController.php
 * Creación:    02/06/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, reset automático de contraseña al nombre de usuario
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function show()
    {
        return view('auth.reset-password-simple');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
        ]);

        $usuario = strtolower(trim($request->usuario));

        $user = User::where('name', $usuario)->first();

        if (!$user) {
            return back()->withErrors([
                'usuario' => 'No se encontró ningún usuario con ese nombre.',
            ]);
        }

        // Resetear contraseña al mismo nombre de usuario
        $user->update([
            'password' => Hash::make($usuario),
        ]);

        return redirect()->route('login')
            ->with('success', "Contraseña restablecida correctamente. Tu nueva contraseña es tu nombre de usuario: {$usuario}");
    }
}