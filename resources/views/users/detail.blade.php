@extends('layouts.app')

@section('title', 'Mi Información')

@section('content')
<style>
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
    /* Personal info collapsible */
    .personal-info-details summary::-webkit-details-marker { display: none; }
    .personal-info-details summary { list-style: none; }
    .personal-info-details[open] .collapse-icon { transform: rotate(180deg); }
</style>

<!-- Información Personal (Collapsible) -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content" style="padding: 0;">
                <details class="personal-info-details">
                    <summary class="collapsible-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; cursor: pointer; list-style: none;">
                        <span class="card-title" style="margin: 0; display: flex; align-items: center;">
                            <md-icon style="margin-right: 8px;">person</md-icon>
                            Información Personal
                            <md-icon class="collapse-icon" style="margin-left: 8px; transition: transform 0.3s;">expand_more</md-icon>
                        </span>
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;" onclick="event.stopPropagation();">
                            <md-switch id="show-full-info"></md-switch>
                            <md-icon id="visibility-icon" style="color: var(--text-secondary);">visibility_off</md-icon>
                        </label>
                    </summary>
                    <div style="padding: 0 24px 20px;">
                        <div class="divider" style="margin-bottom: 20px;"></div>
                        <div class="row" style="margin-bottom: 0;">
                            <div class="col s12 m6">
                                <h6 class="text-secondary">Nombre</h6>
                                <p>{{ Str::ucfirst($user->name()) }}</p>
                            </div>
                            <div class="col s12 m6">
                                <h6 class="text-secondary">Email</h6>
                                <p>
                                    <span class="masked-info">{{ Str::mask($user->email()->value(), '*', 3, strpos($user->email()->value(), '@') - 3) }}</span>
                                    <span class="full-info" style="display: none;">{{ $user->email()->value() }}</span>
                                </p>
                            </div>
                            <div class="col s12 m6">
                                <h6 class="text-secondary">UUID</h6>
                                <p>
                                    <code class="text-secondary masked-info">{{ Str::limit($user->uuid()->value(), 18) }}</code>
                                    <code class="text-secondary full-info" style="display: none;">{{ $user->uuid()->value() }}</code>
                                </p>
                            </div>
                            <div class="col s12 m6">
                                <h6 class="text-secondary">Estado</h6>
                                <p>
                                    @if($user->isActive())
                                        <span class="status-badge status-badge-success">
                                            <md-icon style="font-size: 14px;">check_circle</md-icon> Activo
                                        </span>
                                    @else
                                        <span class="status-badge status-badge-error">
                                            <md-icon style="font-size: 14px;">cancel</md-icon> Inactivo
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>

