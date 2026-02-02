/**
 * Holiday index page module.
 * - Date picker initialization for start/end dates
 * - Form validation
 */

export function init() {
    const startInput = document.getElementById('start_date_input');
    const endInput = document.getElementById('end_date_input');
    const startDisplay = document.getElementById('start_date_display');
    const endDisplay = document.getElementById('end_date_display');
    const startTrigger = document.getElementById('start_date_trigger');
    const endTrigger = document.getElementById('end_date_trigger');

    if (!startTrigger || !endTrigger) {
        return;
    }

    let endPicker = null;

    // Start date picker
    window.initDatePicker(startInput, {
        trigger: startTrigger,
        displayElement: startDisplay,
        minDate: new Date(),
        onSelect: function (date) {
            startDisplay.innerHTML = window.formatDisplayDate(date);
            if (endPicker) {
                endPicker.setMinDate(date);
            }
        }
    });

    // End date picker
    endPicker = window.initDatePicker(endInput, {
        trigger: endTrigger,
        displayElement: endDisplay,
        minDate: new Date(),
        getMinDate: function () {
            if (startInput.value) {
                return new Date(startInput.value);
            }
            return new Date();
        }
    });

    // Set initial values if present (from old() form values)
    const pageData = window.__pageData || {};

    if (pageData.oldStartDate) {
        const oldStartDate = new Date(pageData.oldStartDate);
        startDisplay.innerHTML = window.formatDisplayDate(oldStartDate);
    }

    if (pageData.oldEndDate) {
        const oldEndDate = new Date(pageData.oldEndDate);
        endDisplay.innerHTML = window.formatDisplayDate(oldEndDate);
    }

    // Form validation
    const form = document.getElementById('holiday-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!startInput.value || !endInput.value) {
                e.preventDefault();
                window.toast.error('Por favor selecciona ambas fechas');
                return false;
            }
        });
    }
}
