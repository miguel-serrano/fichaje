import './bootstrap';

// Material Web Components - Import all needed components
import '@material/web/button/filled-button.js';
import '@material/web/button/outlined-button.js';
import '@material/web/button/text-button.js';
import '@material/web/button/filled-tonal-button.js';
import '@material/web/checkbox/checkbox.js';
import '@material/web/dialog/dialog.js';
import '@material/web/divider/divider.js';
import '@material/web/fab/fab.js';
import '@material/web/icon/icon.js';
import '@material/web/iconbutton/icon-button.js';
import '@material/web/iconbutton/filled-icon-button.js';
import '@material/web/iconbutton/filled-tonal-icon-button.js';
import '@material/web/iconbutton/outlined-icon-button.js';
import '@material/web/list/list.js';
import '@material/web/list/list-item.js';
import '@material/web/menu/menu.js';
import '@material/web/menu/menu-item.js';
import '@material/web/progress/linear-progress.js';
import '@material/web/progress/circular-progress.js';
import '@material/web/radio/radio.js';
import '@material/web/select/outlined-select.js';
import '@material/web/select/select-option.js';
import '@material/web/switch/switch.js';
import '@material/web/tabs/tabs.js';
import '@material/web/tabs/primary-tab.js';
import '@material/web/tabs/secondary-tab.js';
import '@material/web/textfield/outlined-text-field.js';
import '@material/web/textfield/filled-text-field.js';

// Toast notifications
import { toast } from './toast.js';

// Flatpickr for date picking (MaterializeCSS datepicker replacement)
import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';

// Configure flatpickr defaults
flatpickr.localize(Spanish);

// Make toast globally available
window.toast = toast;

// Initialize flatpickr on elements with data-flatpickr attribute
document.addEventListener('DOMContentLoaded', () => {
    // Auto-initialize flatpickr
    document.querySelectorAll('[data-flatpickr]').forEach(el => {
        const options = {
            dateFormat: 'Y-m-d',
            allowInput: true,
            ...JSON.parse(el.dataset.flatpickr || '{}')
        };
        flatpickr(el, options);
    });

    // Initialize md-menu anchors
    document.querySelectorAll('md-menu[anchor]').forEach(menu => {
        const anchorId = menu.getAttribute('anchor');
        const anchor = document.getElementById(anchorId);
        if (anchor) {
            menu.anchorElement = anchor;
        }
    });
});
