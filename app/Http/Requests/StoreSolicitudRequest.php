<?php

/**
 * Empresa:     OMEGA Solutions
 * Proyecto:    ProyectoC - Sistema de Gestión de Accesos y Visitas
 * Archivo:     app/Http/Requests/StoreSolicitudRequest.php
 * Creación:    19/03/2026
 * Creado por:  Jacqueline Marifer Escobar Espinoza
 * Aprobado por: Líder de Área
 *
 * Changelog:
 * ID: 1 | Fecha: 19/03/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Creación inicial, validación de campos de nueva solicitud
 * ID: 2 | Fecha: 07/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Validación de horarios L-V 7-21h, Sáb hasta 14h, no domingos
 * ID: 3 | Fecha: 28/05/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Agregar regla AnticipacionMinimaVisita de 1 hora
 * ID: 4 | Fecha: 02/06/2026 | Modificado por: Jacqueline Marifer Escobar Espinoza | Descripción: Reactivar validación de días tras período de pruebas
 */

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
           'fecha_inicio' => ['required', 'date', new AnticipacionMinimaVisita(1), function($attribute, $value, $fail) {
                $fecha = Carbon::parse($value);
                $hora  = (int) $fecha->format('H');
                $dia   = (int) $fecha->dayOfWeek;

                if ($dia === 0) {
                     $fail('No se pueden agendar visitas los domingos.');
                     return;
                }

                if ($dia === 7 && $hora >= 14) {
                    $fail('Los sabados solo se permiten visitas hasta las 2:00 PM.');
                    return;
                }

                if ($hora < 7 || $hora >= 21) {
                    $fail('Las visitas solo pueden agendarse entre las 7:00 AM y las 9:00 PM.');
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