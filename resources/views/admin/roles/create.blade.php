@extends('layouts.app')

@section('title', 'Crear Rol')

@section('content')
<div class="row">
    <div class="col s12 l8 offset-l2">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <span class="card-title">
                            <i class="material-icons left">add_circle</i>
                            Crear Nuevo Rol
                        </span>
                        <p class="grey-text">Define un nuevo rol con su configuración.</p>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">label</i>
                            <input id="name" name="name" type="text" class="validate @error('name') invalid @enderror"
                                   value="{{ old('name') }}" required>
                            <label for="name">Nombre del Rol *</label>
                            @error('name')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">code</i>
                            <input id="slug" name="slug" type="text" class="validate @error('slug') invalid @enderror"
                                   value="{{ old('slug') }}" required pattern="^[a-z][a-z0-9_]*$">
                            <label for="slug">Identificador (slug) *</label>
                            <span class="helper-text">Solo letras minúsculas, números y guiones bajos. Ej: admin, super_admin</span>
                            @error('slug')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">description</i>
                            <textarea id="description" name="description" class="materialize-textarea @error('description') invalid @enderror">{{ old('description') }}</textarea>
                            <label for="description">Descripción</label>
                            @error('description')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">sort</i>
                            <input id="hierarchy" name="hierarchy" type="number" class="validate @error('hierarchy') invalid @enderror"
                                   value="{{ old('hierarchy', 0) }}" min="0" max="100">
                            <label for="hierarchy">Jerarquía (0-100)</label>
                            <span class="helper-text">Mayor valor = más privilegios</span>
                            @error('hierarchy')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="divider" style="margin: 20px 0;"></div>

                    <div class="row">
                        <div class="col s12">
                            <a href="{{ route('admin.roles.index') }}" class="btn-flat waves-effect">
                                <i class="material-icons left">cancel</i>Cancelar
                            </a>
                            <button type="submit" class="btn waves-effect waves-light light-green right">
                                <i class="material-icons left">save</i>Crear Rol
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
