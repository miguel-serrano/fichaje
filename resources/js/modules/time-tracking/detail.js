/**
 * Time tracking detail page module (users/detail).
 * - Personal info toggle (show/hide masked data)
 * - Collapsible expand/collapse controls
 * - Live timers for open time entries
 */

import { startLiveTimers } from '../shared/live-timer.js';
import { initCollapsibleControls } from '../shared/collapsible.js';

export function init() {
    initPersonalInfoToggle();
    initCollapsibleControls('daily-collapsible');
    startLiveTimers();
}

function initPersonalInfoToggle() {
    const toggle = document.getElementById('show-full-info');
    const icon = document.getElementById('visibility-icon');

    if (!toggle || !icon) {
        return;
    }

    toggle.addEventListener('change', function () {
        const masked = document.querySelectorAll('.masked-info');
        const full = document.querySelectorAll('.full-info');

        if (this.selected) {
            masked.forEach(function (el) { el.style.display = 'none'; });
            full.forEach(function (el) { el.style.display = 'inline'; });
            icon.textContent = 'visibility';
            icon.style.color = 'var(--claude-primary)';
        } else {
            masked.forEach(function (el) { el.style.display = 'inline'; });
            full.forEach(function (el) { el.style.display = 'none'; });
            icon.textContent = 'visibility_off';
            icon.style.color = 'var(--text-secondary)';
        }
    });
}
