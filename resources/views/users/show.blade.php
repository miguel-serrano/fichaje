@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-3xl font-semibold text-gray-900">User Details</h1>
            <p class="mt-2 text-sm text-gray-700">View user information.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex gap-3">
            <a href="{{ route('users.index') }}" class="block rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                Back to Users
            </a>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $user->id()->getValue() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">UUID</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $user->uuid()->getValue() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($user->isActive())
                                <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800">Inactive</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->name() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email()->getValue() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Sección de Todos los Fichajes (All Time Entries) - Desplegable -->
    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <!-- Header clickeable para Todos los Fichajes -->
                    <button 
                        type="button" 
                        class="w-full px-4 py-4 bg-gray-50 hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
                        onclick="toggleAllFichajes()"
                    >
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-3">
                                <svg id="icon-all-fichajes" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Todos los Fichajes</h3>
                                @if(isset($allRegistros))
                                    <span class="text-sm text-gray-500">({{ count($allRegistros) }} {{ count($allRegistros) == 1 ? 'registro' : 'registros' }})</span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">Click para ver detalles</span>
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </button>
                    
                    <!-- Contenido colapsable de Todos los Fichajes -->
                    <div id="content-all-fichajes" class="hidden">
                        @if(isset($allRegistros) && count($allRegistros) > 0)
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">ID</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Fecha</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Entrada</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Salida</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Duración</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($allRegistros as $registro)
                                        <tr class="hover:bg-gray-50">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ $registro->id() ? $registro->id()->getValue() : 'N/A' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $registro->entrada()->format('Y-m-d') }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $registro->entrada()->format('H:i:s') }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if($registro->salida())
                                                    {{ $registro->salida()->format('H:i:s') }}
                                                @else
                                                    <span class="text-yellow-600 font-medium">Abierto</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if($registro->salida())
                                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                        {{ gmdate('H:i:s', $registro->segundosTrabajados()) }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">--</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if($registro->isAbierto())
                                                    <span class="inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800">Abierto</span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">Cerrado</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Sin registros de fichaje</h3>
                                <p class="mt-1 text-sm text-gray-500">Este usuario aún no tiene ningún registro de fichaje.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Fichajes por Día (resumenes diarios de fichajes cerrados) -->
    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-3 sm:space-y-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-2 sm:space-y-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Resumen Diario (Fichajes Cerrados)</h3>
                        @if(isset($totalMes) && $totalMes['segundos'] > 0)
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">Total {{ $totalMes['mes'] }}:</span>
                            <span class="inline-flex items-center rounded-md bg-green-50 px-3 py-1 text-sm font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
                                {{ $totalMes['formateado'] }}
                            </span>
                        </div>
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        <button 
                            type="button" 
                            onclick="toggleAll(true)"
                            class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            Expandir Todo
                        </button>
                        <button 
                            type="button" 
                            onclick="toggleAll(false)"
                            class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                            Colapsar Todo
                        </button>
                    </div>
                </div>
                
                <div class="space-y-3">
                    @if(count($dailyRegistros) > 0)
                        @foreach($dailyRegistros as $index => $dia)
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <!-- Header clickeable -->
                            <button 
                                type="button" 
                                class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
                                onclick="toggleDay('day-{{ $index }}')"
                            >
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <svg id="icon-day-{{ $index }}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                        <h4 class="text-md font-semibold text-gray-900">{{ $dia['fecha_formateada'] }}</h4>
                                        <span class="text-sm text-gray-500">({{ count($dia['registros']) }} {{ count($dia['registros']) == 1 ? 'fichaje' : 'fichajes' }})</span>
                                    </div>
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                        Total: {{ $dia['total_formateado'] }}
                                    </span>
                                </div>
                            </button>
                            
                            <!-- Contenido colapsable -->
                            <div id="content-day-{{ $index }}" class="hidden">
                                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5">
                                    <table class="min-w-full divide-y divide-gray-300">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">
                                                    Entrada
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">
                                                    Salida
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">
                                                    Duración
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($dia['registros'] as $registro)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $registro['entrada'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $registro['salida'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                        {{ $registro['duracion'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Sin fichajes cerrados</h3>
                            <p class="mt-1 text-sm text-gray-500">Este usuario aún no tiene registros de fichajes completados para mostrar en el resumen diario.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para toggle del desplegable de Todos los Fichajes
function toggleAllFichajes() {
    const content = document.getElementById('content-all-fichajes');
    const icon = document.getElementById('icon-all-fichajes');
    
    if (content.classList.contains('hidden')) {
        // Expandir
        content.classList.remove('hidden');
        icon.classList.add('rotate-90');
    } else {
        // Colapsar
        content.classList.add('hidden');
        icon.classList.remove('rotate-90');
    }
}

function toggleDay(dayId) {
    const content = document.getElementById('content-' + dayId);
    const icon = document.getElementById('icon-' + dayId);
    
    if (content.classList.contains('hidden')) {
        // Expandir
        content.classList.remove('hidden');
        icon.classList.add('rotate-90');
    } else {
        // Colapsar
        content.classList.add('hidden');
        icon.classList.remove('rotate-90');
    }
}

// Función para expandir/colapsar todos los días
function toggleAll(expand = true) {
    const allContents = document.querySelectorAll('[id^="content-day-"]');
    const allIcons = document.querySelectorAll('[id^="icon-day-"]');
    
    allContents.forEach(content => {
        if (expand) {
            content.classList.remove('hidden');
        } else {
            content.classList.add('hidden');
        }
    });
    
    allIcons.forEach(icon => {
        if (expand) {
            icon.classList.add('rotate-90');
        } else {
            icon.classList.remove('rotate-90');
        }
    });
}
</script>

@endsection