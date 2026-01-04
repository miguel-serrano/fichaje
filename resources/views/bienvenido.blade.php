@extends('layouts.app')

@section('title', 'Bienvenido')

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title center-align">
                    <i class="material-icons light-green-text text-darken-3" style="font-size: 48px;">waving_hand</i>
                    <br>
                    Bienvenido, {{ Str::ucfirst(auth()->user()->name) }}
                </span>

                @if(!auth()->user()->is_active)
                    <div class="card-panel amber lighten-4 amber-text text-darken-4" style="margin-top: 20px;">
                        <i class="material-icons left">info</i>
                        <strong>Tu cuenta está pendiente de activación.</strong>
                        <p style="margin-top: 10px; margin-bottom: 0;">
                            Un administrador debe activar tu cuenta antes de que puedas utilizar la funcionalidad de Fichar.
                            Mientras tanto, puedes acceder a la sección de Seguimiento.
                        </p>
                    </div>
                @endif

                <div class="divider" style="margin: 30px 0;"></div>

                <h6 class="grey-text text-darken-2">Términos y Condiciones</h6>
                <p class="grey-text" style="text-align: justify; margin-bottom: 15px;">
                    Esta aplicación se encuentra actualmente en <strong>versión beta</strong>, lo que significa que se sigue trabajando activamente para mejorar su rendimiento, estabilidad y seguridad. Se realizan ajustes y optimizaciones continuas con el objetivo de ofrecer una experiencia de uso cada vez más confiable y completa.
                </p>
                <p class="grey-text" style="text-align: justify; margin-bottom: 15px;">
                    Al utilizar este servicio, aceptas que el uso de la aplicación es bajo tu propia responsabilidad. Aunque se procura garantizar su correcto funcionamiento, pueden producirse errores, interrupciones o pérdida de datos propios de esta etapa de prueba.
                </p>
                <p class="grey-text" style="text-align: justify; margin-bottom: 15px;">
                    Gracias por tu comprensión y colaboración mientras se avanza hacia una versión final más sólida y segura. En breve, se habilitará una sección de comentarios y sugerencias para que puedas compartir tus opiniones y contribuir a la mejora continua de la aplicación.
                </p>
                <p class="grey-text" style="text-align: justify; margin-bottom: 15px;">
                    Espero que esta versión sea de utilidad y resulte una buena experiencia mientras se continúa perfeccionando la aplicación.
                </p>

                <h6 class="grey-text text-darken-2" style="margin-top: 25px;">Límites de Uso</h6>
                <p class="grey-text" style="text-align: justify; margin-bottom: 15px;">
                    <i class="material-icons tiny" style="vertical-align: middle;">schedule</i>
                    Cada usuario puede registrar un máximo de <strong>8 fichajes por día</strong>. Este límite ha sido establecido para garantizar un uso razonable de la aplicación durante la fase beta.
                </p>

                <p class="grey-text text-lighten-1" style="font-size: 13px; margin-bottom: 25px;">
                    <i class="material-icons tiny" style="vertical-align: middle;">verified</i>
                    {{ \App\Helpers\TestCounter::count() }} tests y subiendo
                </p>

                @if(!auth()->user()->accepted_terms)
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="switch">
                            <label>
                                <input type="checkbox" id="accept-terms-switch">
                                <span class="lever"></span>
                            </label>
                        </div>
                        <span class="grey-text text-darken-1">Acepto los términos y condiciones de uso de la versión beta</span>
                    </div>

                    <style>
                        .switch label input[type=checkbox]:checked + .lever { background-color: #a5d6a7; }
                        .switch label input[type=checkbox]:checked + .lever:after { background-color: #388e3c; }
                    </style>

                    <script>
                        document.getElementById('accept-terms-switch').addEventListener('change', function() {
                            if (this.checked) {
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
                                    this.checked = false;
                                    M.toast({html: 'Error al guardar. Inténtalo de nuevo.'});
                                });
                            }
                        });
                    </script>
                @else
                    <div class="card-panel green lighten-4 green-text text-darken-4" style="margin-top: 20px;">
                        <i class="material-icons left">check_circle</i>
                        <strong>Has aceptado los términos y condiciones.</strong>
                        <p style="margin-top: 10px; margin-bottom: 0;">
                            Gracias por tu confianza. Ya puedes navegar por la aplicación.
                        </p>
                    </div>
                @endif

                <div class="divider" style="margin: 20px 0;"></div>

                <div class="center-align">
                    <a href="{{ route('user.me') }}" class="btn-flat waves-effect light-green-text text-darken-3">
                        <i class="material-icons left">chrome_reader_mode</i>
                        Ir a Seguimiento
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
