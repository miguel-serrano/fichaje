@extends('layouts.app')

@section('title', 'Rol: ' . $role['name'])

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12 m8">
                        <span class="card-title">{{ $role['name'] }}</span>
                        <p class="text-secondary"><code>{{ $role['slug'] }}</code></p>
                    </div>
                    <div class="col s12 m4 right-align">
                        <md-text-button href="{{ route('admin.roles.index') }}">
                            <md-icon slot="icon">arrow_back</md-icon>
                            Volver
                        </md-text-button>
                        @if(!$role['is_system'])
                            <md-filled-tonal-button href="{{ route('admin.roles.edit', $role['id']) }}">
                                <md-icon slot="icon">edit</md-icon>
                                Editar
                            </md-filled-tonal-button>
                        @endif
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="row">
                    <div class="col s12 m6">
                        <h6 class="text-secondary">Descripción</h6>
                        <p>{{ $role['description'] ?: 'Sin descripción' }}</p>
                    </div>
                    <div class="col s6 m3">
                        <h6 class="text-secondary">Jerarquía</h6>
                        <p>{{ $role['hierarchy'] }}</p>
                    </div>
                    <div class="col s6 m3">
                        <h6 class="text-secondary">Tipo</h6>
                        <p>
                            @if($role['is_system'])
                                <span class="status-badge status-badge-warning">Sistema</span>
                            @else
                                <span class="status-badge status-badge-success">Personalizado</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">vpn_key</md-icon>
                    Permisos del Rol
                </span>

                <form action="{{ route('admin.roles.permissions.sync', $role['id']) }}" method="POST" id="permissions-form">
                    @csrf
                    @method('PUT')

                    @foreach($permissionsByContext as $context => $permissions)
                    <div class="section">
                        <h6 class="text-secondary">
                            <md-icon style="font-size: 14px;">folder</md-icon> {{ $context }}
                            <span class="badge">{{ count($permissions) }}</span>
                        </h6>
                        <div class="row">
                            @foreach($permissions as $permission)
                            <div class="col s12 m6 l4" style="margin-bottom: 12px;">
                                <label class="d-flex align-center gap-2 cursor-pointer">
                                    <md-checkbox
                                        data-permission-id="{{ $permission['id'] }}"
                                        {{ in_array($permission['id'], $rolePermissionIds) ? 'checked' : '' }}
                                    ></md-checkbox>
                                    <span>
                                        {{ $permission['name'] }}
                                        <br><small class="text-secondary">{{ $permission['slug'] }}</small>
                                    </span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="divider"></div>
                    @endforeach

                    <div class="section right-align">
                        <md-filled-button type="submit" style="--md-filled-button-container-color: var(--success);">
                            <md-icon slot="icon">save</md-icon>
                            Guardar Cambios
                        </md-filled-button>
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
    const form = document.getElementById('permissions-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Get all checked md-checkbox elements and create hidden inputs
            const checkboxes = form.querySelectorAll('md-checkbox');
            const existingHidden = form.querySelectorAll('input[name="permissions[]"][type="hidden"]');
            existingHidden.forEach(el => el.remove());

            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'permissions[]';
                    hiddenInput.value = checkbox.dataset.permissionId;
                    form.appendChild(hiddenInput);
                }
            });
        });
    }
});
</script>
@endsection
