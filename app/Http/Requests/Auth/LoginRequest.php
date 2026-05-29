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

        $usuarioInput = trim((string) ($this->input('usuario') ?? $this->input('email')));

        if (str_contains($usuarioInput, '@')) {
            $usuarioInput = explode('@', $usuarioInput)[0];
        }

        $usuarioInput = Str::lower($usuarioInput);

        $empleado = \App\Models\Empleado::where('usuario', $usuarioInput)
            ->where('estatus', 'Activo')
            ->first();

        $validCredentials = false;
        if ($empleado) {
            $stored = (string) $empleado->password;

            if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2b$') || str_starts_with($stored, '$argon')) {
                $validCredentials = password_verify($this->password, $stored);
            }
            elseif (strpos($stored, ':') !== false) {
                [$a, $b] = explode(':', $stored, 2);
                $validCredentials = hash('sha256', $this->password . $a) === $b
                    || hash('sha256', $a . $this->password) === $b
                    || hash('sha256', $this->password . $b) === $a
                    || hash('sha256', $b . $this->password) === $a;
            }
            elseif (preg_match('/^[0-9a-f]{64}$/i', $stored)) {
                $pw = (string) $this->password;
                $candidates = array_unique(array_filter([
                    $pw, trim($pw), strtoupper($pw), strtolower($pw),
                    strrev($pw), base64_encode($pw),
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

                $utf16 = mb_convert_encoding($pw, 'UTF-16LE');
                $candidates[] = $utf16;

                foreach (['123','1234','2023','2024','1'] as $suf) {
                    $candidates[] = $pw . $suf;
                    $candidates[] = $suf . $pw;
                }

                $candidates = array_unique($candidates);

                foreach ($candidates as $cand) {
                    if (hash('sha256', $cand) === $stored) {
                        $validCredentials = true;
                        break;
                    }
                    if (md5($cand) === $stored || sha1($cand) === $stored) {
                        $validCredentials = true;
                        break;
                    }
                }
            }
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

        // Buscar por name (usuario SAM) — respeta el email y roles que ya tenga
        $user = \App\Models\User::where('name', $usuarioInput)->first();

        if (!$user) {
            $user = \App\Models\User::create([
                'name'            => $usuarioInput,
                'email'           => $usuarioInput . '@toluca.tecnm.mx',
                'password'        => bcrypt($this->password),
                'id_empleado_sam' => $empleado->id_empleado,
            ]);
        }

        if (!$user->id_empleado_sam) {
            $user->update(['id_empleado_sam' => $empleado->id_empleado]);
        }

        // Solo asignar rol si no tiene ninguno — respeta roles manuales
        if ($user->roles->isEmpty()) {
            $roles = match($empleado->credenciales) {
                'Administrador master' => ['administrador', 'autorizador'],
                default => ['solicitante'],
            };
            $user->assignRole($roles);
        }

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
        return Str::transliterate(Str::lower((string) $usuarioInput) . '|' . $this->ip());
    }
}