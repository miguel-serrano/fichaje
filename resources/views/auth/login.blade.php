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
                    {{-- Hidden inputs for form submission (Material Web compatibility) --}}
                    <input type="hidden" name="email" id="email-hidden" value="{{ old('email') }}">
                    <input type="hidden" name="password" id="password-hidden" value="">

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="email"
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
    // Sync Material Web text fields with hidden inputs in real-time
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    const emailHidden = document.getElementById('email-hidden');
    const passwordHidden = document.getElementById('password-hidden');

    if (emailField && emailHidden) {
        emailField.addEventListener('input', () => emailHidden.value = emailField.value);
        // Initial sync in case of autofill
        emailHidden.value = emailField.value;
    }

    if (passwordField && passwordHidden) {
        passwordField.addEventListener('input', () => passwordHidden.value = passwordField.value);
    }

    // Also sync on form submit as backup
    const form = document.getElementById('login-form');
    if (form) {
        form.addEventListener('submit', function() {
            if (emailField && emailHidden) emailHidden.value = emailField.value;
            if (passwordField && passwordHidden) passwordHidden.value = passwordField.value;
        });
    }
});
</script>
@endsection
