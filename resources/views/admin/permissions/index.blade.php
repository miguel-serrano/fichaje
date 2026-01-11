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
                        <a href="{{ route('admin.permissions.create') }}" class="btn waves-effect waves-light light-green">
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
                                                <a href="{{ route('admin.permissions.edit', $permission['id']) }}" class="btn-small waves-effect waves-light blue">
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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var collapsibles = document.querySelectorAll('.collapsible.expandable');
    M.Collapsible.init(collapsibles, {
        accordion: false
    });
});
</script>
@endsection
