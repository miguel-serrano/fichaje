@extends('layouts.app')

@section('title', 'Registro Horario')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Registro Horario</span>
                <p class="text-secondary">Ficha de entrada y salida.</p>

                <div class="divider" style="margin: 30px 0;"></div>

                <div class="row">
                    <div class="col s12 m6">
                        <p><strong class="text-secondary">Usuario:</strong></p>
                        <p>{{ Str::ucfirst($user->name()) }} ({{ $user->email()->value() }})</p>
                    </div>
                    <div class="col s12 m6">
                        <p><strong class="text-secondary">Tiempo acumulado hoy:</strong></p>
                        <p>
                            <span class="status-badge status-badge-info">
                                @php
                                    $horas = floor($segundos / 3600);
                                    $minutos = floor(($segundos % 3600) / 60);
                                    $segundosRestantes = $segundos % 60;
                                @endphp
                                {{ str_pad($horas, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutos, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($segundosRestantes, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row" style="margin-top: 30px;">
                    <div class="col s12">
                        @if($canClockIn)
                            <form method="POST" action="{{ route('registro_horario.entrada') }}">
                                @csrf
                                <md-filled-button
                                    type="submit"
                                    @if($tieneRegistroAbierto) disabled @endif
                                >
                                    <md-icon slot="icon">input</md-icon>
                                    Fichar Entrada
                                </md-filled-button>
                            </form>
                            @if($tieneRegistroAbierto)
                                <p style="margin-top: 15px;" class="text-secondary">
                                    Tienes un fichaje abierto. Puedes cerrarlo desde <a href="{{ route('user.me') }}" class="text-claude">tu página de fichajes</a>.
                                </p>
                            @endif
                        @else
                            <div class="card-panel card-panel-warning">
                                <md-icon style="margin-right: 8px;">warning</md-icon>
                                <span>No tienes permisos para fichar.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
