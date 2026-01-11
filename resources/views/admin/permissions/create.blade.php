@extends('layouts.app')

@section('title', 'Crear Permiso')

@section('content')
<div class="row">
    <div class="col s12 l8 offset-l2">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <span class="card-title">
                            <i class="material-icons left">add_circle</i>
                            Crear Nuevo Permiso
                        </span>
                        <p class="grey-text">Define un nuevo permiso para el sistema.</p>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                @if(session('error'))
                <div class="card-panel red lighten-4 red-text text-darken-4">
                    <i class="material-icons left">error</i>
                    {{ session('error') }}
                </div>
                @endif

                <form action="{{ route('admin.permissions.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">label</i>
                            <input id="name" name="name" type="text" class="validate @error('name') invalid @enderror"
                                   value="{{ old('name') }}">
                            <label for="name">Nombre del Permiso *</label>
                            @error('name')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">code</i>
                            <input id="slug" name="slug" type="text" class="validate @error('slug') invalid @enderror"
                                   value="{{ old('slug') }}">
                            <label for="slug">Identificador (slug) *</label>
                            <span class="helper-text">Formato: contexto.accion (ej: user.view, holiday.approve)</span>
                            @error('slug')
                                <span class="helper-text red-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">folder</i>
                            <select id="bounded_context" name="bounded_context">
                                <option value="" disabled selected>Selecciona un contexto</option>
                                @foreach($contexts as $context)
                                    <option value="{{ $context }}" {{ old('bounded_context') == $context ? 'selected' : '' }}>{{ $context }}</option>
                                @endforeach
                            </select>
                            <label for="bounded_context">Contexto *</label>
                            @error('bounded_context')
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

                    <div class="divider" style="margin: 20px 0;"></div>

                    <div class="row">
                        <div class="col s12">
                            <a href="{{ route('admin.permissions.index') }}" class="btn-flat waves-effect">
                                <i class="material-icons left">cancel</i>Cancelar
                            </a>
                            <button type="submit" class="btn waves-effect waves-light light-green right">
                                <i class="material-icons left">save</i>Crear Permiso
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var selects = document.querySelectorAll('select');
    M.FormSelect.init(selects);
});
</script>
@endsection
