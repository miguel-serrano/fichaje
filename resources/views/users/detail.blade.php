@extends('layouts.app')

@section('title', 'Mi Información')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">person</i>
                    Información Personal
                </span>

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="row">
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">Nombre</h6>
                        <p>{{ $user->name() }}</p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">Email</h6>
                        <p>{{ $user->email()->getValue() }}</p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">UUID</h6>
                        <p><code class="grey-text">{{ $user->uuid()->getValue() }}</code></p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">Estado</h6>
                        <p>
                            @if($user->isActive())
                                <span class="chip green lighten-4 green-text text-darken-2">
                                    <i class="material-icons tiny">check_circle</i> Activo
                                </span>
                            @else
                                <span class="chip red lighten-4 red-text text-darken-2">
                                    <i class="material-icons tiny">cancel</i> Inactivo
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Aviso de cuenta inactiva -->
@if(!$user->isActive())
<div class="row">
    <div class="col s12">
        <div class="card amber lighten-4">
            <div class="card-content amber-text text-darken-4">
                <div class="row valign-wrapper" style="margin-bottom: 0;">
                    <div class="col s12 m1 center-align">
                        <i class="material-icons amber-text text-darken-2" style="font-size: 48px;">warning</i>
                    </div>
                    <div class="col s12 m11">
                        <h5 class="amber-text text-darken-4" style="margin-top: 0;">Cuenta Inactiva</h5>
                        <p>
                            Tu cuenta está pendiente de activación. No podrás fichar entrada ni salida hasta que un administrador active tu cuenta.
                            Por favor, contacta con un administrador.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Resumen Mensual -->
@if(isset($totalMes) && $totalMes['segundos'] > 0)
<div class="row">
    <div class="col s12">
        <div class="card light-green lighten-5">
            <div class="card-content">
                <div class="row valign-wrapper" style="margin-bottom: 0;">
                    <div class="col s12 m6">
                        <i class="material-icons left light-green-text">event</i>
                        <span class="card-title">Total {{ $totalMes['mes'] }}</span>
                    </div>
                    <div class="col s12 m6 right-align">
                        <h4 class="light-green-text text-darken-2" style="margin: 0;">{{ $totalMes['formateado'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Todos los Fichajes -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">assignment</i>
                    Todos los Fichajes
                </span>

                @if(isset($allRegistros) && count($allRegistros) > 0)
                    <p class="grey-text">Total de {{ count($allRegistros) }} {{ count($allRegistros) == 1 ? 'registro' : 'registros' }}</p>

                    <table class="striped responsive-table highlight">
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
                                <td>{{ $registro->entrada()->format('d/m/Y') }}</td>
                                <td>{{ $registro->entrada()->format('H:i:s') }}</td>
                                <td>
                                    @if($registro->salida())
                                        {{ $registro->salida()->format('H:i:s') }}
                                    @else
                                        <span class="amber-text text-darken-2">
                                            <i class="material-icons tiny">schedule</i> Abierto
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($registro->salida())
                                        <span class="chip blue lighten-4 blue-text text-darken-2">
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
                                        <span class="chip green lighten-4 green-text text-darken-2">Cerrado</span>
                                    @endif
                                </td>
                                <td class="right-align">
                                    @if($registro->isAbierto())
                                        <form action="{{ route('registro_horario.salida', ['registroHorarioId' => $registro->id()->getValue()]) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="userUuid" value="{{ $user->uuid()->getValue() }}">
                                            <button type="submit" class="btn-small waves-effect waves-light light-green">
                                                <i class="material-icons left">check</i>Cerrar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="center-align" style="padding: 60px 20px;">
                        <i class="material-icons grey-text" style="font-size: 72px;">assignment_late</i>
                        <h5 class="grey-text text-darken-1">Sin registros de fichaje</h5>
                        <p class="grey-text">Aún no tienes ningún registro de fichaje.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Resumen Diario -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 10px;">
                    <div class="col s12 m6">
                        <span class="card-title">
                            <i class="material-icons left">calendar_today</i>
                            Resumen Diario
                        </span>
                        <p class="grey-text">Fichajes cerrados agrupados por día</p>
                    </div>
                    <div class="col s12 m6 right-align">
                        <button onclick="expandAll()" class="btn-small waves-effect waves-light light-green">
                            <i class="material-icons left">unfold_more</i>Expandir
                        </button>
                        <button onclick="collapseAll()" class="btn-small waves-effect waves-light light-green">
                            <i class="material-icons left">unfold_less</i>Colapsar
                        </button>
                    </div>
                </div>

                @if(count($dailyRegistros) > 0)
                    <ul class="collapsible" id="daily-collapsible">
                        @foreach($dailyRegistros as $index => $dia)
                        <li>
                            <div class="collapsible-header">
                                <i class="material-icons">date_range</i>
                                <span style="flex: 1;">{{ $dia['fecha_formateada'] }}</span>
                                <span class="chip blue lighten-4 blue-text text-darken-2">
                                    {{ $dia['total_formateado'] }}
                                </span>
                                <span class="badge grey lighten-2 grey-text text-darken-2">
                                    {{ count($dia['registros']) }} {{ count($dia['registros']) == 1 ? 'fichaje' : 'fichajes' }}
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
                                            <td>
                                                <i class="material-icons tiny light-green-text">login</i>
                                                {{ $registro['entrada'] }}
                                            </td>
                                            <td>
                                                <i class="material-icons tiny red-text text-lighten-1">logout</i>
                                                {{ $registro['salida'] }}
                                            </td>
                                            <td>
                                                <span class="chip green lighten-4 green-text text-darken-2">
                                                    <i class="material-icons tiny">timer</i>
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
                    <div class="center-align" style="padding: 60px 20px;">
                        <i class="material-icons grey-text" style="font-size: 72px;">event_busy</i>
                        <h5 class="grey-text text-darken-1">Sin fichajes cerrados</h5>
                        <p class="grey-text">Aún no tienes registros de fichajes completados para mostrar en el resumen diario.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize collapsibles with accordion disabled (allows multiple open)
    var elems = document.querySelectorAll('.collapsible');
    M.Collapsible.init(elems, { accordion: false });
});

function expandAll() {
    var elem = document.getElementById('daily-collapsible');
    var instance = M.Collapsible.getInstance(elem);
    if (instance) {
        var items = elem.querySelectorAll('li');
        for (let i = 0; i < items.length; i++) {
            instance.open(i);
        }
    }
}

function collapseAll() {
    var elem = document.getElementById('daily-collapsible');
    var instance = M.Collapsible.getInstance(elem);
    if (instance) {
        var items = elem.querySelectorAll('li');
        for (let i = 0; i < items.length; i++) {
            instance.close(i);
        }
    }
}
</script>

@endsection
