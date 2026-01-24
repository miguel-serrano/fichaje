@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <span class="card-title">Usuarios</span>
                        <p class="text-secondary">
                            @if($isAdmin)
                                Lista de todos los usuarios del sistema.
                            @else
                                Tu información de usuario.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col s12">
                        @if(count($users) > 0)
                            <div class="overflow-x-auto">
                                <table class="striped highlight">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>UUID</th>
                                            <th>Estado</th>
                                            <th class="right-align">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                        <tr>
                                            <td><a href="{{ route('user.show', $user['id']) }}" class="text-claude">{{ $user['name'] }}</a></td>
                                            <td class="text-secondary">{{ $user['email'] }}</td>
                                            <td><code style="font-size: 0.85rem;">{{ $user['uuid'] }}</code></td>
                                            <td>
                                                @if($user['is_active'])
                                                    <span class="status-badge status-badge-success">Activo</span>
                                                @else
                                                    <span class="status-badge status-badge-error">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="right-align">
                                                <div class="action-buttons">
                                                    <md-icon-button href="{{ route('user.show', $user['id']) }}" title="Ver detalles">
                                                        <md-icon>visibility</md-icon>
                                                    </md-icon-button>
                                                    @if($isAdmin)
                                                        <form action="{{ route('user.toggle-active', $user['id']) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            @if($user['is_active'])
                                                                <md-icon-button type="submit" title="Desactivar usuario" style="--md-icon-button-icon-color: var(--error);">
                                                                    <md-icon>block</md-icon>
                                                                </md-icon-button>
                                                            @else
                                                                <md-icon-button type="submit" title="Activar usuario" style="--md-icon-button-icon-color: var(--success);">
                                                                    <md-icon>check_circle</md-icon>
                                                                </md-icon-button>
                                                            @endif
                                                        </form>
                                                        <md-icon-button
                                                            type="button"
                                                            class="btn-delete-user"
                                                            title="Eliminar usuario"
                                                            style="--md-icon-button-icon-color: var(--error);"
                                                            data-user-id="{{ $user['id'] }}"
                                                            data-user-name="{{ $user['name'] }}"
                                                        >
                                                            <md-icon>delete</md-icon>
                                                        </md-icon-button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="center-align" style="padding: 60px 20px;">
                                <md-icon class="text-secondary" style="font-size: 72px; width: 72px; height: 72px;">people_outline</md-icon>
                                <h5 class="text-secondary">No hay usuarios</h5>
                                <p class="text-secondary">No se encontraron usuarios en el sistema.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar usuario -->
<md-dialog id="delete-user-dialog">
    <div slot="headline">
        <md-icon style="color: var(--error); vertical-align: middle; margin-right: 8px;">warning</md-icon>
        Confirmar eliminación
    </div>
    <form slot="content" id="delete-user-form" method="POST">
        @csrf
        @method('DELETE')
        <p>¿Estás seguro de que deseas eliminar al usuario <strong id="delete-user-name"></strong>?</p>
        <p class="text-secondary">Esta acción no se puede deshacer.</p>
    </form>
    <div slot="actions">
        <md-text-button form="delete-user-form" value="cancel" type="button" id="cancel-delete-btn">Cancelar</md-text-button>
        <md-filled-button form="delete-user-form" type="submit" style="--md-filled-button-container-color: var(--error);">
            <md-icon slot="icon">delete</md-icon>
            Eliminar
        </md-filled-button>
    </div>
</md-dialog>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dialog = document.getElementById('delete-user-dialog');
    const deleteButtons = document.querySelectorAll('.btn-delete-user');
    const deleteForm = document.getElementById('delete-user-form');
    const deleteUserName = document.getElementById('delete-user-name');
    const cancelBtn = document.getElementById('cancel-delete-btn');

    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;

            deleteForm.action = '{{ url("user") }}/' + userId;
            deleteUserName.textContent = userName;

            dialog.open = true;
        });
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            dialog.open = false;
        });
    }

    // Close dialog when clicking outside
    dialog.addEventListener('close', function() {
        dialog.open = false;
    });
});
</script>
@endsection
