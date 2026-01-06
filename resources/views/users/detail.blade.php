@extends('layouts.app')

@section('title', 'Mi Información')

@section('content')
<style>
    .switch label input[type=checkbox]:checked + .lever {
        background-color: #f48fb1;
    }
    .switch label input[type=checkbox]:checked + .lever:after {
        background-color: #c2185b;
    }
</style>
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="card-title" style="margin: 0;">
                        <i class="material-icons left">person</i>
                        Información Personal
                    </span>
                    <div class="switch" style="display: flex; align-items: center;">
                        <label>
                            <input type="checkbox" id="show-full-info">
                            <span class="lever"></span>
                        </label>
                        <i class="material-icons grey-text" id="visibility-icon" style="margin-left: 8px;">visibility_off</i>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="row">
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">Nombre</h6>
                        <p>{{ Str::ucfirst($user->name()) }}</p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">Email</h6>
                        <p>
                            <span class="masked-info">{{ Str::mask($user->email()->getValue(), '*', 3, strpos($user->email()->getValue(), '@') - 3) }}</span>
                            <span class="full-info" style="display: none;">{{ $user->email()->getValue() }}</span>
                        </p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">UUID</h6>
                        <p>
                            <code class="grey-text masked-info">{{ Str::limit($user->uuid()->getValue(), 18) }}</code>
                            <code class="grey-text full-info" style="display: none;">{{ $user->uuid()->getValue() }}</code>
                        </p>
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

