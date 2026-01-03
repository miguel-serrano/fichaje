@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12 m8">
                        <span class="card-title">User Details</span>
                        <p class="grey-text">View user information.</p>
                    </div>
                    <div class="col s12 m4 right-align">
                        <a href="{{ route('users.index') }}" class="btn waves-effect waves-light light-green darken-3">
                            <i class="material-icons left">arrow_back</i>Volver a Usuarios
                        </a>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;">
                    <div class="col s12 m6">
                        <p><strong class="grey-text text-darken-1">ID:</strong></p>
                        <p><code>{{ $user->id()->getValue() }}</code></p>
                    </div>
                    <div class="col s12 m6">
                        <p><strong class="grey-text text-darken-1">UUID:</strong></p>
                        <p><code>{{ $user->uuid()->getValue() }}</code></p>
                    </div>
                    <div class="col s12 m6">
                        <p><strong class="grey-text text-darken-1">Status:</strong></p>
                        <p>
                            @if($user->isActive())
                                <span class="chip green lighten-4 green-text text-darken-2">Active</span>
                            @else
                                <span class="chip red lighten-4 red-text text-darken-2">Inactive</span>
                            @endif
                        </p>
                    </div>
                    <div class="col s12 m6">
                        <p><strong class="grey-text text-darken-1">Name:</strong></p>
                        <p>{{ $user->name() }}</p>
                    </div>
                    <div class="col s12 m6">
                        <p><strong class="grey-text text-darken-1">Email:</strong></p>
                        <p>{{ $user->email()->getValue() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Todos los Fichajes -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Todos los Fichajes</span>
                @if(isset($allRegistros) && count($allRegistros) > 0)
                    <ul class="collapsible">
                        <li>
                            <div class="collapsible-header">
                                <i class="material-icons">assignment</i>
                                <span>Ver todos los registros</span>
                                <span class="badge grey lighten-2 grey-text text-darken-2">{{ count($allRegistros) }} {{ count($allRegistros) == 1 ? 'registro' : 'registros' }}</span>
                            </div>
                            <div class="collapsible-body">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Entrada</th>
                                            <th>Salida</th>
                                            <th>Duración</th>
                                            <th>Estado</th>
                                            <th class="right-align">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(collect($allRegistros)->sortByDesc(function($registro) { return $registro->entrada(); }) as $registro)
                                        <tr>
                                            <td>{{ $registro->entrada()->format('Y-m-d') }}</td>
                                            <td>{{ $registro->entrada()->format('H:i:s') }}</td>
                                            <td>
                                                @if($registro->salida())
                                                    {{ $registro->salida()->format('H:i:s') }}
                                                @else
                                                    <span class="amber-text text-darken-2"><strong>Abierto</strong></span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registro->salida())
                                                    <span class="chip green lighten-4 green-text text-darken-2">
                                                        {{ gmdate('H:i:s', $registro->segundosTrabajados()) }}
                                                    </span>
                                                @else
                                                    <span class="grey-text">--</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registro->isAbierto())
                                                    <span class="chip amber lighten-4 amber-text text-darken-2">Abierto</span>
                                                @else
                                                    <span class="chip blue lighten-4 blue-text text-darken-2">Cerrado</span>
                                                @endif
                                            </td>
                                            <td class="right-align">
                                                @if($registro->isAbierto())
                                                    <form action="{{ route('registro_horario.salida', ['registroHorarioId' => $registro->id()->getValue()]) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <input type="hidden" name="userUuid" value="{{ $user->uuid()->getValue() }}">
                                                        <button type="submit" class="btn-small waves-effect waves-light pink darken-3">
                                                            <i class="material-icons left">exit_to_app</i>Cerrar Fichaje
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </li>
                    </ul>
                @else
                    <div class="center-align" style="padding: 40px 20px;">
                        <i class="material-icons grey-text" style="font-size: 64px;">assignment_late</i>
                        <h5 class="grey-text text-darken-1">Sin registros de fichaje</h5>
                        <p class="grey-text">Este usuario aún no tiene ningún registro de fichaje.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Sección de Fichajes por Día -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 10px;">
                    <div class="col s12 m6">
                        <span class="card-title">Resumen Diario (Fichajes Cerrados)</span>
                    </div>
                    <div class="col s12 m6 right-align">
                        <button onclick="expandAll()" class="btn-small waves-effect waves-light indigo darken-4">
                            <i class="material-icons left">unfold_more</i>Expandir Todo
                        </button>
                        <button onclick="collapseAll()" class="btn-small waves-effect waves-light indigo darken-4">
                            <i class="material-icons left">unfold_less</i>Colapsar Todo
                        </button>
                    </div>
                </div>
                @if(isset($totalMes) && $totalMes['segundos'] > 0)
                <div class="row" style="margin-bottom: 10px;">
                    <div class="col s12" style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="grey-text">Total {{ $totalMes['mes'] }}:</span>
                        <span class="chip blue darken-3 white-text">{{ $totalMes['formateado'] }}</span>
                    </div>
                </div>
                @endif

                @if(count($dailyRegistros) > 0)
                    <ul class="collapsible" id="daily-collapsible">
                        @foreach($dailyRegistros as $index => $dia)
                        <li>
                            <div class="collapsible-header">
                                <i class="material-icons">calendar_today</i>
                                <span>{{ $dia['fecha_formateada'] }}</span>
                                <span class="badge grey lighten-2 grey-text text-darken-2">{{ count($dia['registros']) }} {{ count($dia['registros']) == 1 ? 'fichaje' : 'fichajes' }}</span>
                                <span class="chip blue lighten-4 blue-text text-darken-2">
                                    Total: {{ $dia['total_formateado'] }}
                                </span>
                            </div>
                            <div class="collapsible-body">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>Entrada</th>
                                            <th>Salida</th>
                                            <th>Duración</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dia['registros'] as $registro)
                                        <tr>
                                            <td>{{ $registro['entrada'] }}</td>
                                            <td>{{ $registro['salida'] }}</td>
                                            <td>
                                                <span class="chip green lighten-4 green-text text-darken-2">
                                                    {{ $registro['duracion'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="center-align" style="padding: 40px 20px;">
                        <i class="material-icons grey-text" style="font-size: 64px;">assignment</i>
                        <h5 class="grey-text text-darken-1">Sin fichajes cerrados</h5>
                        <p class="grey-text">Este usuario aún no tiene registros de fichajes completados para mostrar en el resumen diario.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize collapsibles
    var elems = document.querySelectorAll('.collapsible');
    M.Collapsible.init(elems);
});

function expandAll() {
    var instance = M.Collapsible.getInstance(document.getElementById('daily-collapsible'));
    if (instance) {
        for (let i = 0; i < instance.$el[0].children.length; i++) {
            instance.open(i);
        }
    }
}

function collapseAll() {
    var instance = M.Collapsible.getInstance(document.getElementById('daily-collapsible'));
    if (instance) {
        for (let i = 0; i < instance.$el[0].children.length; i++) {
            instance.close(i);
        }
    }
}
</script>

@endsection
