@extends('layouts.app')

@section('title', 'Editar Rol: ' . $role['name'])

@section('content')
<div class="row">
    <div class="col s12 l8 offset-l2">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <span class="card-title">
                            <i class="material-icons left">edit</i>
                            Editar Rol
                        </span>
                        <p class="grey-text">Modifica la configuración del rol <strong>{{ $role['name'] }}</strong>.</p>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <form action="{{ route('admin.roles.update', $role['id']) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">label</i>
                            <input id="name" name="name" type="text" class="validate @error('name') invalid @enderror"
                                   value="{{ old('name', $role['name']) }}" required>
                            <label for="name" class="active">Nombre del Rol *</label>
                            @error('name')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">code</i>
                            <input id="slug" type="text" class="validate" value="{{ $role['slug'] }}" disabled>
                            <label for="slug" class="active">Identificador (slug)</label>
                            <span class="helper-text">El identificador no se puede cambiar</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">description</i>
                            <textarea id="description" name="description" class="materialize-textarea @error('description') invalid @enderror">{{ old('description', $role['description']) }}</textarea>
                            <label for="description" class="active">Descripción</label>
                            @error('description')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">sort</i>
                            <input id="hierarchy" name="hierarchy" type="number" class="validate @error('hierarchy') invalid @enderror"
                                   value="{{ old('hierarchy', $role['hierarchy']) }}" min="0" max="100">
                            <label for="hierarchy" class="active">Jerarquía (0-100)</label>
                            <span class="helper-text">Mayor valor = más privilegios</span>
                            @error('hierarchy')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="divider" style="margin: 20px 0;"></div>

                    <div class="row">
                        <div class="col s12">
                            <a href="{{ route('admin.roles.show', $role['id']) }}" class="btn-flat waves-effect">
                                <i class="material-icons left">cancel</i>Cancelar
                            </a>
                            <button type="submit" class="btn waves-effect waves-light light-green right">
                                <i class="material-icons left">save</i>Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
