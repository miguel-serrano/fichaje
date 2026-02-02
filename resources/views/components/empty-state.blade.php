@props(['icon', 'title', 'description' => null])

<div class="center-align" style="padding: 60px 20px;">
    <md-icon class="text-secondary" style="font-size: 72px; width: 72px; height: 72px;">{{ $icon }}</md-icon>
    <h5 class="text-secondary">{{ $title }}</h5>
    @if($description)
        <p class="text-secondary">{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
