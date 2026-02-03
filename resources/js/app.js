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

// Material Date Picker
import { MaterialDatePicker, initDatePicker, formatDisplayDate } from './date-picker.js';

// Chart.js for data visualization
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// Make utilities globally available
window.toast = toast;
window.Chart = Chart;
window.MaterialDatePicker = MaterialDatePicker;
window.initDatePicker = initDatePicker;
window.formatDisplayDate = formatDisplayDate;

// Initialize md-menu anchors
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('md-menu[anchor]').forEach(menu => {
        const anchorId = menu.getAttribute('anchor');
        const anchor = document.getElementById(anchorId);
        if (anchor) {
            menu.anchorElement = anchor;
        }
    });
});

// Layout module (sidenav, theme, logout, notifications)
import('./modules/layout.js').then(m => m.init());

// Page-specific module loading via data-page attribute on <body>
document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.dataset.page;
    if (!page) {
        return;
    }

    const pageModules = {
        'auth.login': () => import('./modules/auth/login.js'),
        'auth.register': () => import('./modules/auth/register.js'),
        'user.index': () => import('./modules/user/index.js'),
        'user.show': () => import('./modules/user/show.js'),
        'user.detail': () => import('./modules/time-tracking/detail.js'),
        'holiday.index': () => import('./modules/holiday/index.js'),
        'admin.form': () => import('./modules/admin/form-sync.js'),
        'report.me': () => import('./modules/report/me.js'),
    };

    const loader = pageModules[page];
    if (loader) {
        loader().then(m => m.init && m.init());
    }
});
