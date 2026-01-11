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
                        <p class="grey-text"><code>{{ $role['slug'] }}</code></p>
                    </div>
                    <div class="col s12 m4 right-align">
                        <a href="{{ route('admin.roles.index') }}" class="btn-flat waves-effect">
                            <i class="material-icons left">arrow_back</i>Volver
                        </a>
                        @if(!$role['is_system'])
                            <a href="{{ route('admin.roles.edit', $role['id']) }}" class="btn waves-effect waves-light blue">
                                <i class="material-icons left">edit</i>Editar
                            </a>
                        @endif
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="row">
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">Descripción</h6>
                        <p>{{ $role['description'] ?: 'Sin descripción' }}</p>
                    </div>
                    <div class="col s6 m3">
                        <h6 class="grey-text text-darken-1">Jerarquía</h6>
                        <p>{{ $role['hierarchy'] }}</p>
                    </div>
                    <div class="col s6 m3">
                        <h6 class="grey-text text-darken-1">Tipo</h6>
                        <p>
                            @if($role['is_system'])
                                <span class="chip amber lighten-4 amber-text text-darken-2">Sistema</span>
                            @else
                                <span class="chip green lighten-4 green-text text-darken-2">Personalizado</span>
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
                    <i class="material-icons left">vpn_key</i>
                    Permisos del Rol
                </span>

                <form action="{{ route('admin.roles.permissions.sync', $role['id']) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @foreach($permissionsByContext as $context => $permissions)
                    <div class="section">
                        <h6 class="grey-text text-darken-1">
                            <i class="material-icons tiny">folder</i> {{ $context }}
                            <span class="badge">{{ count($permissions) }}</span>
                        </h6>
                        <div class="row">
                            @foreach($permissions as $permission)
                            <div class="col s12 m6 l4">
                                <label>
                                    <input type="checkbox" name="permissions[]" value="{{ $permission['id'] }}"
                                        {{ in_array($permission['id'], $rolePermissionIds) ? 'checked' : '' }}
                                        class="filled-in" />
                                    <span>
                                        {{ $permission['name'] }}
                                        <br><small class="grey-text">{{ $permission['slug'] }}</small>
                                    </span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="divider"></div>
                    @endforeach

                    <div class="section right-align">
                        <button type="submit" class="btn waves-effect waves-light light-green">
                            <i class="material-icons left">save</i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
