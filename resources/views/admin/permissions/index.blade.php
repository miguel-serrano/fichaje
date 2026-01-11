@extends('layouts.app')

@section('title', 'Gestión de Permisos')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12 m8">
                        <span class="card-title">
                            <i class="material-icons left">vpn_key</i>
                            Gestión de Permisos
                        </span>
                        <p class="grey-text">Administra los permisos del sistema agrupados por contexto.</p>
                    </div>
                    <div class="col s12 m4 right-align">
                        <a href="#modal-create-permission" class="btn waves-effect waves-light light-green modal-trigger">
                            <i class="material-icons left">add</i>Nuevo Permiso
                        </a>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                @if(count($permissionsByContext) > 0)
                <ul class="collapsible expandable">
                    @foreach($permissionsByContext as $context => $permissions)
                    <li class="active">
                        <div class="collapsible-header">
                            <i class="material-icons">folder</i>
                            <span class="title">{{ $context }}</span>
                            <span class="badge">{{ count($permissions) }} permisos</span>
                        </div>
                        <div class="collapsible-body">
                            <table class="striped responsive-table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Slug</th>
                                        <th>Descripción</th>
                                        <th>Tipo</th>
                                        <th class="right-align">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissions as $permission)
                                    <tr>
                                        <td>{{ $permission['name'] }}</td>
                                        <td><code class="grey-text">{{ $permission['slug'] }}</code></td>
                                        <td class="grey-text">{{ $permission['description'] ?: '-' }}</td>
                                        <td>
                                            @if($permission['is_system'])
                                                <span class="chip amber lighten-4 amber-text text-darken-2">Sistema</span>
                                            @else
                                                <span class="chip green lighten-4 green-text text-darken-2">Personalizado</span>
                                            @endif
                                        </td>
                                        <td class="right-align">
                                            @if(!$permission['is_system'])
                                                <a href="#modal-edit-permission-{{ $permission['id'] }}" class="btn-small waves-effect waves-light blue modal-trigger">
                                                    <i class="material-icons">edit</i>
                                                </a>
                                                <form action="{{ route('admin.permissions.destroy', $permission['id']) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este permiso?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-small waves-effect waves-light red">
                                                        <i class="material-icons">delete</i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="grey-text">-</span>
                                            @endif
                                        </td>
                                    </tr>

                                    @if(!$permission['is_system'])
                                    <!-- Modal Edit Permission -->
                                    <div id="modal-edit-permission-{{ $permission['id'] }}" class="modal">
                                        <form action="{{ route('admin.permissions.update', $permission['id']) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                                <h5>Editar Permiso</h5>
                                                <p class="grey-text">Modifica el permiso <strong>{{ $permission['slug'] }}</strong></p>

                                                <div class="row">
                                                    <div class="input-field col s12">
                                                        <input id="edit-name-{{ $permission['id'] }}" name="name" type="text"
                                                               value="{{ $permission['name'] }}" required>
                                                        <label for="edit-name-{{ $permission['id'] }}" class="active">Nombre *</label>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="input-field col s12">
                                                        <textarea id="edit-description-{{ $permission['id'] }}" name="description"
                                                                  class="materialize-textarea">{{ $permission['description'] }}</textarea>
                                                        <label for="edit-description-{{ $permission['id'] }}" class="active">Descripción</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="#!" class="modal-close waves-effect waves-light btn-flat">Cancelar</a>
                                                <button type="submit" class="waves-effect waves-light btn light-green">
                                                    <i class="material-icons left">save</i>Guardar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    @endif

                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="center-align" style="padding: 60px 20px;">
                    <i class="material-icons grey-text" style="font-size: 72px;">vpn_key</i>
                    <h5 class="grey-text text-darken-1">No hay permisos</h5>
                    <p class="grey-text">Crea tu primer permiso para comenzar.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Permission -->
<div id="modal-create-permission" class="modal">
    <form action="{{ route('admin.permissions.store') }}" method="POST">
        @csrf
        <div class="modal-content">
            <h5>Crear Nuevo Permiso</h5>
            <p class="grey-text">Define un nuevo permiso para el sistema.</p>

            <div class="row">
                <div class="input-field col s12">
                    <input id="create-name" name="name" type="text" required>
                    <label for="create-name">Nombre *</label>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s12">
                    <input id="create-slug" name="slug" type="text" required pattern="^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$">
                    <label for="create-slug">Slug *</label>
                    <span class="helper-text">Formato: contexto.accion (ej: user.view, holiday.approve)</span>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s12">
                    <select id="create-bounded_context" name="bounded_context" required>
                        <option value="" disabled selected>Selecciona un contexto</option>
                        @foreach($contexts as $context)
                            <option value="{{ $context }}">{{ $context }}</option>
                        @endforeach
                    </select>
                    <label for="create-bounded_context">Contexto *</label>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s12">
                    <textarea id="create-description" name="description" class="materialize-textarea"></textarea>
                    <label for="create-description">Descripción</label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-light btn-flat">Cancelar</a>
            <button type="submit" class="waves-effect waves-light btn light-green">
                <i class="material-icons left">save</i>Crear
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var collapsibles = document.querySelectorAll('.collapsible.expandable');
    M.Collapsible.init(collapsibles, {
        accordion: false
    });

    var modals = document.querySelectorAll('.modal');
    M.Modal.init(modals);

    var selects = document.querySelectorAll('select');
    M.FormSelect.init(selects);
});
</script>
@endsection
