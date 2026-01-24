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
                            <md-icon style="margin-right: 8px;">security</md-icon>
                            Gestión de Roles
                        </span>
                        <p class="text-secondary">Administra los roles del sistema y sus permisos asociados.</p>
                    </div>
                    <div class="col s12 m4 right-align">
                        <md-filled-button href="{{ route('admin.roles.create') }}">
                            <md-icon slot="icon">add</md-icon>
                            Nuevo Rol
                        </md-filled-button>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="row">
                    @forelse($roles as $role)
                    <div class="col s12 m6 l4">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">
                                    {{ $role['name'] }}
                                    @if($role['is_system'])
                                        <span class="status-badge status-badge-warning" style="font-size: 0.8rem;">Sistema</span>
                                    @endif
                                </span>
                                <p class="text-secondary"><code>{{ $role['slug'] }}</code></p>
                                @if($role['description'])
                                    <p style="margin-top: 10px;">{{ $role['description'] }}</p>
                                @endif
                                <div class="badge-group" style="margin-top: 15px;">
                                    <span class="status-badge status-badge-info">
                                        <md-icon style="font-size: 14px;">vpn_key</md-icon>
                                        {{ count($role['permissions']) }} permisos
                                    </span>
                                    <span class="status-badge status-badge-neutral">
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
                        <md-icon class="text-secondary" style="font-size: 72px; width: 72px; height: 72px;">security</md-icon>
                        <h5 class="text-secondary">No hay roles</h5>
                        <p class="text-secondary">Crea tu primer rol para comenzar.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
