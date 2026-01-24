@extends('layouts.app')

@section('title', 'Crear Rol')

@section('content')
<div class="row">
    <div class="col s12 l8 offset-l2">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <span class="card-title">
                            <md-icon style="margin-right: 8px;">add_circle</md-icon>
                            Crear Nuevo Rol
                        </span>
                        <p class="text-secondary">Define un nuevo rol con su configuración.</p>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <form action="{{ route('admin.roles.store') }}" method="POST" id="create-role-form">
                    @csrf

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="name"
                                name="name"
                                type="text"
                                label="Nombre del Rol *"
                                value="{{ old('name') }}"
                                required
                                style="width: 100%;"
                                @error('name') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">label</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="slug"
                                name="slug"
                                type="text"
                                label="Identificador (slug) *"
                                value="{{ old('slug') }}"
                                required
                                pattern="^[a-z][a-z0-9_]*$"
                                supporting-text="Solo letras minúsculas, números y guiones bajos. Ej: admin, super_admin"
                                style="width: 100%;"
                                @error('slug') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">code</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="description"
                                name="description"
                                type="textarea"
                                label="Descripción"
                                value="{{ old('description') }}"
                                rows="3"
                                style="width: 100%;"
                                @error('description') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">description</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12 m6" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="hierarchy"
                                name="hierarchy"
                                type="number"
                                label="Jerarquía (0-100)"
                                value="{{ old('hierarchy', 0) }}"
                                min="0"
                                max="100"
                                supporting-text="Mayor valor = más privilegios"
                                style="width: 100%;"
                                @error('hierarchy') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">sort</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>

                    <div class="divider" style="margin: 20px 0;"></div>

                    <div class="row">
                        <div class="col s12 d-flex justify-between flex-wrap gap-2">
                            <md-text-button href="{{ route('admin.roles.index') }}">
                                <md-icon slot="icon">cancel</md-icon>
                                Cancelar
                            </md-text-button>
                            <md-filled-button type="submit" style="--md-filled-button-container-color: var(--success);">
                                <md-icon slot="icon">save</md-icon>
                                Crear Rol
                            </md-filled-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('create-role-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            ['name', 'slug', 'description', 'hierarchy'].forEach(function(fieldName) {
                const field = document.getElementById(fieldName);
                if (field && field.value !== undefined) {
                    let hiddenInput = form.querySelector('input[name="' + fieldName + '"][type="hidden"]');
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = fieldName;
                        form.appendChild(hiddenInput);
                    }
                    hiddenInput.value = field.value;
                }
            });
        });
    }
});
</script>
@endsection
