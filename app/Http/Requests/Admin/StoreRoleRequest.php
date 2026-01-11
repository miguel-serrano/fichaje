<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug|regex:/^[a-z][a-z0-9_]*$/',
            'description' => 'nullable|string|max:1000',
            'hierarchy' => 'nullable|integer|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'slug.required' => 'El identificador del rol es obligatorio.',
            'slug.unique' => 'Ya existe un rol con este identificador.',
            'slug.regex' => 'El identificador debe comenzar con letra minúscula y contener solo letras minúsculas, números y guiones bajos.',
            'description.max' => 'La descripción no puede superar los 1000 caracteres.',
            'hierarchy.integer' => 'La jerarquía debe ser un número entero.',
            'hierarchy.min' => 'La jerarquía no puede ser negativa.',
            'hierarchy.max' => 'La jerarquía no puede ser mayor a 100.',
        ];
    }
}
