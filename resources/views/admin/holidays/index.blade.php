@extends('layouts.app')

@section('title', 'Gestionar Vacaciones')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">hourglass_empty</i>
                    Solicitudes de Vacaciones Pendientes
                </span>
                @if(empty($pendingWithUsers))
                    <p class="grey-text">No hay solicitudes pendientes.</p>
                @else
                    <table class="striped responsive-table">
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
                                            <br><small class="grey-text">{{ $user->email()->value() }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $holiday->dateRange()->startDateFormatted('d/m/Y') }}</td>
                                    <td>{{ $holiday->dateRange()->endDateFormatted('d/m/Y') }}</td>
                                    <td>{{ $holiday->dateRange()->totalDays() }}</td>
                                    <td>{{ $holiday->createdAt()->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <form action="{{ route('admin.holidays.approve', $holiday->id()->value()) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-small waves-effect waves-light green" title="Aprobar">
                                                <i class="material-icons">check</i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.holidays.reject', $holiday->id()->value()) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-small waves-effect waves-light red" title="Rechazar">
                                                <i class="material-icons">close</i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                    <i class="material-icons left">event_available</i>
                    Vacaciones Aprobadas
                </span>
                @if(empty($approvedWithUsers))
                    <p class="grey-text">No hay vacaciones aprobadas.</p>
                @else
                    <table class="striped responsive-table">
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
                                            <small class="grey-text">{{ $user->email()->value() }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $holiday->dateRange()->startDateFormatted('d/m/Y') }}</td>
                                    <td>{{ $holiday->dateRange()->endDateFormatted('d/m/Y') }}</td>
                                    <td>{{ $holiday->dateRange()->totalDays() }}</td>
                                    <td>{{ $holiday->createdAt()->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="new badge green" data-badge-caption="">
                                            <i class="material-icons tiny">check</i> Aprobada
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
