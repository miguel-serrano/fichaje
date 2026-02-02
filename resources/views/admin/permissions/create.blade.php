@extends('layouts.app')

@section('title', 'Crear Permiso')
@section('page-id', 'admin.form')

@section('content')
<div class="row">
    <div class="col s12 l8 offset-l2">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <span class="card-title">
                            <md-icon style="margin-right: 8px;">add_circle</md-icon>
                            Crear Nuevo Permiso
                        </span>
                        <p class="text-secondary">Define un nuevo permiso para el sistema.</p>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                @if(session('error'))
                <div class="card-panel card-panel-error">
                    <md-icon style="margin-right: 8px;">error</md-icon>
                    {{ session('error') }}
                </div>
                @endif

                <form action="{{ route('admin.permissions.store') }}" method="POST" id="create-permission-form">
                    @csrf

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="name"
                                name="name"
                                type="text"
                                label="Nombre del Permiso *"
                                value="{{ old('name') }}"
                                style="width: 100%;"
                                @error('name') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">label</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="slug"
                                name="slug"
                                type="text"
                                label="Identificador (slug) *"
                                value="{{ old('slug') }}"
                                supporting-text="Formato: contexto.accion (ej: user.view, holiday.approve)"
                                style="width: 100%;"
                                @error('slug') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">code</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-select
                                id="bounded_context"
                                name="bounded_context"
                                label="Contexto *"
                                style="width: 100%;"
                                @error('bounded_context') error @enderror
                            >
                                @foreach($contexts as $context)
                                    <md-select-option value="{{ $context }}" {{ old('bounded_context') == $context ? 'selected' : '' }}>
                                        <div slot="headline">{{ $context }}</div>
                                    </md-select-option>
                                @endforeach
                            </md-outlined-select>
                            @error('bounded_context')
                                <p class="text-sm" style="color: var(--error); margin-top: 4px;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="description"
                                name="description"
                                type="textarea"
                                label="Descripción"
                                value="{{ old('description') }}"
                                rows="3"
                                style="width: 100%;"
                                @error('description') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">description</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>

                    <div class="divider" style="margin: 20px 0;"></div>

                    <div class="row">
                        <div class="col s12 d-flex justify-between flex-wrap gap-2">
                            <md-text-button type="button" href="{{ route('admin.permissions.index') }}">
                                <md-icon slot="icon">cancel</md-icon>
                                Cancelar
                            </md-text-button>
                            <md-filled-button type="submit" style="--md-filled-button-container-color: var(--success);">
                                <md-icon slot="icon">save</md-icon>
                                Crear Permiso
                            </md-filled-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-data')
<script>window.__pageData = { formId: 'create-permission-form', fieldNames: ['name', 'slug', 'bounded_context', 'description'] };</script>
@endpush
