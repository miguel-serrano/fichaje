<?php

namespace App\Http\Requests\Admin;

use App\DDD\Authorization\Domain\ValueObjects\BoundedContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contexts = array_column(BoundedContext::cases(), 'value');

        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug|regex:/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/',
            'bounded_context' => ['required', 'string', Rule::in($contexts)],
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'slug.required' => 'El identificador del permiso es obligatorio.',
            'slug.unique' => 'Ya existe un permiso con este identificador.',
            'slug.regex' => 'El identificador debe tener formato contexto.accion (ej: user.view).',
            'bounded_context.required' => 'El contexto es obligatorio.',
            'bounded_context.in' => 'El contexto seleccionado no es válido.',
            'description.max' => 'La descripción no puede superar los 1000 caracteres.',
        ];
    }
}
