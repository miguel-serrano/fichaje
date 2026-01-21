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
                        <a href="{{ route('users.index') }}" class="btn waves-effect waves-light btn-claude">
                            <i class="material-icons left">arrow_back</i>Volver
                        </a>
                    </div>
                </div>

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="row">
                    <div class="col s12 m6">
                        <h6 class="grey-text">Nombre</h6>
                        <p>{{ $user->name() }}</p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text">Email</h6>
                        <p>{{ $user->email()->value() }}</p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text">UUID</h6>
                        <p><code>{{ $user->uuid()->value() }}</code></p>
                    </div>
                    <div class="col s12 m6">
                        <h6 class="grey-text">Estado</h6>
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

<!-- Gestión de Roles -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">security</i>
                    Roles del Usuario
                </span>

                <div class="divider" style="margin: 20px 0;"></div>

                <!-- Roles actuales -->
                <h6 class="grey-text">Roles Asignados</h6>
                <div style="margin: 15px 0;">
                    @forelse($userRoles as $role)
                        <div class="chip" style="margin: 5px;">
                            <i class="material-icons tiny">verified_user</i>
                            {{ $role['name'] }}
                            @if(!($role['is_system'] && $role['slug'] === 'super_admin'))
                                <form action="{{ route('user.roles.remove', ['id' => $user->id()->value(), 'roleSlug' => $role['slug']]) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('¿Seguro que deseas quitar el rol {{ $role['name'] }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-flat btn-small" style="padding: 0; margin-left: 5px; min-width: auto; height: auto; line-height: 1;">
                                        <i class="material-icons tiny" style="color: var(--error);">close</i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="grey-text"><em>Sin roles asignados</em></p>
                    @endforelse
                </div>

                <!-- Agregar nuevo rol -->
                @php
                    $userRoleSlugs = collect($userRoles)->pluck('slug')->toArray();
                    $availableRoles = collect($allRoles)->filter(fn($r) => !in_array($r['slug'], $userRoleSlugs));
                @endphp

                @if($availableRoles->isNotEmpty())
                    <div class="divider" style="margin: 20px 0;"></div>
                    <h6 class="grey-text">Asignar Nuevo Rol</h6>

                    <form action="{{ route('user.roles.assign', ['id' => $user->id()->value()]) }}" method="POST">
                        @csrf
                        <div class="row" style="margin-bottom: 0;">
                            <div class="input-field col s12 m8">
                                <select name="role_slug" id="role_slug" required>
                                    <option value="" disabled selected>Selecciona un rol</option>
                                    @foreach($availableRoles as $role)
                                        <option value="{{ $role['slug'] }}">
                                            {{ $role['name'] }}
                                            @if($role['description'])
                                                - {{ $role['description'] }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <label for="role_slug">Rol a asignar</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <button type="submit" class="btn waves-effect waves-light btn-claude">
                                    <i class="material-icons left">add</i>Asignar
                                </button>
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
                        <i class="material-icons left text-claude">event</i>
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
                                        <span class="grey-text">--</span>
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
                        <h5 class="grey-text">Sin registros de fichaje</h5>
                        <p class="grey-text">Este usuario aún no tiene ningún registro de fichaje.</p>
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
                        <button onclick="expandAll()" class="btn-small waves-effect waves-light btn-claude">
                            <i class="material-icons left">unfold_more</i>Expandir
                        </button>
                        <button onclick="collapseAll()" class="btn-small waves-effect waves-light" style="background: var(--bg-secondary) !important; color: var(--text-primary) !important;">
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
                                <span class="chip chip-info">
                                    {{ $dia['total_formateado'] }}
                                </span>
                                <span class="chip chip-neutral">
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
                                                <i class="material-icons tiny" style="color: var(--error);">logout</i>
                                                {{ $registro['salida'] }}
                                            </td>
                                            <td>
                                                <span class="chip chip-success">
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
                        <h5 class="grey-text">Sin fichajes cerrados</h5>
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

    // Initialize select for role assignment
    var selectElems = document.querySelectorAll('select');
    M.FormSelect.init(selectElems);
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
