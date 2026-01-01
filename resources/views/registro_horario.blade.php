@extends('layouts.app')

@section('title', 'Registro Horario')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-3xl font-semibold text-gray-900">Registro Horario</h1>
            <p class="mt-2 text-sm text-gray-700">Ficha de entrada y salida del usuario.</p>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <form id="tokenUserForm" action="{{ route('registro_horario.index') }}" method="GET" class="p-6 border-b border-gray-200">
                <div class="space-y-6">
                    @if(!App::environment('local'))
                    <div>
                        <label for="remember_token_code" class="block text-sm font-medium leading-6 text-gray-900">Código personal</label>
                        <div class="mt-2">
                            <input type="text" id="remember_token_code" name="remember_token_code" minlength="8" maxlength="255" autocomplete="off" placeholder="Introduce tu código" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                        </div>
                    </div>
                    @endif

                    <label for="userUuid" class="block text-sm font-medium leading-6 text-gray-900">Usuario</label>
                    <div class="mt-2">
                        <select 
                            name="userUuid" 
                            id="userUuid" 
                            onchange="this.form.submit()"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                            <option value="">Selecciona un usuario</option>
                            @if(App::environment('local'))
                                @foreach($users as $user)
                                    <option value="{{ $user['uuid'] }}" @if(isset($selectedUserUuid) && $selectedUserUuid == $user['uuid']) selected @endif>
                                        {{ $user['name'] }} ({{ $user['email'] }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </form>
            <script>
                window.isLocalEnv = {{ App::environment('local') ? 'true' : 'false' }};
            </script>
            <script src="/js/registro_horario_token.js"></script>

            @if($selectedUserUuid)
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">User UUID</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedUserUuid }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tiempo acumulado hoy</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">
                            <span class="inline-flex items-center rounded-md bg-blue-100 px-2.5 py-0.5 text-sm font-medium text-blue-800">
                                @php
                                    $horas = floor($segundos / 3600);
                                    $minutos = floor(($segundos % 3600) / 60);
                                    $segundosRestantes = $segundos % 60;
                                @endphp
                                {{ str_pad($horas, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutos, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($segundosRestantes, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </dd>
                    </div>
                </dl>

                <div class="mt-8 flex gap-4">
                    <form method="POST" action="{{ route('registro_horario.entrada') }}">
                        @csrf
                        <input type="hidden" name="userUuid" value="{{ $selectedUserUuid }}">
                        <button 
                            type="submit" 
                            @if($tieneRegistroAbierto) disabled @endif
                            class="rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 @if($tieneRegistroAbierto) bg-gray-400 cursor-not-allowed @else bg-green-600 hover:bg-green-500 @endif"
                        >
                            Fichar Entrada
                        </button>
                    </form>
                    <form method="POST" action="{{ route('registro_horario.salida') }}">
                        @csrf
                        <input type="hidden" name="userUuid" value="{{ $selectedUserUuid }}">
                        <button 
                            type="submit" 
                            @if(!$tieneRegistroAbierto) disabled @endif
                            class="rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 @if(!$tieneRegistroAbierto) bg-gray-400 cursor-not-allowed @else bg-red-600 hover:bg-red-500 @endif"
                        >
                            Fichar Salida
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="px-4 py-5 sm:p-6">
                <p class="text-sm text-gray-500">Por favor, selecciona un usuario para ver su registro de horario.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