<!-- Histórico de Fichajes - Gráfica (oculto en móviles) -->
<div class="row hide-on-small-only">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">show_chart</md-icon>
                    Histórico de Fichajes
                </span>

                @if(isset($chartData) && $chartData['hasData'])
                    <div style="position: relative; height: 300px; margin-top: 20px;">
                        <canvas id="dailyHoursChart"></canvas>
                    </div>
                    <p class="text-secondary center-align" style="margin-top: 10px; font-size: 0.9rem;">
                        Horas trabajadas por día (solo fichajes cerrados)
                    </p>
                @else
                    <div class="center-align" style="padding: 60px 20px;">
                        <md-icon class="text-secondary" style="font-size: 72px; width: 72px; height: 72px;">insert_chart_outlined</md-icon>
                        <h5 class="text-secondary">Sin datos para mostrar</h5>
                        <p class="text-secondary">No hay fichajes cerrados en los últimos 30 días.</p>
                    </div>
                @endif
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
                        <md-icon style="font-size: 48px; width: 48px; height: 48px; color: var(--warning);">warning</md-icon>
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
                        <md-icon style="margin-right: 8px;">schedule</md-icon>
                        Fichaje de hoy
                    </span>
                    @if(isset($allRegistros) && count($allRegistros) > 0)
                        @if($tieneAbiertoHoy)
                            <span class="status-badge status-badge-warning live-timer-total"
                                  style="margin: 0; font-size: 1.1rem; font-weight: 600;"
                                  data-base-seconds="{{ $totalSegundosHoy }}"
                                  data-start-time="{{ $registroAbierto->startTime() }}">
                                {{ gmdate('H:i:s', $totalSegundosHoy) }}
                            </span>
                        @else
                            <span class="status-badge status-badge-info" style="margin: 0; font-size: 1.1rem; font-weight: 600;">
                                {{ gmdate('H:i:s', $totalSegundosHoy) }}
                            </span>
                        @endif
                    @endif
                </div>

                @if(isset($allRegistros) && count($allRegistros) > 0)
                    <p class="text-secondary" style="margin-top: 10px;">Total de {{ count($allRegistros) }} {{ count($allRegistros) == 1 ? 'registro' : 'registros' }}</p>

                    {{-- Mobile view: Cards --}}
                    <div class="hide-on-med-and-up">
                        @foreach(collect($allRegistros)->sortByDesc(function($registro) { return $registro->startTime(); }) as $registro)
                        <div class="fichaje-card-mobile">
                            <div class="fichaje-card-header">
                                <span class="fichaje-card-date">{{ $registro->startTimeFormatted('d/m/Y') }}</span>
                                @if($registro->isOpen())
                                    <span class="status-badge status-badge-warning">Abierto</span>
                                @else
                                    <span class="status-badge status-badge-success">Cerrado</span>
                                @endif
                            </div>
                            <div class="fichaje-card-times">
                                <div class="fichaje-time-item">
                                    <span class="label">Entrada</span>
                                    <span class="value">{{ $registro->startTimeFormatted('H:i:s') }}</span>
                                </div>
                                <div class="fichaje-time-item">
                                    <span class="label">Salida</span>
                                    <span class="value">
                                        @if($registro->endTime())
                                            {{ $registro->endTimeFormatted('H:i:s') }}
                                        @else
                                            <span style="color: var(--warning);">--:--:--</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="fichaje-time-item">
                                    <span class="label">Duracion</span>
                                    @if($registro->endTime())
                                        <span class="status-badge status-badge-info" style="margin: 0;">
                                            {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                        </span>
                                    @else
                                        <span class="status-badge status-badge-warning live-timer" style="margin: 0;"
                                              data-start-time="{{ $registro->startTime() }}">
                                            {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($registro->isOpen())
                            <div class="fichaje-card-footer">
                                <form action="{{ route('registro_horario.salida') }}" method="POST" style="width: 100%;">
                                    @csrf
                                    <md-filled-tonal-button type="submit" style="width: 100%;">
                                        <md-icon slot="icon">check</md-icon>
                                        Cerrar fichaje
                                    </md-filled-tonal-button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    {{-- Desktop view: Table --}}
                    <div class="hide-on-small-only">
                        <table class="striped highlight">
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
                                                <md-icon style="font-size: 14px;">schedule</md-icon> Abierto
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($registro->endTime())
                                            <span class="status-badge status-badge-info">
                                                {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                            </span>
                                        @else
                                            <span class="status-badge status-badge-warning live-timer"
                                                  data-start-time="{{ $registro->startTime() }}">
                                                {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($registro->isOpen())
                                            <span class="status-badge status-badge-warning">Abierto</span>
                                        @else
                                            <span class="status-badge status-badge-success">Cerrado</span>
                                        @endif
                                    </td>
                                    <td class="right-align">
                                        @if($registro->isOpen())
                                            <form action="{{ route('registro_horario.salida') }}" method="POST" style="display: inline;">
                                                @csrf
                                                <md-filled-tonal-button type="submit">
                                                    <md-icon slot="icon">check</md-icon>
                                                    Cerrar
                                                </md-filled-tonal-button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="center-align" style="padding: 60px 20px;">
                        <md-icon class="text-secondary" style="font-size: 72px; width: 72px; height: 72px;">schedule</md-icon>
                        <h5 class="text-secondary">Sin registros de fichaje</h5>
                        <p class="text-secondary">Aún no tienes ningún registro de fichaje.</p>
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
                    <md-icon style="margin-right: 8px;">calendar_today</md-icon>
                    Resumen Diario
                </span>
                <p class="text-secondary">Fichajes cerrados agrupados por día</p>
                <div style="margin: 15px 0; display: flex; gap: 8px; flex-wrap: wrap;">
                    <md-filled-tonal-button onclick="expandAll()">
                        <md-icon slot="icon">unfold_more</md-icon>
                        <span class="hide-on-small-only">Expandir</span>
                    </md-filled-tonal-button>
                    <md-outlined-button onclick="collapseAll()">
                        <md-icon slot="icon">unfold_less</md-icon>
                        <span class="hide-on-small-only">Colapsar</span>
                    </md-outlined-button>
                </div>

                @if(count($dailyRegistros) > 0)
                    <ul class="collapsible" id="daily-collapsible">
                        @foreach($dailyRegistros as $index => $dia)
                        <li class="collapsible-item">
                            <details>
                                <summary class="collapsible-header">
                                    <md-icon>date_range</md-icon>
                                    <span style="flex: 1;">{{ $dia['fecha_formateada'] }}</span>
                                    @if(!empty($dia['tiene_abierto']))
                                        <span class="status-badge status-badge-warning live-timer-total"
                                              style="min-width: 90px; text-align: center;"
                                              data-base-seconds="{{ $dia['total_segundos'] }}"
                                              data-start-time="{{ collect($dia['registros'])->firstWhere('abierto', true)['entrada_timestamp'] ?? 0 }}">
                                            {{ $dia['total_formateado'] }}
                                        </span>
                                    @else
                                        <span class="status-badge status-badge-info" style="min-width: 90px; text-align: center;">
                                            {{ $dia['total_formateado'] }}
                                        </span>
                                    @endif
                                    <span class="status-badge status-badge-neutral" style="min-width: 90px; text-align: center;">
                                        {{ count($dia['registros']) }} {{ count($dia['registros']) == 1 ? 'fichaje' : 'fichajes' }}
                                    </span>
                                    <md-icon class="expand-icon">expand_more</md-icon>
                                </summary>
                                <div class="collapsible-content">
                                    {{-- Mobile view: List --}}
                                    <div class="hide-on-med-and-up">
                                        @foreach($dia['registros'] as $registro)
                                        <div class="fichaje-mini-card" style="{{ !empty($registro['abierto']) ? 'flex-wrap: wrap;' : '' }}">
                                            <div class="fichaje-mini-times">
                                                <div class="fichaje-mini-time">
                                                    <md-icon class="text-claude">login</md-icon>
                                                    <span>{{ $registro['entrada'] }}</span>
                                                </div>
                                                <md-icon class="fichaje-mini-arrow">arrow_forward</md-icon>
                                                <div class="fichaje-mini-time">
                                                    @if(!empty($registro['abierto']))
                                                        <md-icon style="color: var(--warning);">schedule</md-icon>
                                                        <span style="color: var(--warning);">Abierto</span>
                                                    @else
                                                        <md-icon style="color: var(--error);">logout</md-icon>
                                                        <span>{{ $registro['salida'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="fichaje-mini-duration">
                                                @if(!empty($registro['abierto']))
                                                    <span class="status-badge status-badge-warning live-timer"
                                                          data-start-time="{{ $registro['entrada_timestamp'] }}">
                                                        {{ $registro['duracion'] }}
                                                    </span>
                                                @else
                                                    <span class="status-badge status-badge-success">
                                                        {{ $registro['duracion'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if(!empty($registro['abierto']))
                                            <div style="width: 100%; margin-top: 12px;">
                                                <form action="{{ route('registro_horario.salida') }}" method="POST">
                                                    @csrf
                                                    <md-filled-tonal-button type="submit" style="width: 100%;">
                                                        <md-icon slot="icon">check</md-icon>
                                                        Cerrar fichaje
                                                    </md-filled-tonal-button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>

                                    {{-- Desktop view: Table --}}
                                    <div class="hide-on-small-only">
                                        <table class="striped">
                                            <thead>
                                                <tr>
                                                    <th>Entrada</th>
                                                    <th>Salida</th>
                                                    <th>Duración</th>
                                                    <th class="right-align">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dia['registros'] as $registro)
                                                <tr>
                                                    <td>
                                                        <md-icon class="text-claude" style="font-size: 14px;">login</md-icon>
                                                        {{ $registro['entrada'] }}
                                                    </td>
                                                    <td>
                                                        @if(!empty($registro['abierto']))
                                                            <span style="color: var(--warning);">
                                                                <md-icon style="font-size: 14px;">schedule</md-icon> Abierto
                                                            </span>
                                                        @else
                                                            <md-icon style="font-size: 14px; color: var(--error);">logout</md-icon>
                                                            {{ $registro['salida'] }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($registro['abierto']))
                                                            <span class="status-badge status-badge-warning live-timer"
                                                                  data-start-time="{{ $registro['entrada_timestamp'] }}">
                                                                <md-icon style="font-size: 14px;">timer</md-icon>
                                                                {{ $registro['duracion'] }}
                                                            </span>
                                                        @else
                                                            <span class="status-badge status-badge-success">
                                                                <md-icon style="font-size: 14px;">timer</md-icon>
                                                                {{ $registro['duracion'] }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="right-align">
                                                        @if(!empty($registro['abierto']))
                                                            <form action="{{ route('registro_horario.salida') }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                <md-filled-tonal-button type="submit">
                                                                    <md-icon slot="icon">check</md-icon>
                                                                    Cerrar
                                                                </md-filled-tonal-button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </details>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="center-align" style="padding: 60px 20px;">
                        <md-icon class="text-secondary" style="font-size: 72px; width: 72px; height: 72px;">event_busy</md-icon>
                        <h5 class="text-secondary">Sin fichajes cerrados</h5>
                        <p class="text-secondary">Aún no tienes registros de fichajes completados para mostrar en el resumen diario.</p>
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

    // Formatear tiempo que puede superar 24 horas
    $formatTiempo = function($segundos) {
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;
        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
    };

    $balanceFormateado = ($esPositivo ? '+' : '-') . $formatTiempo(abs($balanceSegundos));
    $esperadoFormateado = $formatTiempo($segundosEsperados);
@endphp
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">balance</md-icon>
                    Balance de Horas - {{ $totalMes['mes'] }}
                </span>

                <div class="row" style="margin-top: 20px; margin-bottom: 0;">
                    <div class="col s12 m4 center-align" style="margin-bottom: 15px;">
                        <h6 class="text-secondary" style="margin-bottom: 10px;">Trabajadas</h6>
                        <span class="status-badge status-badge-info" style="font-size: 1.2rem; font-weight: 600;">
                            {{ $totalMes['formateado'] }}
                        </span>
                    </div>
                    <div class="col s12 m4 center-align" style="margin-bottom: 15px;">
                        <h6 class="text-secondary" style="margin-bottom: 10px;">Esperadas</h6>
                        <span class="status-badge status-badge-neutral" style="font-size: 1.2rem; font-weight: 600;">
                            {{ $esperadoFormateado }}
                        </span>
                        <p class="text-secondary" style="margin: 5px 0 0 0; font-size: 0.9rem;">{{ $diasFichados }} {{ $diasFichados == 1 ? 'día' : 'días' }} x 8h</p>
                    </div>
                    <div class="col s12 m4 center-align" style="margin-bottom: 15px;">
                        <h6 class="text-secondary" style="margin-bottom: 10px;">Balance</h6>
                        @if($esPositivo)
                            <span class="status-badge status-badge-success" style="font-size: 1.2rem; font-weight: 600;">
                                <md-icon style="font-size: 14px;">trending_up</md-icon> {{ $balanceFormateado }}
                            </span>
                        @else
                            <span class="status-badge status-badge-error" style="font-size: 1.2rem; font-weight: 600;">
                                <md-icon style="font-size: 14px;">trending_down</md-icon> {{ $balanceFormateado }}
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
                    <md-icon style="margin-right: 8px;">date_range</md-icon>
                    Resumen Mensual
                    @if($tieneAbiertoHoy)
                        <md-icon style="font-size: 20px; vertical-align: middle; color: var(--warning);" title="Fichaje abierto">warning</md-icon>
                    @endif
                </span>

                @if(isset($monthlyRegistros) && count($monthlyRegistros) > 0)
                    <ul class="collapsible" id="monthly-collapsible">
                        <li class="collapsible-item">
                            <details>
                                <summary class="collapsible-header">
                                    <md-icon>event_note</md-icon>
                                    <span style="flex: 1;">{{ $totalMes['mes'] }}</span>
                                    <span class="status-badge status-badge-info" style="min-width: 90px; text-align: center;">
                                        {{ $totalMes['formateado'] }}
                                    </span>
                                    <span class="status-badge status-badge-neutral" style="min-width: 90px; text-align: center;">
                                        {{ count($monthlyRegistros) }} {{ count($monthlyRegistros) == 1 ? 'fichaje' : 'fichajes' }}
                                    </span>
                                    <md-icon class="expand-icon">expand_more</md-icon>
                                </summary>
                                <div class="collapsible-content">
                                    <div class="overflow-x-auto">
                                        <table class="striped highlight">
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
                                                @foreach(collect($monthlyRegistros)->sortByDesc(function($registro) { return $registro->startTime(); }) as $registro)
                                                <tr>
                                                    <td>{{ $registro->startTimeFormatted('d/m/Y') }}</td>
                                                    <td>{{ $registro->startTimeFormatted('H:i:s') }}</td>
                                                    <td>
                                                        @if($registro->endTime())
                                                            {{ $registro->endTimeFormatted('H:i:s') }}
                                                        @else
                                                            <span style="color: var(--warning);">
                                                                <md-icon style="font-size: 14px;">schedule</md-icon> Abierto
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($registro->endTime())
                                                            <span class="status-badge status-badge-info">
                                                                {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                                            </span>
                                                        @else
                                                            <span class="status-badge status-badge-warning live-timer"
                                                                  data-start-time="{{ $registro->startTime() }}">
                                                                {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($registro->isOpen())
                                                            <span class="status-badge status-badge-warning">Abierto</span>
                                                        @else
                                                            <span class="status-badge status-badge-success">Cerrado</span>
                                                        @endif
                                                    </td>
                                                    <td class="right-align">
                                                        @if($registro->isOpen())
                                                            <form action="{{ route('registro_horario.salida') }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                <md-filled-tonal-button type="submit">
                                                                    <md-icon slot="icon">check</md-icon>
                                                                    Cerrar
                                                                </md-filled-tonal-button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </details>
                        </li>
                    </ul>
                @else
                    <div class="center-align" style="padding: 60px 20px;">
                        <md-icon class="text-secondary" style="font-size: 72px; width: 72px; height: 72px;">event_busy</md-icon>
                        <h5 class="text-secondary">Sin fichajes este mes</h5>
                        <p class="text-secondary">Aún no tienes registros de fichajes este mes.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle show/hide full info with md-switch
    var toggle = document.getElementById('show-full-info');
    var icon = document.getElementById('visibility-icon');
    toggle.addEventListener('change', function() {
        var masked = document.querySelectorAll('.masked-info');
        var full = document.querySelectorAll('.full-info');

        if (this.selected) {
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
    document.querySelectorAll('#daily-collapsible details').forEach(function(details) {
        details.open = true;
    });
}

function collapseAll() {
    document.querySelectorAll('#daily-collapsible details').forEach(function(details) {
        details.open = false;
    });
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

        var secondsFromOpen = now - startTime;

        if (!el.dataset.baseClosed) {
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

@if(isset($chartData) && $chartData['hasData'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dailyHoursChart');
    if (!ctx || typeof Chart === 'undefined') return;

    const chartData = @json($chartData);

    // Function to get current theme colors
    function getThemeColors() {
        const style = getComputedStyle(document.documentElement);
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            isDark,
            textColor: style.getPropertyValue('--text-primary').trim() || (isDark ? 'rgba(255, 255, 255, 0.87)' : 'rgba(0, 0, 0, 0.87)'),
            gridColor: style.getPropertyValue('--border-color').trim() || (isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.12)'),
            primaryColor: style.getPropertyValue('--claude-primary').trim() || '#0336FF',
            tooltipBg: isDark ? 'rgba(30, 30, 30, 0.95)' : 'rgba(255, 255, 255, 0.95)',
            tooltipText: isDark ? 'rgba(255, 255, 255, 0.87)' : 'rgba(0, 0, 0, 0.87)',
            pointBorder: isDark ? '#1E1E1E' : '#FFFFFF'
        };
    }

    let colors = getThemeColors();

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Horas trabajadas',
                data: chartData.data,
                borderColor: colors.primaryColor,
                backgroundColor: colors.primaryColor + '20',
                borderWidth: 2,
                fill: true,
                tension: 0,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: colors.primaryColor,
                pointBorderColor: colors.pointBorder,
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: colors.tooltipBg,
                    titleColor: colors.tooltipText,
                    bodyColor: colors.tooltipText,
                    borderColor: colors.gridColor,
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        title: function(tooltipItems) {
                            return tooltipItems[0].label;
                        },
                        label: function(context) {
                            const hours = context.parsed.y;
                            const h = Math.floor(hours);
                            const m = Math.round((hours - h) * 60);
                            return h + 'h ' + m + 'm';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: colors.gridColor,
                        drawBorder: false,
                    },
                    ticks: {
                        color: colors.textColor,
                        maxRotation: 45,
                        minRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 15,
                    }
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: 10,
                    grid: {
                        color: colors.gridColor,
                        drawBorder: false,
                    },
                    ticks: {
                        color: colors.textColor,
                        callback: function(value) {
                            return value + 'h';
                        }
                    }
                }
            }
        }
    });

    // Update chart colors when theme changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'data-theme') {
                colors = getThemeColors();
                // Update dataset colors
                chart.data.datasets[0].borderColor = colors.primaryColor;
                chart.data.datasets[0].backgroundColor = colors.primaryColor + '20';
                chart.data.datasets[0].pointBackgroundColor = colors.primaryColor;
                chart.data.datasets[0].pointBorderColor = colors.pointBorder;
                // Update scales colors
                chart.options.scales.x.grid.color = colors.gridColor;
                chart.options.scales.x.ticks.color = colors.textColor;
                chart.options.scales.y.grid.color = colors.gridColor;
                chart.options.scales.y.ticks.color = colors.textColor;
                // Update tooltip colors
                chart.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
                chart.options.plugins.tooltip.titleColor = colors.tooltipText;
                chart.options.plugins.tooltip.bodyColor = colors.tooltipText;
                chart.options.plugins.tooltip.borderColor = colors.gridColor;
                chart.update();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
});
</script>
@endif
@endsection
