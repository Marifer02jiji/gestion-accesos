<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EscanearQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_qr' => ['required', 'string', 'regex:/^VIS-[A-Z0-9]{4}-[A-Z0-9]{4}$/'],
            'telefono'  => ['required', 'string'],
            'area'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_qr.required' => 'El código QR es obligatorio.',
            'codigo_qr.regex'    => 'El formato del código QR no es válido (esperado: VIS-XXXX-XXXX).',
            'telefono.required'  => 'El teléfono del vigilante es obligatorio.',
        ];
    }
}