<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Horario</title>
</head>
<body>
    <h1>Registro horario</h1>
    <form method="POST" action="{{ route('registro_horario.fichar') }}">
        @csrf
        <button type="submit">Fichar entrada/salida</button>
    </form>
    <p>Tiempo acumulado hoy: <strong>{{ $segundos }}</strong> segundos</p>
</body>
</html>
