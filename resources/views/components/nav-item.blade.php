@props(['route', 'icon', 'label', 'active' => false])

<li class="{{ $active ? 'active' : '' }}">
    <a href="{{ $route }}">
        <md-icon>{{ $icon }}</md-icon>{{ $label }}
    </a>
</li>
