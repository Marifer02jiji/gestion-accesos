<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio'          => 'required|date|after:now',
            'lugar_encuentro'       => 'required|string|max:100',
            'motivo_visita'         => 'required|string|max:255',
            'id_tipo_solicitud'     => 'required|exists:ca_TipoSolicitud,id_tipo_solicitud',
            'tolerancia_antes'      => 'required|in:15,30',
            'tolerancia_despues'    => 'required|in:15,30',
            'visitante_nombre.*'    => 'required|string|max:100',
            'visitante_apellidos.*' => 'required|string|max:100',
            'visitante_correo.*'    => 'required|email|max:150',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.required'          => 'La fecha y hora es obligatoria.',
            'fecha_inicio.after'             => 'La fecha debe ser posterior a la hora actual.',
            'lugar_encuentro.required'       => 'El lugar de encuentro es obligatorio.',
            'motivo_visita.required'         => 'El motivo de visita es obligatorio.',
            'id_tipo_solicitud.required'     => 'Seleccione el tipo de visitante.',
            'id_tipo_solicitud.exists'       => 'El tipo de visita seleccionado no es válido.',
            'tolerancia_antes.required'      => 'La tolerancia de llegada es obligatoria.',
            'tolerancia_despues.required'    => 'La tolerancia de salida es obligatoria.',
            'visitante_nombre.*.required'    => 'El nombre del visitante es obligatorio.',
            'visitante_apellidos.*.required' => 'Los apellidos del visitante son obligatorios.',
            'visitante_correo.*.required'    => 'El correo del visitante es obligatorio.',
            'visitante_correo.*.email'       => 'El correo del visitante debe ser válido.',
        ];
    }
}