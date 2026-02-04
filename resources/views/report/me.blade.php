@extends('layouts.app')

@section('title', 'Mi Informe')
@section('page-id', 'report.me')

@section('content')
<div class="row">
    <div class="col s12">
        <h4 style="display: flex; align-items: center; gap: 8px;">
            <md-icon>assessment</md-icon>
            Mi Informe
        </h4>
    </div>
</div>

<!-- Histórico de Fichajes - Gráfica -->
<div class="row">
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
                    <x-empty-state icon="insert_chart_outlined" title="Sin datos para mostrar" description="No hay fichajes cerrados en los últimos 30 días." />
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Horas trabajadas del mes -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">schedule</md-icon>
                    Horas del Mes
                </span>
                <p class="text-secondary" style="margin-bottom: 16px;">
                    {{ $hoursWorked }}h trabajadas de {{ $hoursTarget }}h
                </p>
                <div style="position: relative; height: 300px;">
                    <canvas id="hoursChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Vacaciones aceptadas -->
    <div class="col s12 l6">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">beach_access</md-icon>
                    Vacaciones {{ date('Y') }}
                </span>
                <p class="text-secondary" style="margin-bottom: 16px;">
                    {{ $approvedDays }} días disfrutados de {{ $holidaysTarget }}
                </p>
                <div style="position: relative; height: 300px;">
                    <canvas id="holidaysChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-data')
<script>
window.__pageData = {
    hoursWorked: @json($hoursWorked),
    hoursTarget: @json($hoursTarget),
    approvedDays: @json($approvedDays),
    holidaysTarget: @json($holidaysTarget),
    chartData: @json($chartData),
};
</script>
@endpush
