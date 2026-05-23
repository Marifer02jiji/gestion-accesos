<?php

/**
 * Empresa: OMEGA
 * Proyecto: Sistema de Gestión de Accesos
 * Creación: 07/05/2026
 * Creado por: Desarrollador
 * Aprobado por: Líder del Área
 *
 * Changelog:
 * ID: 1 | Fecha: 07/05/2026 | Modificado por: Desarrollador | Descripción: Creación inicial
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVigilanteEscanearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_qr' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_qr.required' => 'El código QR es obligatorio.',
        ];
    }
}