@extends('layouts.app')

@section('title', 'Detalles de Usuario')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12 m8">
                        <span class="card-title">Información del Usuario</span>
                    </div>
                    <div class="col s12 m4 right-align">
                        <md-filled-button href="{{ route('users.index') }}">
                            <md-icon slot="icon">arrow_back</md-icon>
                            Volver
                        </md-filled-button>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="row">
                    <div class="col s12 m6">
                        <h6 class="text-secondary">Nombre</h6>
                        <p>{{ $user->name() }}</p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="text-secondary">Email</h6>
                        <p>{{ $user->email()->value() }}</p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="text-secondary">UUID</h6>
                        <p><code>{{ $user->uuid()->value() }}</code></p>
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
        </div>
    </div>
</div>

<!-- Gestión de Roles -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">security</md-icon>
                    Roles del Usuario
                </span>

                <div class="divider" style="margin: 20px 0;"></div>

                <!-- Roles actuales -->
                <h6 class="text-secondary">Roles Asignados</h6>
                <div class="badge-group" style="margin: 15px 0;">
                    @forelse($userRoles as $role)
                        <span class="status-badge status-badge-info">
                            <md-icon style="font-size: 14px;">verified_user</md-icon>
                            {{ $role['name'] }}
                            @if(!($role['is_system'] && $role['slug'] === 'super_admin'))
                                <form action="{{ route('user.roles.remove', ['id' => $user->id()->value(), 'roleSlug' => $role['slug']]) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('¿Seguro que deseas quitar el rol {{ $role['name'] }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; padding: 0; margin-left: 4px; cursor: pointer; vertical-align: middle; line-height: 1;">
                                        <md-icon style="font-size: 14px; color: white;">close</md-icon>
                                    </button>
                                </form>
                            @endif
                        </span>
                    @empty
                        <p class="text-secondary"><em>Sin roles asignados</em></p>
                    @endforelse
                </div>

                <!-- Agregar nuevo rol -->
                @php
                    $userRoleSlugs = collect($userRoles)->pluck('slug')->toArray();
                    $availableRoles = collect($allRoles)->filter(fn($r) => !in_array($r['slug'], $userRoleSlugs));
                @endphp

                @if($availableRoles->isNotEmpty())
                    <div class="divider" style="margin: 20px 0;"></div>
                    <h6 class="text-secondary">Asignar Nuevo Rol</h6>

                    <form action="{{ route('user.roles.assign', ['id' => $user->id()->value()]) }}" method="POST" id="assign-role-form">
                        @csrf
                        <div class="row" style="margin-bottom: 0; align-items: center;">
                            <div class="col s12 m8" style="margin-bottom: 16px;">
                                <md-outlined-select
                                    name="role_slug"
                                    id="role_slug"
                                    label="Rol a asignar"
                                    required
                                    style="width: 100%;"
                                >
                                    @foreach($availableRoles as $role)
                                        <md-select-option value="{{ $role['slug'] }}">
                                            <div slot="headline">{{ $role['name'] }}</div>
                                            @if($role['description'])
                                                <div slot="supporting-text">{{ $role['description'] }}</div>
                                            @endif
                                        </md-select-option>
                                    @endforeach
                                </md-outlined-select>
                            </div>
                            <div class="col s12 m4">
                                <md-filled-button type="submit">
                                    <md-icon slot="icon">add</md-icon>
                                    Asignar
                                </md-filled-button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Resumen Mensual -->
