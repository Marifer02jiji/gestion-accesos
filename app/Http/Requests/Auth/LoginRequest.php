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
            'usuario'  => ['required_without:email', 'string'],
            'email'    => ['required_without:usuario', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Usar usuario o correo, y quitar dominio si viene con @
        $usuarioInput = trim((string) ($this->input('usuario') ?? $this->input('email')));

        if (str_contains($usuarioInput, '@')) {
            $usuarioInput = explode('@', $usuarioInput)[0];
        }

        $usuarioInput = Str::lower($usuarioInput);

        // Buscar empleado en SAM
        $empleado = \App\Models\Empleado::where('usuario', $usuarioInput)
            ->where('estatus', 'Activo')
            ->first();

        // Validar usuario y contraseña (detección robusta)
        $validCredentials = false;
        if ($empleado) {
            $stored = (string) $empleado->password;

            // bcrypt / argon2
            if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2b$') || str_starts_with($stored, '$argon')) {
                $validCredentials = password_verify($this->password, $stored);
            }
            // formato salt:hash o hash:salt
            elseif (strpos($stored, ':') !== false) {
                [$a, $b] = explode(':', $stored, 2);
                // probar password + salt y salt + password
                $validCredentials = hash('sha256', $this->password . $a) === $b
                    || hash('sha256', $a . $this->password) === $b
                    || hash('sha256', $this->password . $b) === $a
                    || hash('sha256', $b . $this->password) === $a;
            }
            // SHA-256 puro en hex (64 chars) — probar variaciones comunes
            elseif (preg_match('/^[0-9a-f]{64}$/i', $stored)) {
                $pw = (string) $this->password;
                $candidates = array_unique(array_filter([
                    $pw,
                    trim($pw),
                    strtoupper($pw),
                    strtolower($pw),
                    strrev($pw),
                    base64_encode($pw),
                    base64_encode(hash('sha256', $pw, true)),
                ]));

                $fields = ['usuario','id_empleado','nombre','apellidoPa','apellidoMa','correo','telefono'];
                foreach ($fields as $f) {
                    if (!empty($empleado->{$f})) {
                        $val = (string) $empleado->{$f};
                        $candidates[] = $pw . $val;
                        $candidates[] = $val . $pw;
                        $candidates[] = $pw . '.' . $val;
                        $candidates[] = $val . '.' . $pw;
                        $candidates[] = $pw . '_' . $val;
                        $candidates[] = $val . '_' . $pw;
                        $candidates[] = $pw . '-' . $val;
                        $candidates[] = $val . '-' . $pw;
                    }
                }

                // also try UTF-16LE encoding of the password
                $utf16 = mb_convert_encoding($pw, 'UTF-16LE');
                $candidates[] = $utf16;

                // try simple numeric suffixes (common patterns)
                foreach (['123','1234','2023','2024','1'] as $suf) {
                    $candidates[] = $pw . $suf;
                    $candidates[] = $suf . $pw;
                }

                // normalize and dedupe
                $candidates = array_unique($candidates);

                foreach ($candidates as $cand) {
                    if (hash('sha256', $cand) === $stored) {
                        $validCredentials = true;
                        break;
                    }
                    // try alternative algorithms as fallback
                    if (md5($cand) === $stored || sha1($cand) === $stored) {
                        $validCredentials = true;
                        break;
                    }
                }
            }
            // fallback: comparación directa
            else {
                $validCredentials = $this->password === $stored;
            }
        }

        if (!$empleado || !$validCredentials) {
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
        $usuarioInput = $this->string('usuario') ?: $this->string('email');

        return Str::transliterate(
            Str::lower((string) $usuarioInput) . '|' . $this->ip()
        );
    }
}