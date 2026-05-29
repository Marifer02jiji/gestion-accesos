<?php

namespace App\Http\Requests;

use App\Rules\AnticipacionMinimaVisita;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio'          => ['required', 'date', new AnticipacionMinimaVisita(1), function($attribute, $value, $fail) {
                $fecha = Carbon::parse($value);
                $hora  = (int) $fecha->format('H');
                $dia   = (int) $fecha->dayOfWeek; // 0=domingo, 6=sabado

                // No permitir domingos
                if ($dia === 0) {
                    $fail('No se pueden agendar visitas los domingos.');
                    return;
                }

                // Sabados hasta las 14:00
                if ($dia === 6 && $hora >= 14) {
                    $fail('Los sabados solo se permiten visitas hasta las 2:00 PM.');
                    return;
                }

                // Horario permitido: 6am a 9pm
                if ($hora < 6 || $hora >= 21) {
                    $fail('Las visitas solo pueden agendarse entre las 6:00 AM y las 9:00 PM.');
                    return;
                }
            }],
            'lugar_encuentro'       => 'required|string|max:100',
            'motivo_visita'         => 'required|string|min:5',
            'id_tipo_solicitud'     => 'required|exists:ca_TipoSolicitud,id_tipo_solicitud',
            'tolerancia_antes'      => 'required|in:15,30',
            'visitante_nombre.*'    => 'required|string|min:4|max:100',
            'visitante_apellidos.*' => 'required|string|min:4|max:100',
            'visitante_correo.*'    => 'required|email|max:150|distinct',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.required'          => 'El campo fecha y hora es obligatorio',
            'lugar_encuentro.required'       => 'El campo lugar de encuentro es obligatorio',
            'motivo_visita.required'         => 'El campo motivo de la visita es obligatorio',
            'id_tipo_solicitud.required'     => 'Seleccione el tipo de visitante',
            'id_tipo_solicitud.exists'       => 'El tipo de visita seleccionado no es valido',
            'tolerancia_antes.required'      => 'El campo tolerancia de llegada es obligatorio',
            'visitante_nombre.*.required'    => 'El nombre del visitante es obligatorio',
            'visitante_nombre.*.min'         => 'El nombre debe tener minimo 4 caracteres',
            'visitante_apellidos.*.required' => 'Los apellidos del visitante son obligatorios',
            'visitante_apellidos.*.min'      => 'Los apellidos deben tener minimo 4 caracteres',
            'visitante_correo.*.required'    => 'El correo del visitante es obligatorio',
            'visitante_correo.*.email'       => 'Ingrese un nombre y correo validos',
            'visitante_correo.*.distinct'    => 'No puede haber correos duplicados en el grupo',
        ];
    }
}