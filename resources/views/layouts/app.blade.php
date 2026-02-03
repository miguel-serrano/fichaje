<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Gestión de Horarios') - Laravel DDD</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Material Icons (preload to prevent layout shift) -->
    <link rel="preload" href="https://fonts.googleapis.com/icon?family=Material+Icons" as="style">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Theme initialization (prevents flash) -->
    <script>
    (function() {
        const saved = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', saved);
    })();
    </script>
</head>
<body data-page="@yield('page-id')">
    <div class="navbar-fixed">
    <nav>
        <div class="nav-wrapper">
            <a href="{{ auth()->check() ? route('registro_horario.index') : route('login') }}" class="brand-logo">
                <md-icon>access_time</md-icon>TimeTrack
            </a>
            @auth
            <a href="#" class="sidenav-trigger" id="sidenav-trigger">
                <md-icon>menu</md-icon>
            </a>
            <ul id="nav-mobile" class="hide-on-med-and-down">
                @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'))
                    <x-nav-item
                        :route="route('users.index')"
                        icon="people"
                        label="Usuarios"
                        :active="request()->routeIs('users.*') || request()->routeIs('user.*')"
                    />
                    <x-nav-item
                        :route="route('admin.roles.index')"
                        icon="security"
                        label="Roles"
                        :active="request()->routeIs('admin.roles.*')"
                    />
                    <x-nav-item
                        :route="route('admin.permissions.index')"
                        icon="vpn_key"
                        label="Permisos"
                        :active="request()->routeIs('admin.permissions.*')"
                    />
                @else
                    <x-nav-item
                        :route="route('user.me')"
                        icon="chrome_reader_mode"
                        label="Seguimiento"
                        :active="request()->routeIs('user.me')"
                    />
                @endif
                <x-nav-item
                    :route="route('registro_horario.index')"
                    icon="timer"
                    label="Fichar"
                    :active="request()->routeIs('registro_horario.*')"
                />
                <x-nav-item
                    :route="route('holidays.index')"
                    icon="beach_access"
                    label="Vacaciones"
                    :active="request()->routeIs('holidays.*')"
                />
                <x-nav-item
                    :route="route('report.me')"
                    icon="assessment"
                    label="Informe"
                    :active="request()->routeIs('report.*')"
                />
                @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'))
                    <x-nav-item
                        :route="route('admin.holidays.index')"
                        icon="event_available"
                        label="Gestionar Vacaciones"
                        :active="request()->routeIs('admin.holidays.*')"
                    />
                @endif
                @php
                    $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                    $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->count();
                @endphp
                <li class="menu-anchor">
                    <a href="#" id="notifications-trigger" style="position: relative;">
                        <md-icon>notifications</md-icon>
                        @if($unreadCount > 0)
                            <span class="badge red" style="position: absolute; top: 4px; right: 0; min-width: 18px; height: 18px; line-height: 18px; font-size: 10px; font-weight: 500; border-radius: 50%;">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <md-menu id="notifications-menu" anchor="notifications-trigger" positioning="popover" style="min-width: 300px; max-height: 400px;">
                        @if($unreadNotifications->isEmpty())
                            <md-menu-item disabled>
                                <div slot="headline" style="text-align: center; padding: 16px;">
                                    <md-icon style="font-size: 32px; display: block; margin-bottom: 8px; opacity: 0.5;">notifications_none</md-icon>
                                    No tienes notificaciones
                                </div>
                            </md-menu-item>
                        @else
                            @foreach($unreadNotifications as $notification)
                                <md-menu-item class="notification-item" data-id="{{ $notification->id }}">
                                    <div slot="headline" style="font-size: 13px; font-weight: 600;">{{ $notification->title }}</div>
                                    <div slot="supporting-text">
                                        <span style="font-size: 12px;">{{ $notification->message }}</span>
                                        <small style="display: block; margin-top: 4px; opacity: 0.6;">{{ $notification->getCreatedAtCarbon()->diffForHumans() }}</small>
                                    </div>
                                </md-menu-item>
                            @endforeach
                            @if($unreadCount > 5)
                                <md-menu-item disabled>
                                    <div slot="headline" style="text-align: center; font-size: 12px; opacity: 0.6;">
                                        Y {{ $unreadCount - 5 }} más...
                                    </div>
                                </md-menu-item>
                            @endif
                        @endif
                    </md-menu>
                </li>
                <li>
                    <a href="#" id="theme-toggle">
                        <md-icon id="theme-icon">dark_mode</md-icon>
                    </a>
                </li>
                <li class="menu-anchor">
                    <a href="#" id="user-trigger">
                        <md-icon>account_circle</md-icon>{{ auth()->user()->name }}<md-icon>arrow_drop_down</md-icon>
                    </a>
                    <md-menu id="user-menu" anchor="user-trigger" positioning="popover">
                        <md-menu-item id="logout-menu-item">
                            <md-icon slot="start">exit_to_app</md-icon>
                            <div slot="headline">Cerrar Sesión</div>
                        </md-menu-item>
                    </md-menu>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
            @endauth
        </div>
    </nav>
    </div>

    @auth
    <!-- Sidenav Overlay -->
    <div class="sidenav-overlay" id="sidenav-overlay"></div>

    <!-- Sidenav -->
    <div class="sidenav" id="mobile-nav">
        <div class="user-view">
            <span class="name">{{ auth()->user()->name }}</span>
            <span class="email">{{ auth()->user()->email }}</span>
        </div>
        <ul>
            @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'))
                <x-nav-item
                    :route="route('users.index')"
                    icon="people"
                    label="Usuarios"
                    :active="request()->routeIs('users.*') || request()->routeIs('user.*')"
                />
                <x-nav-item
                    :route="route('admin.roles.index')"
                    icon="security"
                    label="Roles"
                    :active="request()->routeIs('admin.roles.*')"
                />
                <x-nav-item
                    :route="route('admin.permissions.index')"
                    icon="vpn_key"
                    label="Permisos"
                    :active="request()->routeIs('admin.permissions.*')"
                />
            @else
                <x-nav-item
                    :route="route('user.me')"
                    icon="chrome_reader_mode"
                    label="Seguimiento"
                    :active="request()->routeIs('user.me')"
                />
            @endif
            <x-nav-item
                :route="route('registro_horario.index')"
                icon="timer"
                label="Fichar"
                :active="request()->routeIs('registro_horario.*')"
            />
            <x-nav-item
                :route="route('holidays.index')"
                icon="beach_access"
                label="Vacaciones"
                :active="request()->routeIs('holidays.*')"
            />
            <x-nav-item
                :route="route('report.me')"
                icon="assessment"
                label="Informe"
                :active="request()->routeIs('report.*')"
            />
            @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'))
                <x-nav-item
                    :route="route('admin.holidays.index')"
                    icon="event_available"
                    label="Gestionar Vacaciones"
                    :active="request()->routeIs('admin.holidays.*')"
                />
            @endif
        </ul>
        <div class="divider"></div>
        <ul>
            <li>
                <a href="#" id="theme-toggle-mobile">
                    <md-icon id="theme-icon-mobile">dark_mode</md-icon>Cambiar tema
                </a>
            </li>
            <li>
                <a href="#" id="logout-mobile">
                    <md-icon>exit_to_app</md-icon>Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
    @endauth

    <main>
        <div class="container" style="padding-top: 20px;">
            <x-flash-messages />
            @yield('content')
        </div>
    </main>

    @stack('page-data')
    @yield('scripts')
</body>
</html>
