@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="row">
    <div class="col s12 l6 offset-l3">
        <div class="card">
            <div class="card-content">
                <span class="card-title center-align">Iniciar Sesión</span>
                <p class="grey-text center-align">
                    O <a href="{{ route('register') }}" class="light-green-text text-darken-3">crear una cuenta nueva</a>
                </p>

                <div class="divider" style="margin: 20px 0;"></div>

                <form action="{{ route('login') }}" method="POST">
                    @csrf
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
                                autocomplete="current-password"
                                required
                                class="validate"
                            >
                            <label for="password">Contraseña</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <button type="submit" class="btn waves-effect waves-light light-green darken-3 col s12">
                                <i class="material-icons left">login</i>Iniciar Sesión
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
