<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Gestión de Horarios') - Laravel DDD</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Vite Assets (includes Materialize CSS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="blue-grey lighten-5">
    <nav class="light-green darken-3">
        <div class="nav-wrapper">
            <div class="container">
                <a href="{{ auth()->check() ? route('registro_horario.index') : route('login') }}" class="brand-logo">
                    <i class="material-icons left">access_time</i>TimeTrack
                </a>
                @auth
                <ul id="nav-mobile" class="right">
                    <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}">
                            <i class="material-icons left">info</i>Información
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('registro_horario.*') ? 'active' : '' }}">
                        <a href="{{ route('registro_horario.index') }}">
                            <i class="material-icons left">timer</i>Fichaje
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-trigger" data-target="user-dropdown">
                            <i class="material-icons left">account_circle</i>{{ auth()->user()->name }}<i class="material-icons right">arrow_drop_down</i>
                        </a>
                    </li>
                </ul>
                <ul id="user-dropdown" class="dropdown-content">
                    <li>
                        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="waves-effect waves-light" style="background: none; border: none; width: 100%; text-align: left; padding: 14px 16px; cursor: pointer;">
                                <i class="material-icons left">exit_to_app</i>Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
                @endauth
            </div>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('.dropdown-trigger');
            M.Dropdown.init(elems, {
                coverTrigger: false,
                constrainWidth: false
            });
        });
    </script>

    <main>
        <div class="container" style="padding-top: 20px;">
            @if(session('success'))
                <div class="row">
                    <div class="col s12">
                        <div class="card-panel green lighten-4 green-text text-darken-4">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="row">
                    <div class="col s12">
                        <div class="card-panel red lighten-4 red-text text-darken-4">
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="row">
                    <div class="col s12">
                        <div class="card-panel red lighten-4 red-text text-darken-4">
                            <ul style="margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
