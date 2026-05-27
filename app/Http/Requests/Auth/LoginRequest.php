<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario'  => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Obtener input
        $usuarioInput = $this->usuario;

        // Si escribieron correo institucional,
        // quitar el dominio para buscar en SAM
        if (str_contains($usuarioInput, '@toluca.tecnm.mx')) {
            $usuarioInput = str_replace('@toluca.tecnm.mx', '', $usuarioInput);
        }

        // Buscar empleado en SAM
        $empleado = \App\Models\Empleado::where('usuario', $usuarioInput)
            ->where('estatus', 'Activo')
            ->first();

        // Validar usuario y contraseña
        if (!$empleado || $empleado->password !== hash('sha256', $this->password)) {

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'usuario' => __('Las credenciales no coinciden con nuestros registros.'),
            ]);
        }

        // Buscar o crear usuario en gestion_accesos_db
        $user = \App\Models\User::firstOrCreate(
            [
                'email' => $empleado->usuario . '@toluca.tecnm.mx'
            ],
            [
                'name'            => $empleado->nombre . ' ' . $empleado->apellidoPa,
                'email'           => $empleado->usuario . '@toluca.tecnm.mx',
                'password'        => bcrypt($this->password),
                'id_empleado_sam' => $empleado->id_empleado,
            ]
        );

        // Actualizar id_empleado_sam si no lo tenía
        if (!$user->id_empleado_sam) {
            $user->update([
                'id_empleado_sam' => $empleado->id_empleado
            ]);
        }

        // Asignar rol según credenciales del SAM
        $roles = match($empleado->credenciales) {
            'Administrador master' => ['administrador', 'autorizador'],
            default => ['solicitante'],
        };

        if (!$user->hasAllRoles($roles)) {
            $user->assignRole($roles);
        }

        // Login
        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'usuario' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('usuario')) . '|' . $this->ip()
        );
    }
}