@props([
    'id',
    'type' => 'text',
    'label',
    'icon' => null,
    'name' => null,
    'value' => '',
    'required' => false,
])

@php
    $fieldName = $name ?? $id;
    $errorMessage = $errors->first($fieldName);
@endphp

<md-outlined-text-field
    id="{{ $id }}"
    type="{{ $type }}"
    label="{{ $label }}"
    value="{{ old($fieldName, $value) }}"
    @if($required) required @endif
    style="width: 100%;"
    @if($errorMessage) error error-text="{{ $errorMessage }}" @endif
    {{ $attributes }}
>
    @if($icon)
        <md-icon slot="leading-icon">{{ $icon }}</md-icon>
    @endif
</md-outlined-text-field>
<input type="hidden" name="{{ $fieldName }}" value="{{ old($fieldName, $value) }}">
