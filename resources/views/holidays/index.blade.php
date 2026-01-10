@extends('layouts.app')

@section('title', 'Mis Vacaciones')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">beach_access</i>
                    Solicitar Vacaciones
                </span>
                <form action="{{ route('holidays.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">today</i>
                            <input type="date" id="start_date" name="start_date"
                                   value="{{ old('start_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            <label for="start_date" class="active">Fecha de inicio</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">event</i>
                            <input type="date" id="end_date" name="end_date"
                                   value="{{ old('end_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            <label for="end_date" class="active">Fecha de fin</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <button type="submit" class="btn waves-effect waves-light light-green darken-3">
                                <i class="material-icons left">send</i>
                                Enviar Solicitud
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons left">history</i>
                    Mis Solicitudes
                </span>
                @if(empty($holidays))
                    <p class="grey-text">No tienes solicitudes de vacaciones.</p>
                @else
                    <table class="striped responsive-table">
                        <thead>
                            <tr>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Dias</th>
                                <th>Estado</th>
                                <th>Solicitado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($holidays as $holiday)
                                <tr>
                                    <td>{{ $holiday->dateRange()->startDateFormatted('d/m/Y') }}</td>
                                    <td>{{ $holiday->dateRange()->endDateFormatted('d/m/Y') }}</td>
                                    <td>{{ $holiday->dateRange()->totalDays() }}</td>
                                    <td>
                                        <span class="badge {{ $holiday->status()->color() }} white-text" style="border-radius: 4px; padding: 4px 8px;">
                                            {{ $holiday->status()->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $holiday->createdAt()->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var startDate = document.getElementById('start_date');
    var endDate = document.getElementById('end_date');

    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        if (endDate.value && endDate.value < this.value) {
            endDate.value = this.value;
        }
    });
});
</script>
@endsection
