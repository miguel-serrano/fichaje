@extends('layouts.app')

@section('title', 'Registrarse')

@section('content')
<div class="row">
    <div class="col s12 l6 offset-l3">
        <div class="card">
            <div class="card-content">
                <span class="card-title center-align">Crear Cuenta</span>
                <p class="grey-text center-align">
                    O <a href="{{ route('login') }}" class="light-green-text text-darken-3">iniciar sesión con una cuenta existente</a>
                </p>

                <div class="divider" style="margin: 20px 0;"></div>

                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">person</i>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                autocomplete="name"
                                required
                                value="{{ old('name') }}"
                                class="validate"
                            >
                            <label for="name">Nombre</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">email</i>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                value="{{ old('email') }}"
                                class="validate"
                            >
                            <label for="email">Email</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">lock</i>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                required
                                class="validate"
                            >
                            <label for="password">Contraseña</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">lock_outline</i>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                required
                                class="validate"
                            >
                            <label for="password_confirmation">Confirmar Contraseña</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <button type="submit" class="btn waves-effect waves-light light-green darken-3 col s12">
                                <i class="material-icons left">person_add</i>Registrarse
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
