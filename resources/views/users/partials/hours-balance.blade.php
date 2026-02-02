<!-- Balance de Horas -->
@if(isset($totalMes) && count($dailyRegistros) > 0)
@php
    $diasFichados = count($dailyRegistros);
    $segundosEsperados = $diasFichados * 8 * 3600;
    $balanceSegundos = $totalMes['segundos'] - $segundosEsperados;
    $esPositivo = $balanceSegundos >= 0;

    $formatTiempo = function($segundos) {
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;
        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
    };

    $balanceFormateado = ($esPositivo ? '+' : '-') . $formatTiempo(abs($balanceSegundos));
    $esperadoFormateado = $formatTiempo($segundosEsperados);
@endphp
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <md-icon style="margin-right: 8px;">balance</md-icon>
                    Balance de Horas - {{ $totalMes['mes'] }}
                </span>

                <div class="row" style="margin-top: 20px; margin-bottom: 0;">
                    <div class="col s12 m4 center-align" style="margin-bottom: 15px;">
                        <h6 class="text-secondary" style="margin-bottom: 10px;">Trabajadas</h6>
                        <x-status-badge variant="info" style="font-size: 1.2rem; font-weight: 600;">
                            {{ $totalMes['formateado'] }}
                        </x-status-badge>
                    </div>
                    <div class="col s12 m4 center-align" style="margin-bottom: 15px;">
                        <h6 class="text-secondary" style="margin-bottom: 10px;">Esperadas</h6>
                        <x-status-badge variant="neutral" style="font-size: 1.2rem; font-weight: 600;">
                            {{ $esperadoFormateado }}
                        </x-status-badge>
                        <p class="text-secondary" style="margin: 5px 0 0 0; font-size: 0.9rem;">{{ $diasFichados }} {{ $diasFichados == 1 ? 'día' : 'días' }} x 8h</p>
                    </div>
                    <div class="col s12 m4 center-align" style="margin-bottom: 15px;">
                        <h6 class="text-secondary" style="margin-bottom: 10px;">Balance</h6>
                        @if($esPositivo)
                            <x-status-badge variant="success" icon="trending_up" style="font-size: 1.2rem; font-weight: 600;">
                                {{ $balanceFormateado }}
                            </x-status-badge>
                        @else
                            <x-status-badge variant="error" icon="trending_down" style="font-size: 1.2rem; font-weight: 600;">
                                {{ $balanceFormateado }}
                            </x-status-badge>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
