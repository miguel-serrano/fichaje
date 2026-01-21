@extends('layouts.app')

@section('title', 'Mi Información')

@section('content')
<style>
    .visibility-switch .lever {
        background-color: var(--bg-secondary) !important;
    }
    .visibility-switch input:checked + .lever {
        background-color: var(--claude-primary-light) !important;
    }
    .visibility-switch input:checked + .lever:after {
        background-color: var(--claude-primary) !important;
    }
    /* Responsive para collapsible headers en móvil */
    @media only screen and (max-width: 600px) {
        .collapsible-header {
            flex-wrap: wrap;
            padding: 10px 15px;
        }
        .collapsible-header > span:first-of-type {
            width: 100%;
            margin-bottom: 8px;
        }
        .collapsible-header .chip {
            margin: 2px 4px 2px 0;
            font-size: 0.85rem;
        }
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
                    <div class="switch visibility-switch" style="display: flex; align-items: center;">
                        <label>
                            <input type="checkbox" id="show-full-info">
                            <span class="lever"></span>
                        </label>
                        <i class="material-icons" id="visibility-icon" style="margin-left: 8px; color: var(--text-secondary);">visibility_off</i>
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
                            <span class="masked-info">{{ Str::mask($user->email()->value(), '*', 3, strpos($user->email()->value(), '@') - 3) }}</span>
                            <span class="full-info" style="display: none;">{{ $user->email()->value() }}</span>
                        </p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">UUID</h6>
                        <p>
                            <code class="grey-text masked-info">{{ Str::limit($user->uuid()->value(), 18) }}</code>
                            <code class="grey-text full-info" style="display: none;">{{ $user->uuid()->value() }}</code>
                        </p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text text-darken-1">Estado</h6>
                        <p>
                            @if($user->isActive())
                                <span class="chip chip-success">
                                    <i class="material-icons tiny">check_circle</i> Activo
                                </span>
                            @else
                                <span class="chip chip-error">
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
        <div class="card" style="background: var(--warning-bg) !important;">
            <div class="card-content" style="color: var(--warning) !important;">
                <div class="row valign-wrapper" style="margin-bottom: 0;">
                    <div class="col s12 m1 center-align">
                        <i class="material-icons" style="font-size: 48px; color: var(--warning);">warning</i>
                    </div>
                    <div class="col s12 m11">
                        <h5 style="margin-top: 0; color: var(--warning);">Cuenta Inactiva</h5>
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
                    $totalSegundosHoy = collect($allRegistros)->sum(fn($r) => $r->workedSeconds());
                    $tieneAbiertoHoy = collect($allRegistros)->contains(fn($r) => $r->isOpen());
                    $registroAbierto = collect($allRegistros)->first(fn($r) => $r->isOpen());
                @endphp
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <span class="card-title" style="margin: 0;">
                        <i class="material-icons left">assignment</i>
                        Fichaje de hoy
                    </span>
                    @if(isset($allRegistros) && count($allRegistros) > 0)
                        @if($tieneAbiertoHoy)
                            <span class="chip chip-warning live-timer-total"
                                  style="margin: 0; font-size: 1.1rem; font-weight: 600;"
                                  data-base-seconds="{{ $totalSegundosHoy }}"
                                  data-start-time="{{ $registroAbierto->startTime() }}">
                                {{ gmdate('H:i:s', $totalSegundosHoy) }}
                            </span>
                        @else
                            <span class="chip chip-info" style="margin: 0; font-size: 1.1rem; font-weight: 600;">
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
                            @foreach(collect($allRegistros)->sortByDesc(function($registro) { return $registro->startTime(); }) as $registro)
                            <tr>
                                <td>{{ $registro->startTimeFormatted('d/m/Y') }}</td>
                                <td>{{ $registro->startTimeFormatted('H:i:s') }}</td>
                                <td>
                                    @if($registro->endTime())
                                        {{ $registro->endTimeFormatted('H:i:s') }}
                                    @else
                                        <span style="color: var(--warning);">
                                            <i class="material-icons tiny">schedule</i> Abierto
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($registro->endTime())
                                        <span class="chip chip-info">
                                            {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                        </span>
                                    @else
                                        <span class="chip chip-warning live-timer"
                                              data-start-time="{{ $registro->startTime() }}">
                                            {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($registro->isOpen())
                                        <span class="chip chip-warning">Abierto</span>
                                    @else
                                        <span class="chip chip-success">Cerrado</span>
                                    @endif
                                </td>
                                <td class="right-align">
                                    @if($registro->isOpen())
                                        <form action="{{ route('registro_horario.salida', ['registroHorarioId' => $registro->id()->value()]) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="userUuid" value="{{ $user->uuid()->value() }}">
                                            <button type="submit" class="btn-small waves-effect waves-light btn-claude">
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
                <span class="card-title">
                    <i class="material-icons left">calendar_today</i>
                    Resumen Diario
                </span>
                <p class="grey-text">Fichajes cerrados agrupados por día</p>
                <div style="margin: 15px 0; display: flex; gap: 8px;">
                    <button onclick="expandAll()" class="btn-small waves-effect waves-light btn-claude">
                        <i class="material-icons tiny hide-on-med-and-up">unfold_more</i>
                        <span class="hide-on-small-only"><i class="material-icons left">unfold_more</i>Expandir</span>
                        <span class="hide-on-med-and-up">Exp</span>
                    </button>
                    <button onclick="collapseAll()" class="btn-small waves-effect waves-light btn-claude">
                        <i class="material-icons tiny hide-on-med-and-up">unfold_less</i>
                        <span class="hide-on-small-only"><i class="material-icons left">unfold_less</i>Colapsar</span>
                        <span class="hide-on-med-and-up">Col</span>
                    </button>
                </div>

                @if(count($dailyRegistros) > 0)
                    <ul class="collapsible" id="daily-collapsible">
                        @foreach($dailyRegistros as $index => $dia)
                        <li>
                            <div class="collapsible-header">
                                <i class="material-icons">date_range</i>
                                <span style="flex: 1;">{{ $dia['fecha_formateada'] }}</span>
                                @if(!empty($dia['tiene_abierto']))
                                    <span class="chip chip-warning live-timer-total"
                                          style="min-width: 90px; text-align: center;"
                                          data-base-seconds="{{ $dia['total_segundos'] }}"
                                          data-start-time="{{ collect($dia['registros'])->firstWhere('abierto', true)['entrada_timestamp'] ?? 0 }}">
                                        {{ $dia['total_formateado'] }}
                                    </span>
                                @else
                                    <span class="chip chip-info" style="min-width: 90px; text-align: center;">
                                        {{ $dia['total_formateado'] }}
                                    </span>
                                @endif
                                <span class="chip chip-neutral" style="min-width: 90px; text-align: center;">
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
                                                <i class="material-icons tiny text-claude">login</i>
                                                {{ $registro['entrada'] }}
                                            </td>
                                            <td>
                                                @if(!empty($registro['abierto']))
                                                    <span style="color: var(--warning);">
                                                        <i class="material-icons tiny">schedule</i> Abierto
                                                    </span>
                                                @else
                                                    <i class="material-icons tiny" style="color: var(--error);">logout</i>
                                                    {{ $registro['salida'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($registro['abierto']))
                                                    <span class="chip chip-warning live-timer"
                                                          data-start-time="{{ $registro['entrada_timestamp'] }}">
                                                        <i class="material-icons tiny">timer</i>
                                                        {{ $registro['duracion'] }}
                                                    </span>
                                                @else
                                                    <span class="chip chip-success">
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

<!-- Balance de Horas -->
@if(isset($totalMes) && count($dailyRegistros) > 0)
@php
    $diasFichados = count($dailyRegistros);
    $segundosEsperados = $diasFichados * 8 * 3600;
    $balanceSegundos = $totalMes['segundos'] - $segundosEsperados;
    $esPositivo = $balanceSegundos >= 0;
    $balanceFormateado = ($esPositivo ? '+' : '-') . gmdate('H:i:s', abs($balanceSegundos));
    $esperadoFormateado = gmdate('H:i:s', $segundosEsperados);
@endphp
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">balance</i>
                    Balance de Horas - {{ $totalMes['mes'] }}
                </span>

                <div class="row" style="margin-top: 20px; margin-bottom: 0;">
                    <div class="col s12 m4 center-align" style="margin-bottom: 15px;">
                        <h6 class="grey-text" style="margin-bottom: 10px;">Trabajadas</h6>
                        <span class="chip chip-info" style="font-size: 1.2rem; font-weight: 600;">
                            {{ $totalMes['formateado'] }}
                        </span>
                    </div>
                    <div class="col s12 m4 center-align" style="margin-bottom: 15px;">
                        <h6 class="grey-text" style="margin-bottom: 10px;">Esperadas</h6>
                        <span class="chip chip-neutral" style="font-size: 1.2rem; font-weight: 600;">
                            {{ $esperadoFormateado }}
                        </span>
                        <p class="grey-text" style="margin: 5px 0 0 0; font-size: 0.9rem;">{{ $diasFichados }} {{ $diasFichados == 1 ? 'día' : 'días' }} × 8h</p>
                    </div>
                    <div class="col s12 m4 center-align" style="margin-bottom: 15px;">
                        <h6 class="grey-text" style="margin-bottom: 10px;">Balance</h6>
                        @if($esPositivo)
                            <span class="chip chip-success" style="font-size: 1.2rem; font-weight: 600;">
                                <i class="material-icons tiny">trending_up</i> {{ $balanceFormateado }}
                            </span>
                        @else
                            <span class="chip chip-error" style="font-size: 1.2rem; font-weight: 600;">
                                <i class="material-icons tiny">trending_down</i> {{ $balanceFormateado }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Resumen Mensual -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">date_range</i>
                    Resumen Mensual
                    @if($tieneAbiertoHoy)
                        <i class="material-icons" style="font-size: 20px; vertical-align: middle; color: var(--warning);" title="Fichaje abierto">warning</i>
                    @endif
                </span>

                @if(isset($monthlyRegistros) && count($monthlyRegistros) > 0)
                    <ul class="collapsible" id="monthly-collapsible">
                        <li>
                            <div class="collapsible-header">
                                <i class="material-icons">event_note</i>
                                <span style="flex: 1;">{{ $totalMes['mes'] }}</span>
                                <span class="chip chip-info" style="min-width: 90px; text-align: center;">
                                    {{ $totalMes['formateado'] }}
                                </span>
                                <span class="chip chip-neutral" style="min-width: 90px; text-align: center;">
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
                                        @foreach(collect($monthlyRegistros)->sortByDesc(function($registro) { return $registro->startTime(); }) as $registro)
                                        <tr>
                                            <td>{{ $registro->startTimeFormatted('d/m/Y') }}</td>
                                            <td>{{ $registro->startTimeFormatted('H:i:s') }}</td>
                                            <td>
                                                @if($registro->endTime())
                                                    {{ $registro->endTimeFormatted('H:i:s') }}
                                                @else
                                                    <span style="color: var(--warning);">
                                                        <i class="material-icons tiny">schedule</i> Abierto
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registro->endTime())
                                                    <span class="chip chip-info">
                                                        {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                                    </span>
                                                @else
                                                    <span class="chip chip-warning live-timer"
                                                          data-start-time="{{ $registro->startTime() }}">
                                                        {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registro->isOpen())
                                                    <span class="chip chip-warning">Abierto</span>
                                                @else
                                                    <span class="chip chip-success">Cerrado</span>
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
            icon.style.color = 'var(--claude-primary)';
        } else {
            masked.forEach(function(el) { el.style.display = 'inline'; });
            full.forEach(function(el) { el.style.display = 'none'; });
            icon.textContent = 'visibility_off';
            icon.style.color = 'var(--text-secondary)';
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
