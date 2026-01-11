<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_slug' => 'required|string|exists:roles,slug',
        ];
    }

    public function messages(): array
    {
        return [
            'role_slug.required' => 'El rol es obligatorio.',
            'role_slug.string' => 'El rol debe ser una cadena de texto.',
            'role_slug.exists' => 'El rol seleccionado no existe.',
        ];
    }
}
