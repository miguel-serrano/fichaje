@extends('layouts.app')

@section('title', 'Registrarse')

@section('content')
<div class="row">
    <div class="col s12 l6 offset-l3">
        <div class="card">
            <div class="card-content">
                <span class="card-title center-align">Crear Cuenta</span>
                <p class="text-secondary center-align">
                    O <a href="{{ route('login') }}" class="text-claude">iniciar sesión con una cuenta existente</a>
                </p>

                <div class="divider" style="margin: 20px 0;"></div>

                <form action="{{ route('register') }}" method="POST" id="register-form">
                    @csrf
                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="name"
                                name="name"
                                type="text"
                                label="Nombre"
                                autocomplete="name"
                                required
                                value="{{ old('name') }}"
                                style="width: 100%;"
                                @error('name') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">person</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="email"
                                name="email"
                                type="email"
                                label="Email"
                                autocomplete="email"
                                required
                                value="{{ old('email') }}"
                                style="width: 100%;"
                                @error('email') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">email</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="password"
                                name="password"
                                type="password"
                                label="Contraseña"
                                autocomplete="new-password"
                                required
                                style="width: 100%;"
                                @error('password') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">lock</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                label="Confirmar Contraseña"
                                autocomplete="new-password"
                                required
                                style="width: 100%;"
                            >
                                <md-icon slot="leading-icon">lock_outline</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <md-filled-button type="submit" style="width: 100%;">
                                <md-icon slot="icon">person_add</md-icon>
                                Registrarse
                            </md-filled-button>
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
    // Handle form submission with Material Web text fields
    const form = document.getElementById('register-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const fields = ['name', 'email', 'password', 'password_confirmation'];

            fields.forEach(function(fieldName) {
                const field = document.getElementById(fieldName);
                if (field && field.value) {
                    let hiddenInput = form.querySelector('input[name="' + fieldName + '"][type="hidden"]');
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = fieldName;
                        form.appendChild(hiddenInput);
                    }
                    hiddenInput.value = field.value;
                }
            });
        });
    }
});
</script>
@endsection
