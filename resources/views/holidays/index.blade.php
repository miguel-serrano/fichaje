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
                            <input type="text" id="start_date" name="start_date" class="datepicker"
                                   value="{{ old('start_date') }}"
                                   required>
                            <label for="start_date">Fecha de inicio</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">event</i>
                            <input type="text" id="end_date" name="end_date" class="datepicker"
                                   value="{{ old('end_date') }}"
                                   required>
                            <label for="end_date">Fecha de fin</label>
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
    var today = new Date();
    today.setHours(0, 0, 0, 0);

    var i18n = {
        cancel: 'Cancelar',
        clear: 'Limpiar',
        done: 'Aceptar',
        months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthsShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        weekdays: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        weekdaysAbbrev: ['D', 'L', 'M', 'X', 'J', 'V', 'S']
    };

    var startDateElem = document.getElementById('start_date');
    var endDateElem = document.getElementById('end_date');
    var endDateInstance;

    var startDateInstance = M.Datepicker.init(startDateElem, {
        format: 'yyyy-mm-dd',
        minDate: today,
        autoClose: true,
        showClearBtn: true,
        i18n: i18n,
        firstDay: 1,
        onSelect: function(date) {
            if (endDateInstance) {
                endDateInstance.options.minDate = date;
                var endDate = endDateInstance.date;
                if (endDate && endDate < date) {
                    endDateInstance.setDate(date);
                    endDateElem.value = startDateElem.value;
                }
            }
        }
    });

    endDateInstance = M.Datepicker.init(endDateElem, {
        format: 'yyyy-mm-dd',
        minDate: today,
        autoClose: true,
        showClearBtn: true,
        i18n: i18n,
        firstDay: 1
    });
});
</script>
@endsection
