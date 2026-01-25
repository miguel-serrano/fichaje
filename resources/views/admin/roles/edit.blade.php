@extends('layouts.app')

@section('title', 'Editar Rol: ' . $role['name'])

@section('content')
<div class="row">
    <div class="col s12 l8 offset-l2">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <span class="card-title">
                            <md-icon style="margin-right: 8px;">edit</md-icon>
                            Editar Rol
                        </span>
                        <p class="text-secondary">Modifica la configuración del rol <strong>{{ $role['name'] }}</strong>.</p>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <form action="{{ route('admin.roles.update', $role['id']) }}" method="POST" id="edit-role-form">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="name"
                                name="name"
                                type="text"
                                label="Nombre del Rol *"
                                value="{{ old('name', $role['name']) }}"
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
                                type="text"
                                label="Identificador (slug)"
                                value="{{ $role['slug'] }}"
                                disabled
                                supporting-text="El identificador no se puede cambiar"
                                style="width: 100%;"
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
                                value="{{ old('description', $role['description']) }}"
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
                                value="{{ old('hierarchy', $role['hierarchy']) }}"
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
                            <md-text-button type="button" href="{{ route('admin.roles.show', $role['id']) }}">
                                <md-icon slot="icon">cancel</md-icon>
                                Cancelar
                            </md-text-button>
                            <md-filled-button type="submit" style="--md-filled-button-container-color: var(--success);">
                                <md-icon slot="icon">save</md-icon>
                                Guardar Cambios
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
    const form = document.getElementById('edit-role-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            ['name', 'description', 'hierarchy'].forEach(function(fieldName) {
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
