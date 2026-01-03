@extends('layouts.app')

@section('title', 'Registro Horario')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Registro Horario</span>
                <p class="grey-text">Ficha de entrada y salida.</p>

                <div class="divider" style="margin: 30px 0;"></div>

                <div class="row">
                    <div class="col s12 m6">
                        <p><strong class="grey-text text-darken-1">Usuario:</strong></p>
                        <p>{{ $user->name() }} ({{ $user->email()->getValue() }})</p>
                    </div>
                    <div class="col s12 m6">
                        <p><strong class="grey-text text-darken-1">Tiempo acumulado hoy:</strong></p>
                        <p>
                            <span class="chip blue lighten-4 blue-text text-darken-2">
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
                        <form method="POST" action="{{ route('registro_horario.entrada') }}">
                            @csrf
                            <button
                                type="submit"
                                @if($tieneRegistroAbierto) disabled @endif
                                class="btn waves-effect waves-light light-green {{ $tieneRegistroAbierto ? 'disabled' : '' }}"
                            >
                                <i class="material-icons left">input</i>Fichar Entrada
                            </button>
                        </form>
                        @if($tieneRegistroAbierto)
                            <p style="margin-top: 15px;" class="grey-text">
                                Tienes un fichaje abierto. Puedes cerrarlo desde <a href="{{ route('user.index') }}" class="light-green-text">tu página de fichajes</a>.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
