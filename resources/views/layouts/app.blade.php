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
<body>
    <div class="navbar-fixed">
    <nav>
        <div class="nav-wrapper">
            <a href="{{ auth()->check() ? route('registro_horario.index') : route('login') }}" class="brand-logo">
                <md-icon>access_time</md-icon>TimeTrack<sup style="font-size: 12px; margin-left: 4px; opacity: 0.8;">beta</sup>
            </a>
            @auth
            <a href="#" class="sidenav-trigger" id="sidenav-trigger">
                <md-icon>menu</md-icon>
            </a>
            <ul id="nav-mobile" class="hide-on-med-and-down">
                @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'))
                    <li class="{{ request()->routeIs('users.*') || request()->routeIs('user.*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}">
                            <md-icon>people</md-icon>Usuarios
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.roles.index') }}">
                            <md-icon>security</md-icon>Roles
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.permissions.index') }}">
                            <md-icon>vpn_key</md-icon>Permisos
                        </a>
                    </li>
                @else
                    <li class="{{ request()->routeIs('user.me') ? 'active' : '' }}">
                        <a href="{{ route('user.me') }}">
                            <md-icon>chrome_reader_mode</md-icon>Seguimiento
                        </a>
                    </li>
                @endif
                <li class="{{ request()->routeIs('registro_horario.*') ? 'active' : '' }}">
                    <a href="{{ route('registro_horario.index') }}">
                        <md-icon>timer</md-icon>Fichar
                    </a>
                </li>
                <li class="{{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                    <a href="{{ route('holidays.index') }}">
                        <md-icon>beach_access</md-icon>Vacaciones
                    </a>
                </li>
                @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'))
                    <li class="{{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.holidays.index') }}">
                            <md-icon>event_available</md-icon>Gestionar Vacaciones
                        </a>
                    </li>
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
                <li class="{{ request()->routeIs('users.*') || request()->routeIs('user.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}">
                        <md-icon>people</md-icon>Usuarios
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.roles.index') }}">
                        <md-icon>security</md-icon>Roles
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.permissions.index') }}">
                        <md-icon>vpn_key</md-icon>Permisos
                    </a>
                </li>
            @else
                <li class="{{ request()->routeIs('user.me') ? 'active' : '' }}">
                    <a href="{{ route('user.me') }}">
                        <md-icon>chrome_reader_mode</md-icon>Seguimiento
                    </a>
                </li>
            @endif
            <li class="{{ request()->routeIs('registro_horario.*') ? 'active' : '' }}">
                <a href="{{ route('registro_horario.index') }}">
                    <md-icon>timer</md-icon>Fichar
                </a>
            </li>
            <li class="{{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                <a href="{{ route('holidays.index') }}">
                    <md-icon>beach_access</md-icon>Vacaciones
                </a>
            </li>
            @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin'))
                <li class="{{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.holidays.index') }}">
                        <md-icon>event_available</md-icon>Gestionar Vacaciones
                    </a>
                </li>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidenav functionality
        const sidenavTrigger = document.getElementById('sidenav-trigger');
        const sidenav = document.getElementById('mobile-nav');
        const sidenavOverlay = document.getElementById('sidenav-overlay');

        function openSidenav() {
            if (sidenav) sidenav.classList.add('open');
            if (sidenavOverlay) sidenavOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeSidenav() {
            if (sidenav) sidenav.classList.remove('open');
            if (sidenavOverlay) sidenavOverlay.classList.remove('open');
            document.body.style.overflow = '';
        }

        if (sidenavTrigger) {
            sidenavTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                openSidenav();
            });
        }

        if (sidenavOverlay) {
            sidenavOverlay.addEventListener('click', closeSidenav);
        }

        // Close sidenav when clicking a link
        if (sidenav) {
            sidenav.querySelectorAll('a[href]:not([href="#"])').forEach(function(link) {
                link.addEventListener('click', closeSidenav);
            });
        }

        // md-menu initialization
        const notificationsTrigger = document.getElementById('notifications-trigger');
        const notificationsMenu = document.getElementById('notifications-menu');
        const userTrigger = document.getElementById('user-trigger');
        const userMenu = document.getElementById('user-menu');

        if (notificationsTrigger && notificationsMenu) {
            notificationsMenu.anchorElement = notificationsTrigger;
            notificationsTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                notificationsMenu.open = !notificationsMenu.open;
            });
        }

        if (userTrigger && userMenu) {
            userMenu.anchorElement = userTrigger;
            userTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                userMenu.open = !userMenu.open;
            });
        }

        // Logout functionality
        const logoutMenuItem = document.getElementById('logout-menu-item');
        const logoutForm = document.getElementById('logout-form');
        const logoutMobile = document.getElementById('logout-mobile');

        if (logoutMenuItem && logoutForm) {
            logoutMenuItem.addEventListener('click', function() {
                logoutForm.submit();
            });
        }

        if (logoutMobile && logoutForm) {
            logoutMobile.addEventListener('click', function(e) {
                e.preventDefault();
                logoutForm.submit();
            });
        }

        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const themeToggleMobile = document.getElementById('theme-toggle-mobile');
        const themeIcon = document.getElementById('theme-icon');
        const themeIconMobile = document.getElementById('theme-icon-mobile');
        const savedTheme = localStorage.getItem('theme') || 'light';

        function updateIcons(theme) {
            const icon = theme === 'dark' ? 'light_mode' : 'dark_mode';
            if (themeIcon) themeIcon.textContent = icon;
            if (themeIconMobile) themeIconMobile.textContent = icon;
        }

        // Set initial icon state
        updateIcons(savedTheme);

        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            updateIcons(theme);
        }

        function toggleTheme(e) {
            e.preventDefault();
            const currentTheme = localStorage.getItem('theme') || 'light';
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        }

        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        if (themeToggleMobile) {
            themeToggleMobile.addEventListener('click', toggleTheme);
        }

        // Handle notification clicks
        document.querySelectorAll('.notification-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                const notificationId = this.dataset.id;

                fetch('/notifications/' + notificationId + '/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).then(function(response) {
                    if (!response.ok) {
                        console.error('Error marcando notificación como leída:', response.status);
                        return;
                    }
                    item.remove();
                    const badge = document.querySelector('.badge.red');
                    if (badge) {
                        const count = parseInt(badge.textContent) - 1;
                        if (count <= 0) {
                            badge.remove();
                        } else {
                            badge.textContent = count;
                        }
                    }
                }).catch(function(error) {
                    console.error('Error de red:', error);
                });
            });
        });
    });
    </script>

    <main>
        <div class="container" style="padding-top: 20px;">
            @if(session('success'))
                <div class="row">
                    <div class="col s12">
                        <div class="card-panel card-panel-success">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="row">
                    <div class="col s12">
                        <div class="card-panel card-panel-error">
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="row">
                    <div class="col s12">
                        <div class="card-panel card-panel-error">
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

    @yield('scripts')
</body>
</html>
