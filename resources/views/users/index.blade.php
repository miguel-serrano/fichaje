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
                                        <td>{{ $user['name'] }}</td>
                                        <td class="grey-text">{{ $user['email'] }}</td>
                                        <td><code class="grey-text" style="font-size: 0.85rem;">{{ $user['uuid'] }}</code></td>
                                        <td>
                                            @if($user['is_active'])
                                                <span class="chip green lighten-4 green-text text-darken-2">Activo</span>
                                            @else
                                                <span class="chip red lighten-4 red-text text-darken-2">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="right-align">
                                            @if($isAdmin)
                                                <form action="{{ route('user.toggle-active', $user['id']) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    @if($user['is_active'])
                                                        <button type="submit" class="btn-small waves-effect waves-light red">
                                                            <i class="material-icons left">block</i>Desactivar
                                                        </button>
                                                    @else
                                                        <button type="submit" class="btn-small waves-effect waves-light light-green">
                                                            <i class="material-icons left">check_circle</i>Activar
                                                        </button>
                                                    @endif
                                                </form>
                                                <form action="{{ route('user.delete', $user['id']) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-small waves-effect waves-light red darken-3">
                                                        <i class="material-icons left">delete</i>Borrar
                                                    </button>
                                                </form>
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
@endsection
