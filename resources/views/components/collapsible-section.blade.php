@props(['icon' => null, 'title'])

<details {{ $attributes }}>
    <summary class="collapsible-header">
        @if($icon)
            <md-icon>{{ $icon }}</md-icon>
        @endif
        <span style="flex: 1;">{{ $title }}</span>
        {{ $badges ?? '' }}
        <md-icon class="expand-icon">expand_more</md-icon>
    </summary>
    <div class="collapsible-content">
        {{ $slot }}
    </div>
</details>
