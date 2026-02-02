@extends('layouts.app')

@section('title', 'Registrarse')
@section('page-id', 'auth.register')

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
                    {{-- Hidden inputs for form submission (Material Web compatibility) --}}
                    <input type="hidden" name="name" id="name-hidden" value="{{ old('name') }}">
                    <input type="hidden" name="email" id="email-hidden" value="{{ old('email') }}">
                    <input type="hidden" name="password" id="password-hidden" value="">
                    <input type="hidden" name="password_confirmation" id="password_confirmation-hidden" value="">

                    <div class="row">
                        <div class="col s12" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="name"
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

