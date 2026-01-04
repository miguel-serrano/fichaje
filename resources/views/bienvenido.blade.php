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
                <p class="grey-text" style="text-align: justify; margin-bottom: 25px;">
                    Esta aplicación se encuentra actualmente en <strong>versión beta</strong>.
                    Al utilizar este servicio, aceptas que el uso de la aplicación es bajo tu propia responsabilidad.
                    No nos hacemos responsables de posibles errores, pérdida de datos o cualquier inconveniente
                    derivado del uso de esta versión de prueba.
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
