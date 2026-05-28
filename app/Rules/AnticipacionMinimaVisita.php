<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AnticipacionMinimaVisita implements ValidationRule
{
    public function __construct(private int $horas = 1) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        $zona = config('app.timezone');

        // datetime-local envía hora local sin zona; interpretar en la zona de la app
        $fechaCita = Carbon::parse($value, $zona);
        $minimaPermitida = now($zona)->addHours($this->horas);

        if ($fechaCita->lt($minimaPermitida)) {
            $fail("La cita debe ser a las {$minimaPermitida->format('H:i')} o después (mínimo {$this->horas} hora de anticipación).");
        }
    }
}
