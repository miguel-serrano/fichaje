<!-- Información Personal (Collapsible) -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content" style="padding: 0;">
                <details class="personal-info-details">
                    <summary class="collapsible-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; cursor: pointer; list-style: none;">
                        <span class="card-title" style="margin: 0; display: flex; align-items: center;">
                            <md-icon style="margin-right: 8px;">person</md-icon>
                            Información personal
                            <md-icon class="collapse-icon" style="margin-left: 8px; transition: transform 0.3s;">expand_more</md-icon>
                        </span>
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;" onclick="event.stopPropagation();">
                            <md-switch id="show-full-info"></md-switch>
                            <md-icon id="visibility-icon" style="color: var(--text-secondary);">visibility_off</md-icon>
                        </label>
                    </summary>
                    <div style="padding: 0 24px 20px;">
                        <div class="divider" style="margin-bottom: 20px;"></div>
                        <div class="row" style="margin-bottom: 0;">
                            <div class="col s12 m6">
                                <h6 class="text-secondary">Nombre</h6>
                                <p>{{ Str::ucfirst($user->name()) }}</p>
                            </div>
                            <div class="col s12 m6">
                                <h6 class="text-secondary">Email</h6>
                                <p>
                                    <span class="masked-info">{{ Str::mask($user->email()->value(), '*', 3, strpos($user->email()->value(), '@') - 3) }}</span>
                                    <span class="full-info" style="display: none;">{{ $user->email()->value() }}</span>
                                </p>
                            </div>
                            <div class="col s12 m6">
                                <h6 class="text-secondary">UUID</h6>
                                <p>
                                    <code class="text-secondary masked-info">{{ Str::limit($user->uuid()->value(), 18) }}</code>
                                    <code class="text-secondary full-info" style="display: none;">{{ $user->uuid()->value() }}</code>
                                </p>
                            </div>
                            <div class="col s12 m6">
                                <h6 class="text-secondary">Estado</h6>
                                <p>
                                    @if($user->isActive())
                                        <x-status-badge variant="success" icon="check_circle">Activo</x-status-badge>
                                    @else
                                        <x-status-badge variant="error" icon="cancel">Inactivo</x-status-badge>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>