<!-- Todos los Fichajes -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                @php
                    $totalSegundosHoy = collect($allRegistros)->sum(fn($r) => $r->segundosTrabajados());
                    $tieneAbiertoHoy = collect($allRegistros)->contains(fn($r) => $r->isAbierto());
                    $registroAbierto = collect($allRegistros)->first(fn($r) => $r->isAbierto());
                @endphp
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <span class="card-title" style="margin: 0;">
                        <i class="material-icons left">assignment</i>
                        Fichaje de hoy
                    </span>
                    @if(isset($allRegistros) && count($allRegistros) > 0)
                        @if($tieneAbiertoHoy)
                            <span class="chip amber lighten-4 amber-text text-darken-2 live-timer-total"
                                  style="margin: 0; font-size: 1.1rem; font-weight: 600;"
                                  data-base-seconds="{{ $totalSegundosHoy }}"
                                  data-start-time="{{ $registroAbierto->entrada()->getTimestamp() }}">
                                {{ gmdate('H:i:s', $totalSegundosHoy) }}
                            </span>
                        @else
                            <span class="chip blue lighten-4 blue-text text-darken-2" style="margin: 0; font-size: 1.1rem; font-weight: 600;">
                                {{ gmdate('H:i:s', $totalSegundosHoy) }}
                            </span>
                        @endif
                    @endif
                </div>

                @if(isset($allRegistros) && count($allRegistros) > 0)
                    <p class="grey-text" style="margin-top: 10px;">Total de {{ count($allRegistros) }} {{ count($allRegistros) == 1 ? 'registro' : 'registros' }}</p>

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
                                        <span class="chip amber lighten-4 amber-text text-darken-2 live-timer"
                                              data-start-time="{{ $registro->entrada()->getTimestamp() }}">
                                            {{ gmdate('H:i:s', $registro->segundosTrabajados()) }}
                                        </span>
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
                                            <button type="submit" class="btn-small waves-effect waves-light pink darken-2">
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
                                @if(!empty($dia['tiene_abierto']))
                                    <span class="chip amber lighten-4 amber-text text-darken-2 live-timer-total"
                                          style="min-width: 90px; text-align: center;"
                                          data-base-seconds="{{ $dia['total_segundos'] }}"
                                          data-start-time="{{ collect($dia['registros'])->firstWhere('abierto', true)['entrada_timestamp'] ?? 0 }}">
                                        {{ $dia['total_formateado'] }}
                                    </span>
                                @else
                                    <span class="chip blue lighten-4 blue-text text-darken-2" style="min-width: 90px; text-align: center;">
                                        {{ $dia['total_formateado'] }}
                                    </span>
                                @endif
                                <span class="chip grey lighten-2 grey-text text-darken-2" style="min-width: 90px; text-align: center;">
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
                                                @if(!empty($registro['abierto']))
                                                    <span class="amber-text text-darken-2">
                                                        <i class="material-icons tiny">schedule</i> Abierto
                                                    </span>
                                                @else
                                                    <i class="material-icons tiny red-text text-lighten-1">logout</i>
                                                    {{ $registro['salida'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($registro['abierto']))
                                                    <span class="chip amber lighten-4 amber-text text-darken-2 live-timer"
                                                          data-start-time="{{ $registro['entrada_timestamp'] }}">
                                                        <i class="material-icons tiny">timer</i>
                                                        {{ $registro['duracion'] }}
                                                    </span>
                                                @else
                                                    <span class="chip green lighten-4 green-text text-darken-2">
                                                        <i class="material-icons tiny">timer</i>
                                                        {{ $registro['duracion'] }}
                                                    </span>
                                                @endif
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

<!-- Resumen Mensual -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">date_range</i>
                    Resumen Mensual
                    @if($tieneAbiertoHoy)
                        <i class="material-icons amber-text text-darken-2" style="font-size: 20px; vertical-align: middle;" title="Fichaje abierto">warning</i>
                    @endif
                </span>

                @if(isset($monthlyRegistros) && count($monthlyRegistros) > 0)
                    <ul class="collapsible" id="monthly-collapsible">
                        <li>
                            <div class="collapsible-header">
                                <i class="material-icons">event_note</i>
                                <span style="flex: 1;">{{ $totalMes['mes'] }}</span>
                                <span class="chip blue lighten-4 blue-text text-darken-2" style="min-width: 90px; text-align: center;">
                                    {{ $totalMes['formateado'] }}
                                </span>
                                <span class="chip grey lighten-2 grey-text text-darken-2" style="min-width: 90px; text-align: center;">
                                    {{ count($monthlyRegistros) }} {{ count($monthlyRegistros) == 1 ? 'fichaje' : 'fichajes' }}
                                </span>
                            </div>
                            <div class="collapsible-body">
                                <table class="striped responsive-table highlight">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Entrada</th>
                                            <th>Salida</th>
                                            <th>Duración</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(collect($monthlyRegistros)->sortByDesc(function($registro) { return $registro->entrada(); }) as $registro)
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
                                                    <span class="chip amber lighten-4 amber-text text-darken-2 live-timer"
                                                          data-start-time="{{ $registro->entrada()->getTimestamp() }}">
                                                        {{ gmdate('H:i:s', $registro->segundosTrabajados()) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registro->isAbierto())
                                                    <span class="chip amber lighten-4 amber-text text-darken-2">Abierto</span>
                                                @else
                                                    <span class="chip green lighten-4 green-text text-darken-2">Cerrado</span>
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
                    <div class="center-align" style="padding: 60px 20px;">
                        <i class="material-icons grey-text" style="font-size: 72px;">event_busy</i>
                        <h5 class="grey-text text-darken-1">Sin fichajes este mes</h5>
                        <p class="grey-text">Aún no tienes registros de fichajes este mes.</p>
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

    // Toggle show/hide full info
    var toggle = document.getElementById('show-full-info');
    var icon = document.getElementById('visibility-icon');
    toggle.addEventListener('change', function() {
        var masked = document.querySelectorAll('.masked-info');
        var full = document.querySelectorAll('.full-info');

        if (this.checked) {
            masked.forEach(function(el) { el.style.display = 'none'; });
            full.forEach(function(el) { el.style.display = 'inline'; });
            icon.textContent = 'visibility';
            icon.classList.remove('grey-text');
            icon.classList.add('pink-text', 'text-darken-2');
        } else {
            masked.forEach(function(el) { el.style.display = 'inline'; });
            full.forEach(function(el) { el.style.display = 'none'; });
            icon.textContent = 'visibility_off';
            icon.classList.remove('pink-text', 'text-darken-2');
            icon.classList.add('grey-text');
        }
    });
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

// Formatear segundos a HH:MM:SS
function formatTime(seconds) {
    var h = Math.floor(seconds / 3600);
    var m = Math.floor((seconds % 3600) / 60);
    var s = seconds % 60;
    return String(h).padStart(2, '0') + ':' +
           String(m).padStart(2, '0') + ':' +
           String(s).padStart(2, '0');
}

// Live timer para fichajes abiertos individuales
function updateLiveTimers() {
    document.querySelectorAll('.live-timer').forEach(function(el) {
        var startTime = parseInt(el.getAttribute('data-start-time'));
        var now = Math.floor(Date.now() / 1000);
        var seconds = now - startTime;

        // Preservar icono si existe
        var icon = el.querySelector('i');
        if (icon) {
            el.innerHTML = '';
            el.appendChild(icon);
            el.appendChild(document.createTextNode(' ' + formatTime(seconds)));
        } else {
            el.textContent = formatTime(seconds);
        }
    });

    // Live timer para totales del día con fichaje abierto
    document.querySelectorAll('.live-timer-total').forEach(function(el) {
        var startTime = parseInt(el.getAttribute('data-start-time'));
        var baseSeconds = parseInt(el.getAttribute('data-base-seconds')) || 0;
        var now = Math.floor(Date.now() / 1000);

        // El total es: tiempo base (fichajes cerrados) + tiempo actual del abierto
        // Pero baseSeconds ya incluye el tiempo del abierto al momento de cargar
        // Así que solo necesitamos calcular la diferencia desde ese momento
        var secondsFromOpen = now - startTime;
        var initialSecondsFromOpen = baseSeconds - (baseSeconds > 0 ? (el.dataset.initialOpen || 0) : 0);

        // Recalcular: base cerrados + tiempo actual abierto
        if (!el.dataset.baseClosed) {
            // Guardar la base de cerrados (total - tiempo abierto al cargar)
            var openAtLoad = now - startTime;
            el.dataset.baseClosed = baseSeconds - openAtLoad;
        }
        var totalSeconds = parseInt(el.dataset.baseClosed) + secondsFromOpen;

        el.textContent = formatTime(totalSeconds);
    });
}

// Actualizar cada segundo si hay timers activos
if (document.querySelector('.live-timer') || document.querySelector('.live-timer-total')) {
    setInterval(updateLiveTimers, 1000);
    updateLiveTimers();
}
</script>

@endsection
