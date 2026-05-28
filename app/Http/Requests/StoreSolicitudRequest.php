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
            'fecha_inicio.after'             => 'La fecha debe ser posterior a la hora actual',
            'lugar_encuentro.required'       => 'El campo lugar de encuentro es obligatorio',
            'motivo_visita.required'         => 'El campo motivo de la visita es obligatorio',
            'id_tipo_solicitud.required'     => 'Seleccione el tipo de visitante',
            'id_tipo_solicitud.exists'       => 'El tipo de visita seleccionado no es válido',
            'tolerancia_antes.required'      => 'El campo tolerancia de llegada es obligatorio',
            'visitante_nombre.*.required'    => 'El nombre del visitante es obligatorio',
            'visitante_nombre.*.min'         => 'El nombre debe tener mínimo 4 caracteres',
            'visitante_apellidos.*.required' => 'Los apellidos del visitante son obligatorios',
            'visitante_apellidos.*.min'      => 'Los apellidos deben tener mínimo 4 caracteres',
            'visitante_correo.*.required'    => 'El correo del visitante es obligatorio',
            'visitante_correo.*.email'       => 'Ingrese un nombre y correo válidos',
            'visitante_correo.*.distinct'    => 'No puede haber correos duplicados en el grupo',
        ];
    }
}