<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SyncPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.array' => 'Los permisos deben ser una lista.',
            'permissions.*.integer' => 'Cada permiso debe ser un identificador numérico.',
            'permissions.*.exists' => 'Uno de los permisos seleccionados no existe.',
        ];
    }
}
