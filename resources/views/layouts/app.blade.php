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
    <div class="navbar-fixed">
    <nav class="light-green darken-3">
        <div class="nav-wrapper">
            <div class="container">
                <a href="{{ auth()->check() ? route('registro_horario.index') : route('login') }}" class="brand-logo">
                    <i class="material-icons left">access_time</i>TimeTrack<sup style="font-size: 12px; margin-left: 4px; opacity: 0.8;">beta</sup>
                </a>
                @auth
                <a href="#" data-target="mobile-nav" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    @if(auth()->user()->is_admin)
                        <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}">
                                <i class="material-icons left">people</i>Usuarios
                            </a>
                        </li>
                    @else
                        <li class="{{ request()->routeIs('user.me') ? 'active' : '' }}">
                            <a href="{{ route('user.me') }}">
                                <i class="material-icons left">chrome_reader_mode</i>Seguimiento
                            </a>
                        </li>
                    @endif
                    <li class="{{ request()->routeIs('registro_horario.*') ? 'active' : '' }}">
                        <a href="{{ route('registro_horario.index') }}">
                            <i class="material-icons left">timer</i>Fichar
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
                            <button type="submit" class="waves-effect waves-light pink-text text-darken-2" style="background: none; border: none; width: 100%; text-align: left; padding: 14px 16px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <i class="material-icons">exit_to_app</i>Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
                @endauth
            </div>
        </div>
    </nav>
    </div>

    @auth
    <ul class="sidenav" id="mobile-nav">
        <li>
            <div class="user-view">
                <div class="background light-green darken-3"></div>
                <span class="white-text name">{{ auth()->user()->name }}</span>
                <span class="white-text email">{{ auth()->user()->email }}</span>
            </div>
        </li>
        @if(auth()->user()->is_admin)
            <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}">
                    <i class="material-icons">people</i>Usuarios
                </a>
            </li>
        @else
            <li class="{{ request()->routeIs('user.me') ? 'active' : '' }}">
                <a href="{{ route('user.me') }}">
                    <i class="material-icons">chrome_reader_mode</i>Seguimiento
                </a>
            </li>
        @endif
        <li class="{{ request()->routeIs('registro_horario.*') ? 'active' : '' }}">
            <a href="{{ route('registro_horario.index') }}">
                <i class="material-icons">timer</i>Fichar
            </a>
        </li>
        <li><div class="divider"></div></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="waves-effect pink-text text-darken-2" style="background: none; border: none; width: 100%; text-align: left; padding: 0 32px; cursor: pointer; display: flex; align-items: center; gap: 32px; height: 48px; line-height: 48px;">
                    <i class="material-icons">exit_to_app</i>Cerrar Sesión
                </button>
            </form>
        </li>
    </ul>
    @endauth

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dropdowns = document.querySelectorAll('.dropdown-trigger');
            M.Dropdown.init(dropdowns, {
                coverTrigger: false,
                constrainWidth: false
            });

            var sidenavs = document.querySelectorAll('.sidenav');
            M.Sidenav.init(sidenavs);
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
