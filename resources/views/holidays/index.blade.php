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
                    <input type="hidden" name="start_date" id="start_date_input" value="{{ old('start_date') }}">
                    <input type="hidden" name="end_date" id="end_date_input" value="{{ old('end_date') }}">

                    <div class="row">
                        <div class="col s12 m6" style="margin-bottom: 16px;">
                            <label class="text-secondary" style="margin-bottom: 8px; display: block;">Fecha de inicio*</label>
                            <button type="button" class="md-date-chip" id="start_date_trigger" autocomplete="off">
                                <md-icon>today</md-icon>
                                <span class="md-date-chip-text" id="start_date_display">
                                    <span class="md-date-chip-placeholder">Seleccionar fecha</span>
                                </span>
                            </button>
                        </div>
                        <div class="col s12 m6" style="margin-bottom: 16px;">
                            <label class="text-secondary" style="margin-bottom: 8px; display: block;">Fecha de fin*</label>
                            <button type="button" class="md-date-chip" id="end_date_trigger" autocomplete="off">
                                <md-icon>event</md-icon>
                                <span class="md-date-chip-text" id="end_date_display">
                                    <span class="md-date-chip-placeholder">Seleccionar fecha</span>
                                </span>
                            </button>
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
                    {{-- Mobile view: Cards --}}
                    <div class="hide-on-med-and-up">
                        @foreach($holidays as $holiday)
                            @php
                                $statusClass = match($holiday->status()->value) {
                                    'approved' => 'status-badge-success',
                                    'rejected' => 'status-badge-error',
                                    default => 'status-badge-warning',
                                };
                            @endphp
                            <div class="holiday-card-mobile">
                                <div class="holiday-card-header">
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $holiday->status()->label() }}
                                    </span>
                                    <span class="holiday-days">{{ $holiday->dateRange()->totalDays() }} {{ $holiday->dateRange()->totalDays() == 1 ? 'dia' : 'dias' }}</span>
                                </div>
                                <div class="holiday-card-dates">
                                    <div class="holiday-date-item">
                                        <md-icon>flight_takeoff</md-icon>
                                        <span>{{ $holiday->dateRange()->startDateFormatted('d/m/Y') }}</span>
                                    </div>
                                    <md-icon class="holiday-date-arrow">arrow_forward</md-icon>
                                    <div class="holiday-date-item">
                                        <md-icon>flight_land</md-icon>
                                        <span>{{ $holiday->dateRange()->endDateFormatted('d/m/Y') }}</span>
                                    </div>
                                </div>
                                <div class="holiday-card-footer">
                                    <md-icon>schedule</md-icon>
                                    <span class="text-secondary">Solicitado: {{ $holiday->createdAtFormatted('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Desktop view: Table --}}
                    <div class="hide-on-small-only">
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
    const startInput = document.getElementById('start_date_input');
    const endInput = document.getElementById('end_date_input');
    const startDisplay = document.getElementById('start_date_display');
    const endDisplay = document.getElementById('end_date_display');
    const startTrigger = document.getElementById('start_date_trigger');
    const endTrigger = document.getElementById('end_date_trigger');

    if (!startTrigger || !endTrigger) return;

    let endPicker = null;

    // Start date picker
    const startPicker = window.initDatePicker(startInput, {
        trigger: startTrigger,
        displayElement: startDisplay,
        minDate: new Date(),
        onSelect: function(date) {
            startDisplay.innerHTML = window.formatDisplayDate(date);
            // Update end date min to be >= start date
            if (endPicker) {
                endPicker.setMinDate(date);
            }
        }
    });

    // End date picker
    endPicker = window.initDatePicker(endInput, {
        trigger: endTrigger,
        displayElement: endDisplay,
        minDate: new Date(),
        getMinDate: function() {
            // Dynamically get min date from start date
            if (startInput.value) {
                return new Date(startInput.value);
            }
            return new Date();
        }
    });

    // Set initial values if present
    @if(old('start_date'))
    const oldStartDate = new Date('{{ old('start_date') }}');
    startDisplay.innerHTML = window.formatDisplayDate(oldStartDate);
    @endif

    @if(old('end_date'))
    const oldEndDate = new Date('{{ old('end_date') }}');
    endDisplay.innerHTML = window.formatDisplayDate(oldEndDate);
    @endif

    // Form validation
    const form = document.getElementById('holiday-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!startInput.value || !endInput.value) {
                e.preventDefault();
                window.toast.error('Por favor selecciona ambas fechas');
                return false;
            }
        });
    }
});
</script>
@endsection
