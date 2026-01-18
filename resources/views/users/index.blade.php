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
                        <p class="grey-text">
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
                            <table class="striped responsive-table highlight">
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
                                        <td class="grey-text">{{ $user['email'] }}</td>
                                        <td><code style="font-size: 0.85rem;">{{ $user['uuid'] }}</code></td>
                                        <td>
                                            @if($user['is_active'])
                                                <span class="chip chip-success">Activo</span>
                                            @else
                                                <span class="chip chip-error">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="right-align">
                                            <a href="{{ route('user.show', $user['id']) }}" class="btn-small waves-effect waves-light btn-claude">
                                                <i class="material-icons left">visibility</i>Ver
                                            </a>
                                            @if($isAdmin)
                                                <form action="{{ route('user.toggle-active', $user['id']) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    @if($user['is_active'])
                                                        <button type="submit" class="btn-small waves-effect waves-light" style="background: var(--error) !important;">
                                                            <i class="material-icons left">block</i>Desactivar
                                                        </button>
                                                    @else
                                                        <button type="submit" class="btn-small waves-effect waves-light" style="background: var(--success) !important;">
                                                            <i class="material-icons left">check_circle</i>Activar
                                                        </button>
                                                    @endif
                                                </form>
                                                <button type="button"
                                                        class="btn-small waves-effect waves-light btn-delete-user"
                                                        style="background: var(--error) !important;"
                                                        data-user-id="{{ $user['id'] }}"
                                                        data-user-name="{{ $user['name'] }}">
                                                    <i class="material-icons left">delete</i>Borrar
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="center-align" style="padding: 60px 20px;">
                                <i class="material-icons grey-text" style="font-size: 72px;">people_outline</i>
                                <h5 class="grey-text text-darken-1">No hay usuarios</h5>
                                <p class="grey-text">No se encontraron usuarios en el sistema.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar usuario -->
<div id="delete-user-modal" class="modal">
    <div class="modal-content">
        <h5><i class="material-icons left" style="color: var(--error);">warning</i> Confirmar eliminación</h5>
        <p>¿Estás seguro de que deseas eliminar al usuario <strong id="delete-user-name"></strong>?</p>
        <p class="grey-text">Esta acción no se puede deshacer.</p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-light btn-flat">Cancelar</a>
        <form id="delete-user-form" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="waves-effect waves-light btn" style="background: var(--error) !important;">
                <i class="material-icons left">delete</i>Eliminar
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalElem = document.getElementById('delete-user-modal');
    var modalInstance = M.Modal.init(modalElem);

    var deleteButtons = document.querySelectorAll('.btn-delete-user');
    var deleteForm = document.getElementById('delete-user-form');
    var deleteUserName = document.getElementById('delete-user-name');

    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var userId = this.dataset.userId;
            var userName = this.dataset.userName;

            deleteForm.action = '{{ url("user") }}/' + userId;
            deleteUserName.textContent = userName;

            modalInstance.open();
        });
    });
});
</script>
@endsection
