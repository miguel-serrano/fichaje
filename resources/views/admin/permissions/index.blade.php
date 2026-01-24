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
                            <md-icon style="margin-right: 8px;">vpn_key</md-icon>
                            Gestión de Permisos
                        </span>
                        <p class="text-secondary">Administra los permisos del sistema agrupados por contexto.</p>
                    </div>
                    <div class="col s12 m4 right-align">
                        <md-filled-button href="{{ route('admin.permissions.create') }}" style="--md-filled-button-container-color: var(--success);">
                            <md-icon slot="icon">add</md-icon>
                            Nuevo Permiso
                        </md-filled-button>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                @if(count($permissionsByContext) > 0)
                <ul class="collapsible" id="permissions-collapsible">
                    @foreach($permissionsByContext as $context => $permissions)
                    <li class="collapsible-item">
                        <details open>
                            <summary class="collapsible-header">
                                <md-icon>folder</md-icon>
                                <span style="flex: 1;">{{ $context }}</span>
                                <span class="badge">{{ count($permissions) }} permisos</span>
                                <md-icon class="expand-icon">expand_more</md-icon>
                            </summary>
                            <div class="collapsible-content">
                                {{-- Mobile view: Cards --}}
                                <div class="hide-on-med-and-up">
                                    @foreach($permissions as $permission)
                                    <div class="permission-card-mobile">
                                        <div class="permission-card-header">
                                            <div>
                                                <span class="permission-card-name">{{ $permission['name'] }}</span>
                                                <code class="text-secondary permission-card-slug" style="font-size: 0.75rem;">{{ $permission['slug'] }}</code>
                                            </div>
                                            @if($permission['is_system'])
                                                <span class="status-badge status-badge-warning">Sistema</span>
                                            @else
                                                <span class="status-badge status-badge-success">Personalizado</span>
                                            @endif
                                        </div>
                                        @if($permission['description'])
                                            <div class="permission-card-description">{{ $permission['description'] }}</div>
                                        @endif
                                        @if(!$permission['is_system'])
                                            <div class="permission-card-actions">
                                                <md-filled-tonal-button href="{{ route('admin.permissions.edit', $permission['id']) }}">
                                                    <md-icon slot="icon">edit</md-icon>
                                                    Editar
                                                </md-filled-tonal-button>
                                                <form action="{{ route('admin.permissions.destroy', $permission['id']) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este permiso?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <md-outlined-button type="submit" style="--md-outlined-button-outline-color: var(--error); --md-outlined-button-label-text-color: var(--error);">
                                                        <md-icon slot="icon">delete</md-icon>
                                                        Eliminar
                                                    </md-outlined-button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>

                                {{-- Desktop view: Table --}}
                                <div class="hide-on-small-only">
                                    <table class="striped">
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
                                                <td><code class="text-secondary">{{ $permission['slug'] }}</code></td>
                                                <td class="text-secondary">{{ $permission['description'] ?: '-' }}</td>
                                                <td>
                                                    @if($permission['is_system'])
                                                        <span class="status-badge status-badge-warning">Sistema</span>
                                                    @else
                                                        <span class="status-badge status-badge-success">Personalizado</span>
                                                    @endif
                                                </td>
                                                <td class="right-align">
                                                    @if(!$permission['is_system'])
                                                        <md-filled-tonal-button href="{{ route('admin.permissions.edit', $permission['id']) }}">
                                                            <md-icon slot="icon">edit</md-icon>
                                                        </md-filled-tonal-button>
                                                        <form action="{{ route('admin.permissions.destroy', $permission['id']) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este permiso?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <md-filled-button type="submit" style="--md-filled-button-container-color: var(--error);">
                                                                <md-icon slot="icon">delete</md-icon>
                                                            </md-filled-button>
                                                        </form>
                                                    @else
                                                        <span class="text-secondary">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </details>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="center-align" style="padding: 60px 20px;">
                    <md-icon class="text-secondary" style="font-size: 72px; width: 72px; height: 72px;">vpn_key</md-icon>
                    <h5 class="text-secondary">No hay permisos</h5>
                    <p class="text-secondary">Crea tu primer permiso para comenzar.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
