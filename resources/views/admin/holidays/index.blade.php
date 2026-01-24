@extends('layouts.app')

@section('title', 'Gestionar Vacaciones')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">hourglass_empty</md-icon>
                    Solicitudes de Vacaciones Pendientes
                </span>
                @if(empty($pendingWithUsers))
                    <p class="text-secondary">No hay solicitudes pendientes.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="striped">
                            <thead>
                                <tr>
                                    <th>Empleado</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Dias</th>
                                    <th>Solicitado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingWithUsers as $item)
                                    @php
                                        $holiday = $item['holiday'];
                                        $user = $item['user'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $user ? $user->name() : 'Usuario desconocido' }}</strong>
                                            @if($user)
                                                <br><small class="text-secondary">{{ $user->email()->value() }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $holiday->dateRange()->startDateFormatted('d/m/Y') }}</td>
                                        <td>{{ $holiday->dateRange()->endDateFormatted('d/m/Y') }}</td>
                                        <td>{{ $holiday->dateRange()->totalDays() }}</td>
                                        <td>{{ $holiday->createdAtFormatted('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="d-inline-flex gap-1">
                                                <form action="{{ route('admin.holidays.approve', $holiday->id()->value()) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <md-filled-button type="submit" title="Aprobar" style="--md-filled-button-container-color: var(--success);">
                                                        <md-icon slot="icon">check</md-icon>
                                                    </md-filled-button>
                                                </form>
                                                <form action="{{ route('admin.holidays.reject', $holiday->id()->value()) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <md-filled-button type="submit" title="Rechazar" style="--md-filled-button-container-color: var(--error);">
                                                        <md-icon slot="icon">close</md-icon>
                                                    </md-filled-button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">event_available</md-icon>
                    Vacaciones Aprobadas
                </span>
                @if(empty($approvedWithUsers))
                    <p class="text-secondary">No hay vacaciones aprobadas.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="striped">
                            <thead>
                                <tr>
                                    <th>Empleado</th>
                                    <th>Email</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Dias</th>
                                    <th>Solicitado</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvedWithUsers as $item)
                                    @php
                                        $holiday = $item['holiday'];
                                        $user = $item['user'];
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $user ? $user->name() : 'Usuario desconocido' }}</strong></td>
                                        <td>
                                            @if($user)
                                                <small class="text-secondary">{{ $user->email()->value() }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $holiday->dateRange()->startDateFormatted('d/m/Y') }}</td>
                                        <td>{{ $holiday->dateRange()->endDateFormatted('d/m/Y') }}</td>
                                        <td>{{ $holiday->dateRange()->totalDays() }}</td>
                                        <td>{{ $holiday->createdAtFormatted('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="status-badge status-badge-success">
                                                <md-icon style="font-size: 14px;">check</md-icon> Aprobada
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
