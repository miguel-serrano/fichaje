<!-- Resumen Diario -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">calendar_today</md-icon>
                    Resumen Diario
                </span>
                <p class="text-secondary">Fichajes cerrados agrupados por día</p>
                <div style="margin: 15px 0; display: flex; gap: 8px; flex-wrap: wrap;">
                    <md-filled-tonal-button onclick="expandAll()">
                        <md-icon slot="icon">unfold_more</md-icon>
                        <span class="hide-on-small-only">Expandir</span>
                    </md-filled-tonal-button>
                    <md-outlined-button onclick="collapseAll()">
                        <md-icon slot="icon">unfold_less</md-icon>
                        <span class="hide-on-small-only">Colapsar</span>
                    </md-outlined-button>
                </div>

                @if(count($dailyRegistros) > 0)
                    <ul class="collapsible" id="daily-collapsible">
                        @foreach($dailyRegistros as $index => $dia)
                        <li class="collapsible-item">
                            <details>
                                <summary class="collapsible-header">
                                    <md-icon>date_range</md-icon>
                                    <span style="flex: 1;">{{ $dia['fecha_formateada'] }}</span>
                                    @if(!empty($dia['tiene_abierto']))
                                        <span class="status-badge status-badge-warning live-timer-total"
                                              style="min-width: 90px; text-align: center;"
                                              data-base-seconds="{{ $dia['total_segundos'] }}"
                                              data-start-time="{{ collect($dia['registros'])->firstWhere('abierto', true)['entrada_timestamp'] ?? 0 }}">
                                            {{ $dia['total_formateado'] }}
                                        </span>
                                    @else
                                        <x-status-badge variant="info" style="min-width: 90px; text-align: center;">
                                            {{ $dia['total_formateado'] }}
                                        </x-status-badge>
                                    @endif
                                    <x-status-badge variant="neutral" style="min-width: 90px; text-align: center;">
                                        {{ count($dia['registros']) }} {{ count($dia['registros']) == 1 ? 'fichaje' : 'fichajes' }}
                                    </x-status-badge>
                                    <md-icon class="expand-icon">expand_more</md-icon>
                                </summary>
                                <div class="collapsible-content">
                                    {{-- Mobile view: List --}}
                                    <div class="hide-on-med-and-up">
                                        @foreach($dia['registros'] as $registro)
                                        <div class="fichaje-mini-card" style="{{ !empty($registro['abierto']) ? 'flex-wrap: wrap;' : '' }}">
                                            <div class="fichaje-mini-times">
                                                <div class="fichaje-mini-time">
                                                    <md-icon class="text-claude">login</md-icon>
                                                    <span>{{ $registro['entrada'] }}</span>
                                                </div>
                                                <md-icon class="fichaje-mini-arrow">arrow_forward</md-icon>
                                                <div class="fichaje-mini-time">
                                                    @if(!empty($registro['abierto']))
                                                        <md-icon style="color: var(--warning);">schedule</md-icon>
                                                        <span style="color: var(--warning);">Abierto</span>
                                                    @else
                                                        <md-icon style="color: var(--error);">logout</md-icon>
                                                        <span>{{ $registro['salida'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="fichaje-mini-duration">
                                                @if(!empty($registro['abierto']))
                                                    <span class="status-badge status-badge-warning live-timer"
                                                          data-start-time="{{ $registro['entrada_timestamp'] }}">
                                                        {{ $registro['duracion'] }}
                                                    </span>
                                                @else
                                                    <x-status-badge variant="success">
                                                        {{ $registro['duracion'] }}
                                                    </x-status-badge>
                                                @endif
                                            </div>
                                            @if(!empty($registro['abierto']))
                                            <div style="width: 100%; margin-top: 12px;">
                                                <form action="{{ route('registro_horario.salida') }}" method="POST">
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
                                        <table class="striped">
                                            <thead>
                                                <tr>
                                                    <th>Entrada</th>
                                                    <th>Salida</th>
                                                    <th>Duración</th>
                                                    <th class="right-align">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dia['registros'] as $registro)
                                                <tr>
                                                    <td>
                                                        <md-icon class="text-claude" style="font-size: 14px;">login</md-icon>
                                                        {{ $registro['entrada'] }}
                                                    </td>
                                                    <td>
                                                        @if(!empty($registro['abierto']))
                                                            <span style="color: var(--warning);">
                                                                <md-icon style="font-size: 14px;">schedule</md-icon> Abierto
                                                            </span>
                                                        @else
                                                            <md-icon style="font-size: 14px; color: var(--error);">logout</md-icon>
                                                            {{ $registro['salida'] }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($registro['abierto']))
                                                            <span class="status-badge status-badge-warning live-timer"
                                                                  data-start-time="{{ $registro['entrada_timestamp'] }}">
                                                                <md-icon style="font-size: 14px;">timer</md-icon>
                                                                {{ $registro['duracion'] }}
                                                            </span>
                                                        @else
                                                            <x-status-badge variant="success" icon="timer">
                                                                {{ $registro['duracion'] }}
                                                            </x-status-badge>
                                                        @endif
                                                    </td>
                                                    <td class="right-align">
                                                        @if(!empty($registro['abierto']))
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
                        @endforeach
                    </ul>
                @else
                    <x-empty-state icon="event_busy" title="Sin fichajes cerrados" description="Aún no tienes registros de fichajes completados para mostrar en el resumen diario." />
                @endif
            </div>
        </div>
    </div>
</div>
