<!-- Todos los Fichajes de Hoy -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                @php
                    $totalSegundosHoy = collect($allRegistros)->sum(fn($r) => $r->workedSeconds());
                    $tieneAbiertoHoy = collect($allRegistros)->contains(fn($r) => $r->isOpen());
                    $registroAbierto = collect($allRegistros)->first(fn($r) => $r->isOpen());
                @endphp
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <span class="card-title" style="margin: 0;">
                        <md-icon style="margin-right: 8px;">schedule</md-icon>
                        Fichaje de hoy
                    </span>
                    @if(isset($allRegistros) && count($allRegistros) > 0)
                        @if($tieneAbiertoHoy)
                            <span class="status-badge status-badge-warning live-timer-total"
                                  style="margin: 0; font-size: 1.1rem; font-weight: 600;"
                                  data-base-seconds="{{ $totalSegundosHoy }}"
                                  data-start-time="{{ $registroAbierto->startTime() }}">
                                {{ gmdate('H:i:s', $totalSegundosHoy) }}
                            </span>
                        @else
                            <x-status-badge variant="info" style="margin: 0; font-size: 1.1rem; font-weight: 600;">
                                {{ gmdate('H:i:s', $totalSegundosHoy) }}
                            </x-status-badge>
                        @endif
                    @endif
                </div>

                @if(isset($allRegistros) && count($allRegistros) > 0)
                    <p class="text-secondary" style="margin-top: 10px;">Total de {{ count($allRegistros) }} {{ count($allRegistros) == 1 ? 'registro' : 'registros' }}</p>

                    {{-- Mobile view: Cards --}}
                    <div class="hide-on-med-and-up">
                        @foreach(collect($allRegistros)->sortByDesc(function($registro) { return $registro->startTime(); }) as $registro)
                        <div class="fichaje-card-mobile">
                            <div class="fichaje-card-header">
                                <span class="fichaje-card-date">{{ $registro->startTimeFormatted('d/m/Y') }}</span>
                                @if($registro->isOpen())
                                    <x-status-badge variant="warning">Abierto</x-status-badge>
                                @else
                                    <x-status-badge variant="success">Cerrado</x-status-badge>
                                @endif
                            </div>
                            <div class="fichaje-card-times">
                                <div class="fichaje-time-item">
                                    <span class="label">Entrada</span>
                                    <span class="value">{{ $registro->startTimeFormatted('H:i:s') }}</span>
                                </div>
                                <div class="fichaje-time-item">
                                    <span class="label">Salida</span>
                                    <span class="value">
                                        @if($registro->endTime())
                                            {{ $registro->endTimeFormatted('H:i:s') }}
                                        @else
                                            <span style="color: var(--warning);">--:--:--</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="fichaje-time-item">
                                    <span class="label">Duracion</span>
                                    @if($registro->endTime())
                                        <x-status-badge variant="info" style="margin: 0;">
                                            {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                        </x-status-badge>
                                    @else
                                        <span class="status-badge status-badge-warning live-timer" style="margin: 0;"
                                              data-start-time="{{ $registro->startTime() }}">
                                            {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($registro->isOpen())
                            <div class="fichaje-card-footer">
                                <form action="{{ route('registro_horario.salida') }}" method="POST" style="width: 100%;">
                                    @csrf
                                    <md-filled-tonal-button type="submit" style="width: 100%;">
                                        <md-icon slot="icon">check</md-icon>
                                        Cerrar fichaje
                                    </md-filled-tonal-button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    {{-- Desktop view: Table --}}
                    <div class="hide-on-small-only">
                        <table class="striped highlight">
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
                                                <md-icon style="font-size: 14px;">schedule</md-icon> Abierto
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($registro->endTime())
                                            <x-status-badge variant="info">
                                                {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                            </x-status-badge>
                                        @else
                                            <span class="status-badge status-badge-warning live-timer"
                                                  data-start-time="{{ $registro->startTime() }}">
                                                {{ gmdate('H:i:s', $registro->workedSeconds()) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($registro->isOpen())
                                            <x-status-badge variant="warning">Abierto</x-status-badge>
                                        @else
                                            <x-status-badge variant="success">Cerrado</x-status-badge>
                                        @endif
                                    </td>
                                    <td class="right-align">
                                        @if($registro->isOpen())
                                            <form action="{{ route('registro_horario.salida') }}" method="POST" style="display: inline;">
                                                @csrf
                                                <md-filled-tonal-button type="submit">
                                                    <md-icon slot="icon">check</md-icon>
                                                    Cerrar
                                                </md-filled-tonal-button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty-state icon="schedule" title="Sin registros de fichaje" description="Aún no tienes ningún registro de fichaje." />
                @endif
            </div>
        </div>
    </div>
</div>
