<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Gestión de Horarios') - Laravel DDD</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased" style="background-color: #E0E1DD;">
    <nav class="shadow-lg" style="background-color: #778DA9;">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ auth()->check() ? route('registro_horario.index') : route('login') }}" class="flex items-center space-x-2">
                            <svg class="w-8 h-8" style="color: #FFFFFF;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="text-xl font-bold" style="color: #FFFFFF;">TimeTrack</span>
                        </a>
                    </div>
                    @auth
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'border-white' : 'border-transparent hover:border-white/50' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" style="color: #FFFFFF;">
                            Información
                        </a>
                        <a href="{{ route('registro_horario.index') }}" class="{{ request()->routeIs('registro_horario.*') ? 'border-white' : 'border-transparent hover:border-white/50' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" style="color: #FFFFFF;">
                            Fichaje
                        </a>
                    </div>
                    @endauth
                </div>
                @auth
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium" style="color: #FFFFFF;">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium hover:opacity-70" style="color: #FFFFFF;">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-white border border-gray-300 text-black px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-white border border-gray-300 text-black px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-white border border-gray-300 text-black px-4 py-3 rounded relative" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>