@if(isset($totalMes) && $totalMes['segundos'] > 0)
<div class="row">
    <div class="col s12">
        <div class="card" style="background: var(--info-bg) !important;">
            <div class="card-content">
                <div class="row valign-wrapper" style="margin-bottom: 0;">
                    <div class="col s12 m6">
                        <md-icon class="text-claude" style="margin-right: 8px;">event</md-icon>
                        <span class="card-title">Total {{ $totalMes['mes'] }}</span>
                    </div>
                    <div class="col s12 m6 right-align">
                        <h4 class="text-claude" style="margin: 0;">{{ $totalMes['formateado'] }}</h4>
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
                    <md-icon style="margin-right: 8px;">assignment</md-icon>
                    Todos los Fichajes
                </span>

                @if(isset($allRegistros) && count($allRegistros) > 0)
                    <p class="text-secondary">Total de {{ count($allRegistros) }} {{ count($allRegistros) == 1 ? 'registro' : 'registros' }}</p>

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
                                        <span class="text-secondary">--</span>
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
                                            <span class="text-secondary">--</span>
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
                        <md-icon class="text-secondary" style="font-size: 72px; width: 72px; height: 72px;">assignment_late</md-icon>
                        <h5 class="text-secondary">Sin registros de fichaje</h5>
                        <p class="text-secondary">Este usuario aún no tiene ningún registro de fichaje.</p>
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
                            <md-icon style="margin-right: 8px;">calendar_today</md-icon>
                            Resumen Diario
                        </span>
                        <p class="text-secondary">Fichajes cerrados agrupados por día</p>
                    </div>
                    <div class="col s12 m6 right-align">
                        <md-filled-tonal-button onclick="expandAll()">
                            <md-icon slot="icon">unfold_more</md-icon>
                            Expandir
                        </md-filled-tonal-button>
                        <md-outlined-button onclick="collapseAll()">
                            <md-icon slot="icon">unfold_less</md-icon>
                            Colapsar
                        </md-outlined-button>
                    </div>
                </div>

                @if(count($dailyRegistros) > 0)
                    <ul class="collapsible" id="daily-collapsible">
                        @foreach($dailyRegistros as $index => $dia)
                        <li class="collapsible-item">
                            <details>
                                <summary class="collapsible-header">
                                    <md-icon>date_range</md-icon>
                                    <span style="flex: 1;">{{ $dia['fecha_formateada'] }}</span>
                                    <span class="status-badge status-badge-info">
                                        {{ $dia['total_formateado'] }}
                                    </span>
                                    <span class="status-badge status-badge-neutral">
                                        {{ count($dia['registros']) }} {{ count($dia['registros']) == 1 ? 'fichaje' : 'fichajes' }}
                                    </span>
                                    <md-icon class="expand-icon">expand_more</md-icon>
                                </summary>
                                <div class="collapsible-content">
                                    {{-- Mobile view: List --}}
                                    <div class="hide-on-med-and-up">
                                        @foreach($dia['registros'] as $registro)
                                        <div class="fichaje-mini-card">
                                            <div class="fichaje-mini-times">
                                                <div class="fichaje-mini-time">
                                                    <md-icon class="text-claude">login</md-icon>
                                                    <span>{{ $registro['entrada'] }}</span>
                                                </div>
                                                <md-icon class="fichaje-mini-arrow">arrow_forward</md-icon>
                                                <div class="fichaje-mini-time">
                                                    <md-icon style="color: var(--error);">logout</md-icon>
                                                    <span>{{ $registro['salida'] }}</span>
                                                </div>
                                            </div>
                                            <div class="fichaje-mini-duration">
                                                <span class="status-badge status-badge-success">
                                                    {{ $registro['duracion'] }}
                                                </span>
                                            </div>
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
                                                        <md-icon style="font-size: 14px; color: var(--error);">logout</md-icon>
                                                        {{ $registro['salida'] }}
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-badge-success">
                                                            <md-icon style="font-size: 14px;">timer</md-icon>
                                                            {{ $registro['duracion'] }}
                                                        </span>
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
                        <p class="text-secondary">Este usuario aún no tiene registros de fichajes completados para mostrar en el resumen diario.</p>
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
    // Handle form submission with md-outlined-select
    const assignForm = document.getElementById('assign-role-form');
    if (assignForm) {
        assignForm.addEventListener('submit', function(e) {
            const selectEl = document.getElementById('role_slug');
            if (selectEl && selectEl.value) {
                let hiddenInput = assignForm.querySelector('input[name="role_slug"][type="hidden"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'role_slug';
                    assignForm.appendChild(hiddenInput);
                }
                hiddenInput.value = selectEl.value;
            }
        });
    }
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
</script>
@endsection
