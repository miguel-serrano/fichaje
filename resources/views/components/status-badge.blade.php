@props(['variant', 'icon' => null])

<span class="status-badge status-badge-{{ $variant }}" {{ $attributes }}>
    @if($icon)
        <md-icon style="font-size: 14px;">{{ $icon }}</md-icon>
    @endif
    {{ $slot }}
</span>
