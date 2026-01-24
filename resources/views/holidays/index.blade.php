@extends('layouts.app')

@section('title', 'Mis Vacaciones')

@section('content')
@if($canRequestHoliday)
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">beach_access</md-icon>
                    Solicitar Vacaciones
                </span>
                <form action="{{ route('holidays.store') }}" method="POST" id="holiday-form">
                    @csrf
                    <div class="row">
                        <div class="col s12 m6" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="start_date"
                                name="start_date"
                                type="text"
                                label="Fecha de inicio"
                                value="{{ old('start_date') }}"
                                required
                                style="width: 100%;"
                                data-flatpickr='{"minDate": "today"}'
                            >
                                <md-icon slot="leading-icon">today</md-icon>
                            </md-outlined-text-field>
                        </div>
                        <div class="col s12 m6" style="margin-bottom: 16px;">
                            <md-outlined-text-field
                                id="end_date"
                                name="end_date"
                                type="text"
                                label="Fecha de fin"
                                value="{{ old('end_date') }}"
                                required
                                style="width: 100%;"
                                data-flatpickr='{"minDate": "today"}'
                            >
                                <md-icon slot="leading-icon">event</md-icon>
                            </md-outlined-text-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col s12">
                            <md-filled-button type="submit">
                                <md-icon slot="icon">send</md-icon>
                                Enviar Solicitud
                            </md-filled-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<div class="row">
    <div class="col s12">
        <div class="card-panel card-panel-warning">
            <md-icon style="margin-right: 8px;">warning</md-icon>
            <span>No tienes permisos para solicitar vacaciones. Contacta con tu administrador.</span>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">history</md-icon>
                    Mis Solicitudes
                </span>
                @if(empty($holidays))
                    <p class="text-secondary">No tienes solicitudes de vacaciones.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="striped">
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
                                            @php
                                                $statusClass = match($holiday->status()->value) {
                                                    'approved' => 'status-badge-success',
                                                    'rejected' => 'status-badge-error',
                                                    default => 'status-badge-warning',
                                                };
                                            @endphp
                                            <span class="status-badge {{ $statusClass }}">
                                                {{ $holiday->status()->label() }}
                                            </span>
                                        </td>
                                        <td>{{ $holiday->createdAtFormatted('d/m/Y H:i') }}</td>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize flatpickr with date linking
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');

    if (startInput && endInput) {
        const startPicker = flatpickr(startInput, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            allowInput: true,
            onChange: function(selectedDates) {
                if (selectedDates[0]) {
                    endPicker.set('minDate', selectedDates[0]);
                    if (endPicker.selectedDates[0] && endPicker.selectedDates[0] < selectedDates[0]) {
                        endPicker.setDate(selectedDates[0]);
                    }
                }
            }
        });

        const endPicker = flatpickr(endInput, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            allowInput: true
        });
    }

    // Handle form submission
    const form = document.getElementById('holiday-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const startField = document.getElementById('start_date');
            const endField = document.getElementById('end_date');

            // Create hidden inputs with the values from md-outlined-text-field
            ['start_date', 'end_date'].forEach(function(fieldName) {
                const field = document.getElementById(fieldName);
                if (field && field.value) {
                    let hiddenInput = form.querySelector('input[name="' + fieldName + '"][type="hidden"]');
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = fieldName;
                        form.appendChild(hiddenInput);
                    }
                    hiddenInput.value = field.value;
                }
            });
        });
    }
});
</script>
@endsection
