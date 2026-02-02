<!-- Resumen Mensual -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">date_range</md-icon>
                    Resumen Mensual
                    @if($tieneAbiertoHoy ?? false)
                        <md-icon style="font-size: 20px; vertical-align: middle; color: var(--warning);" title="Fichaje abierto">warning</md-icon>
                    @endif
                </span>

                @if(isset($monthlyRegistros) && count($monthlyRegistros) > 0)
                    <ul class="collapsible" id="monthly-collapsible">
                        <li class="collapsible-item">
                            <details>
                                <summary class="collapsible-header">
                                    <md-icon>event_note</md-icon>
                                    <span style="flex: 1;">{{ $totalMes['mes'] }}</span>
                                    <x-status-badge variant="info" style="min-width: 90px; text-align: center;">
                                        {{ $totalMes['formateado'] }}
                                    </x-status-badge>
                                    <x-status-badge variant="neutral" style="min-width: 90px; text-align: center;">
                                        {{ count($monthlyRegistros) }} {{ count($monthlyRegistros) == 1 ? 'fichaje' : 'fichajes' }}
                                    </x-status-badge>
                                    <md-icon class="expand-icon">expand_more</md-icon>
                                </summary>
                                <div class="collapsible-content">
                                    <div class="overflow-x-auto">
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
                                                @foreach(collect($monthlyRegistros)->sortByDesc(function($registro) { return $registro->startTime(); }) as $registro)
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
                                </div>
                            </details>
                        </li>
                    </ul>
                @else
                    <x-empty-state icon="event_busy" title="Sin fichajes este mes" description="Aún no tienes registros de fichajes este mes." />
                @endif
            </div>
        </div>
    </div>
</div>
