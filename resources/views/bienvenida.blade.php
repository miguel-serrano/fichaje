@extends('layouts.app')

@section('title', 'Bienvenido')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title center-align">
                    <md-icon class="text-claude" style="font-size: 48px; width: 48px; height: 48px;">waving_hand</md-icon>
                    <br>
                    Bienvenido, {{ Str::ucfirst(auth()->user()->name) }}
                </span>

                @if(!auth()->user()->is_active)
                    <div class="card-panel card-panel-warning" style="margin-top: 20px;">
                        <md-icon style="margin-right: 8px;">info</md-icon>
                        <strong>Tu cuenta está pendiente de activación, en breve se activará.</strong>
                        <p style="margin-top: 10px; margin-bottom: 0;">
                            Mientras tanto, puedes acceder a la sección de Seguimiento.
                        </p>
                    </div>
                @endif

                <div class="divider" style="margin: 30px 0;"></div>

                <h6 class="text-secondary" style="margin-top: 35px; margin-bottom: 24px; font-size: 1.5rem;">Términos y Condiciones</h6>
                <p class="text-secondary" style="text-align: justify; margin-bottom: 15px;">
                    Esta aplicación se encuentra actualmente en <strong>versión beta</strong>, lo que significa que se sigue trabajando activamente para mejorar su rendimiento, estabilidad y seguridad. Se realizan ajustes y optimizaciones continuas con el objetivo de ofrecer una experiencia de uso cada vez más confiable y completa.
                </p>
                <p class="text-secondary" style="text-align: justify; margin-bottom: 15px;">
                    Al utilizar este servicio, aceptas que el uso de la aplicación es bajo tu propia responsabilidad. Aunque se procura garantizar su correcto funcionamiento, pueden producirse errores, interrupciones o pérdida de datos propios de esta etapa de prueba.
                </p>
                <p class="text-secondary" style="text-align: justify; margin-bottom: 15px;">
                    Gracias por tu comprensión y colaboración mientras se avanza hacia una versión final más sólida y segura. En breve, se habilitará una sección de comentarios y sugerencias para que puedas compartir tus opiniones y contribuir a la mejora continua de la aplicación.
                </p>
                <p class="text-secondary" style="text-align: justify; margin-bottom: 15px;">
                    Espero que esta versión sea de utilidad y resulte una buena experiencia mientras se continúa perfeccionando la aplicación.
                </p>

                <h6 class="text-secondary" style="margin-top: 35px; margin-bottom: 24px; font-size: 1.5rem;">Límites de Uso</h6>
                <p class="text-secondary" style="text-align: justify; margin-bottom: 15px;">
                    Para asegurar la estabilidad de la aplicación durante esta etapa inicial, se aplican algunos límites razonables:
                </p>
                <p class="text-secondary" style="margin-bottom: 10px;">
                    <strong>Fichajes diarios:</strong> Máximo de 8 registros por usuario al día
                </p>
                <p class="text-secondary" style="margin-bottom: 15px;">
                    <strong>Nuevos usuarios:</strong> Hasta 8 registros por día
                </p>
                <p class="text-secondary" style="margin-bottom: 15px;">
                    <strong>Cierre automático:</strong> Los fichajes abiertos se cerrarán automáticamente por la noche, computando un máximo de 8 horas diarias
                </p>
                <p class="text-secondary" style="margin-bottom: 15px;">
                    <strong>Vacaciones:</strong> Pide tus días de vacaciones
                </p>
                <p class="text-secondary" style="text-align: justify; margin-bottom: 15px;">
                    Estos límites se ampliarán progresivamente conforme la aplicación evolucione hacia su versión estable.
                </p>

                @if(!auth()->user()->accepted_terms)
                    <div style="display: flex; align-items: center; gap: 16px; margin-top: 24px;">
                        <md-switch id="accept-terms-switch"></md-switch>
                        <label for="accept-terms-switch" class="text-secondary" style="cursor: pointer;">
                            Acepto los términos y condiciones de uso de la versión beta
                        </label>
                    </div>

                    <script>
                        document.getElementById('accept-terms-switch').addEventListener('change', function() {
                            if (this.selected) {
                                fetch('{{ route('bienvenido.accept-terms') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ accepted_terms: true })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        location.reload();
                                    }
                                })
                                .catch(() => {
                                    this.selected = false;
                                    if (window.toast) {
                                        window.toast.error('Error al guardar. Inténtalo de nuevo.');
                                    }
                                });
                            }
                        });
                    </script>
                @else
                    <div class="card-panel card-panel-success" style="margin-top: 20px;">
                        <md-icon style="margin-right: 8px;">check_circle</md-icon>
                        <strong>Has aceptado los términos y condiciones.</strong>
                        <p style="margin-top: 10px; margin-bottom: 0;">
                            Gracias por tu confianza. Ya puedes navegar por la aplicación.
                        </p>
                    </div>
                @endif

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="center-align">
                    <md-text-button href="{{ route('user.me') }}">
                        <md-icon slot="icon">chrome_reader_mode</md-icon>
                        Ir a Seguimiento
                    </md-text-button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
