<x-mail::message>
# Evento: {{ $eventName }}

- **Agregado:** {{ $aggregateId }}
- **Fecha:** {{ $occurredOn }}

@if (count($fields) > 0)
<x-mail::table>
| Campo | Valor |
|:------|:------|
@foreach ($fields as $field => $value)
| {{ $field }} | {{ $value }} |
@endforeach
</x-mail::table>
@endif

{{ config('app.name') }}
</x-mail::message>
