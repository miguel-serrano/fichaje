@extends('layouts.app')

@section('title', 'Gestión de Roles')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12 m8">
                        <span class="card-title">
                            <i class="material-icons left">security</i>
                            Gestión de Roles
                        </span>
                        <p class="grey-text">Administra los roles del sistema y sus permisos asociados.</p>
                    </div>
                    <div class="col s12 m4 right-align">
                        <a href="{{ route('admin.roles.create') }}" class="btn waves-effect waves-light btn-claude">
                            <i class="material-icons left">add</i>Nuevo Rol
                        </a>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="row">
                    @forelse($roles as $role)
                    <div class="col s12 m6 l4">
                        <div class="card hoverable">
                            <div class="card-content">
                                <span class="card-title">
                                    {{ $role['name'] }}
                                    @if($role['is_system'])
                                        <span class="chip chip-warning" style="font-size: 0.8rem;">Sistema</span>
                                    @endif
                                </span>
                                <p class="grey-text"><code>{{ $role['slug'] }}</code></p>
                                @if($role['description'])
                                    <p style="margin-top: 10px;">{{ $role['description'] }}</p>
                                @endif
                                <div style="margin-top: 15px;">
                                    <span class="chip chip-info">
                                        <i class="material-icons tiny">vpn_key</i>
                                        {{ count($role['permissions']) }} permisos
                                    </span>
                                    <span class="chip chip-neutral">
                                        Jerarquía: {{ $role['hierarchy'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-action">
                                <a href="{{ route('admin.roles.show', $role['id']) }}" class="text-claude">Ver</a>
                                @if(!$role['is_system'])
                                    <a href="{{ route('admin.roles.edit', $role['id']) }}" style="color: var(--info);">Editar</a>
                                    <a href="#" style="color: var(--error);" onclick="event.preventDefault(); if(confirm('¿Eliminar este rol?')) document.getElementById('delete-role-{{ $role['id'] }}').submit();">Eliminar</a>
                                    <form id="delete-role-{{ $role['id'] }}" action="{{ route('admin.roles.destroy', $role['id']) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col s12 center-align" style="padding: 60px 20px;">
                        <i class="material-icons grey-text" style="font-size: 72px;">security</i>
                        <h5 class="grey-text text-darken-1">No hay roles</h5>
                        <p class="grey-text">Crea tu primer rol para comenzar.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
