@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="row">
    <div class="col s12 l6 offset-l3">
        <div class="card">
            <div class="card-content">
                <span class="card-title center-align">Iniciar Sesión</span>
                <p class="text-secondary center-align">
                    O <a href="{{ route('register') }}" class="text-claude">crear una cuenta nueva</a>
                </p>

                <div class="divider" style="margin: 20px 0;"></div>

                <form action="{{ route('login') }}" method="POST" id="login-form">
                    @csrf
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
                                autocomplete="current-password"
                                required
                                style="width: 100%;"
                                @error('password') error error-text="{{ $message }}" @enderror
                            >
                                <md-icon slot="leading-icon">lock</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <md-filled-button type="submit" style="width: 100%;">
                                <md-icon slot="icon">login</md-icon>
                                Iniciar Sesión
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
    const form = document.getElementById('login-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');

            // Create hidden inputs with the values from md-outlined-text-field
            if (emailField && emailField.value) {
                let hiddenEmail = form.querySelector('input[name="email"][type="hidden"]');
                if (!hiddenEmail) {
                    hiddenEmail = document.createElement('input');
                    hiddenEmail.type = 'hidden';
                    hiddenEmail.name = 'email';
                    form.appendChild(hiddenEmail);
                }
                hiddenEmail.value = emailField.value;
            }

            if (passwordField && passwordField.value) {
                let hiddenPassword = form.querySelector('input[name="password"][type="hidden"]');
                if (!hiddenPassword) {
                    hiddenPassword = document.createElement('input');
                    hiddenPassword.type = 'hidden';
                    hiddenPassword.name = 'password';
                    form.appendChild(hiddenPassword);
                }
                hiddenPassword.value = passwordField.value;
            }
        });
    }
});
</script>
@endsection
