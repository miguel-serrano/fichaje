@extends('layouts.app')

@section('title', 'Editar Permiso: ' . $permission['name'])

@section('content')
<div class="row">
    <div class="col s12 l8 offset-l2">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <span class="card-title">
                            <i class="material-icons left">edit</i>
                            Editar Permiso
                        </span>
                        <p class="grey-text">Modifica la configuración del permiso <strong>{{ $permission['name'] }}</strong>.</p>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <form action="{{ route('admin.permissions.update', $permission['id']) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">label</i>
                            <input id="name" name="name" type="text" class="validate @error('name') invalid @enderror"
                                   value="{{ old('name', $permission['name']) }}" required>
                            <label for="name" class="active">Nombre del Permiso *</label>
                            @error('name')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">code</i>
                            <input id="slug" type="text" class="validate" value="{{ $permission['slug'] }}" disabled>
                            <label for="slug" class="active">Identificador (slug)</label>
                            <span class="helper-text">El identificador no se puede cambiar</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">folder</i>
                            <input id="bounded_context" type="text" class="validate" value="{{ $permission['bounded_context'] }}" disabled>
                            <label for="bounded_context" class="active">Contexto</label>
                            <span class="helper-text">El contexto no se puede cambiar</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">description</i>
                            <textarea id="description" name="description" class="materialize-textarea @error('description') invalid @enderror">{{ old('description', $permission['description']) }}</textarea>
                            <label for="description" class="active">Descripción</label>
                            @error('description')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="divider" style="margin: 20px 0;"></div>

                    <div class="row">
                        <div class="col s12">
                            <a href="{{ route('admin.permissions.index') }}" class="btn-flat waves-effect">
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
