@extends('layouts.app')

@section('title', 'Registro Horario')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-3xl font-semibold text-gray-900">Registro Horario</h1>
            <p class="mt-2 text-sm text-gray-700">Ficha de entrada y salida.</p>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Usuario</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->name() }} ({{ $user->email()->getValue() }})</dd>
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

                <div class="mt-8">
                    <form method="POST" action="{{ route('registro_horario.entrada') }}">
                        @csrf
                        <button
                            type="submit"
                            @if($tieneRegistroAbierto) disabled @endif
                            class="rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 @if($tieneRegistroAbierto) bg-gray-400 cursor-not-allowed @else bg-green-600 hover:bg-green-500 @endif"
                        >
                            Fichar Entrada
                        </button>
                    </form>
                    @if($tieneRegistroAbierto)
                        <p class="mt-3 text-sm text-gray-600">
                            Tienes un fichaje abierto. Puedes cerrarlo desde <a href="{{ route('users.index') }}" class="text-blue-600 hover:text-blue-500 font-medium">tu página de fichajes</a>.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
