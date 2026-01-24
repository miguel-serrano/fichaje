/**
 * Material Design 2 Date Picker for Mobile
 * Based on https://m2.material.io/components/date-pickers#mobile-pickers
 */

const MONTHS_ES = [
    'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
];

const MONTHS_ES_SHORT = [
    'ene', 'feb', 'mar', 'abr', 'may', 'jun',
    'jul', 'ago', 'sep', 'oct', 'nov', 'dic'
];

const DAYS_ES = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const DAYS_ES_SHORT = ['D', 'L', 'M', 'X', 'J', 'V', 'S'];

class MaterialDatePicker {
    constructor(options = {}) {
        this.selectedDate = options.selectedDate ? new Date(options.selectedDate) : new Date();
        this.viewDate = new Date(this.selectedDate);
        this.minDate = options.minDate ? new Date(options.minDate) : null;
        this.maxDate = options.maxDate ? new Date(options.maxDate) : null;
        this.onSelect = options.onSelect || (() => {});
        this.onCancel = options.onCancel || (() => {});
        this.yearViewActive = false;

        this.dialog = null;
        this.calendarContainer = null;
        this.headerDateDisplay = null;
        this.headerYearDisplay = null;

        this.createDialog();
    }

    createDialog() {
        // Create the dialog element
        this.dialog = document.createElement('md-dialog');
        this.dialog.classList.add('md-date-picker-dialog');

        this.dialog.innerHTML = `
            <div slot="content" class="md-date-picker">
                <div class="md-date-picker-header">
                    <button type="button" class="md-date-picker-year" aria-label="Cambiar año">
                        <span class="year-text"></span>
                        <md-icon>arrow_drop_down</md-icon>
                    </button>
                    <div class="md-date-picker-date"></div>
                </div>
                <div class="md-date-picker-body">
                    <div class="md-date-picker-calendar">
                        <div class="md-date-picker-nav">
                            <button type="button" class="md-date-picker-nav-btn prev" aria-label="Mes anterior">
                                <md-icon>chevron_left</md-icon>
                            </button>
                            <span class="md-date-picker-month-year"></span>
                            <button type="button" class="md-date-picker-nav-btn next" aria-label="Mes siguiente">
                                <md-icon>chevron_right</md-icon>
                            </button>
                        </div>
                        <div class="md-date-picker-weekdays">
                            ${DAYS_ES_SHORT.map(day => `<span class="md-date-picker-weekday">${day}</span>`).join('')}
                        </div>
                        <div class="md-date-picker-days"></div>
                    </div>
                    <div class="md-date-picker-years" style="display: none;">
                        <div class="md-date-picker-years-grid"></div>
                    </div>
                </div>
            </div>
            <div slot="actions">
                <md-text-button class="md-date-picker-cancel">Cancelar</md-text-button>
                <md-text-button class="md-date-picker-ok">OK</md-text-button>
            </div>
        `;

        document.body.appendChild(this.dialog);

        // Cache elements
        this.headerYearDisplay = this.dialog.querySelector('.md-date-picker-year .year-text');
        this.headerDateDisplay = this.dialog.querySelector('.md-date-picker-date');
        this.calendarContainer = this.dialog.querySelector('.md-date-picker-days');
        this.monthYearDisplay = this.dialog.querySelector('.md-date-picker-month-year');
        this.calendarView = this.dialog.querySelector('.md-date-picker-calendar');
        this.yearsView = this.dialog.querySelector('.md-date-picker-years');
        this.yearsGrid = this.dialog.querySelector('.md-date-picker-years-grid');

        // Bind events
        this.bindEvents();
    }

    bindEvents() {
        // Year toggle
        const yearBtn = this.dialog.querySelector('.md-date-picker-year');
        yearBtn.addEventListener('click', () => this.toggleYearView());

        // Navigation
        this.dialog.querySelector('.md-date-picker-nav-btn.prev').addEventListener('click', () => this.previousMonth());
        this.dialog.querySelector('.md-date-picker-nav-btn.next').addEventListener('click', () => this.nextMonth());

        // Cancel button
        this.dialog.querySelector('.md-date-picker-cancel').addEventListener('click', () => {
            this.close();
            this.onCancel();
        });

        // OK button
        this.dialog.querySelector('.md-date-picker-ok').addEventListener('click', () => {
            this.close();
            this.onSelect(this.selectedDate);
        });

        // Day selection
        this.calendarContainer.addEventListener('click', (e) => {
            const dayBtn = e.target.closest('.md-date-picker-day');
            if (dayBtn && !dayBtn.classList.contains('disabled')) {
                const day = parseInt(dayBtn.dataset.day, 10);
                const month = parseInt(dayBtn.dataset.month, 10);
                const year = parseInt(dayBtn.dataset.year, 10);
                this.selectDate(new Date(year, month, day));
            }
        });

        // Year selection
        this.yearsGrid.addEventListener('click', (e) => {
            const yearBtn = e.target.closest('.md-date-picker-year-item');
            if (yearBtn) {
                const year = parseInt(yearBtn.dataset.year, 10);
                this.viewDate.setFullYear(year);
                this.selectedDate.setFullYear(year);
                this.toggleYearView();
                this.render();
            }
        });

        // Close on backdrop click
        this.dialog.addEventListener('cancel', () => {
            this.onCancel();
        });
    }

    toggleYearView() {
        this.yearViewActive = !this.yearViewActive;

        if (this.yearViewActive) {
            this.calendarView.style.display = 'none';
            this.yearsView.style.display = 'block';
            this.renderYears();

            // Scroll to selected year
            requestAnimationFrame(() => {
                const selectedYear = this.yearsGrid.querySelector('.selected');
                if (selectedYear) {
                    selectedYear.scrollIntoView({ block: 'center' });
                }
            });
        } else {
            this.calendarView.style.display = 'block';
            this.yearsView.style.display = 'none';
        }

        // Update arrow icon
        const arrow = this.dialog.querySelector('.md-date-picker-year md-icon');
        arrow.textContent = this.yearViewActive ? 'arrow_drop_up' : 'arrow_drop_down';
    }

    renderYears() {
        const currentYear = this.selectedDate.getFullYear();
        const minYear = this.minDate ? this.minDate.getFullYear() : currentYear - 100;
        const maxYear = this.maxDate ? this.maxDate.getFullYear() : currentYear + 50;

        let html = '';
        for (let year = minYear; year <= maxYear; year++) {
            const isSelected = year === currentYear;
            html += `
                <button type="button" class="md-date-picker-year-item ${isSelected ? 'selected' : ''}" data-year="${year}">
                    ${year}
                </button>
            `;
        }

        this.yearsGrid.innerHTML = html;
    }

    selectDate(date) {
        this.selectedDate = new Date(date);
        this.viewDate = new Date(date);
        this.render();
    }

    previousMonth() {
        this.viewDate.setMonth(this.viewDate.getMonth() - 1);
        this.renderCalendar();
    }

    nextMonth() {
        this.viewDate.setMonth(this.viewDate.getMonth() + 1);
        this.renderCalendar();
    }

    render() {
        this.renderHeader();
        this.renderCalendar();
    }

    renderHeader() {
        // Year display
        this.headerYearDisplay.textContent = this.selectedDate.getFullYear();

        // Date display
        const dayOfWeek = DAYS_ES[this.selectedDate.getDay()];
        const month = MONTHS_ES_SHORT[this.selectedDate.getMonth()];
        const day = this.selectedDate.getDate();

        this.headerDateDisplay.textContent = `${dayOfWeek}, ${day} ${month}`;
    }

    renderCalendar() {
        const year = this.viewDate.getFullYear();
        const month = this.viewDate.getMonth();

        // Update month/year display
        this.monthYearDisplay.textContent = `${MONTHS_ES[month]} ${year}`;

        // Get first day of month and total days
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startDayOfWeek = firstDay.getDay();

        // Get previous month days to show
        const prevMonthLastDay = new Date(year, month, 0).getDate();

        let html = '';

        // Previous month days
        for (let i = startDayOfWeek - 1; i >= 0; i--) {
            const day = prevMonthLastDay - i;
            const prevMonth = month === 0 ? 11 : month - 1;
            const prevYear = month === 0 ? year - 1 : year;
            const date = new Date(prevYear, prevMonth, day);
            const disabled = this.isDateDisabled(date);

            html += `
                <button type="button" class="md-date-picker-day other-month ${disabled ? 'disabled' : ''}"
                        data-day="${day}" data-month="${prevMonth}" data-year="${prevYear}"
                        ${disabled ? 'disabled' : ''}>
                    ${day}
                </button>
            `;
        }

        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const isSelected = this.isSameDate(date, this.selectedDate);
            const isToday = this.isSameDate(date, new Date());
            const disabled = this.isDateDisabled(date);

            let classes = 'md-date-picker-day';
            if (isSelected) classes += ' selected';
            if (isToday) classes += ' today';
            if (disabled) classes += ' disabled';

            html += `
                <button type="button" class="${classes}"
                        data-day="${day}" data-month="${month}" data-year="${year}"
                        ${disabled ? 'disabled' : ''}>
                    ${day}
                </button>
            `;
        }

        // Next month days
        const totalCells = startDayOfWeek + daysInMonth;
        const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);

        for (let day = 1; day <= remainingCells; day++) {
            const nextMonth = month === 11 ? 0 : month + 1;
            const nextYear = month === 11 ? year + 1 : year;
            const date = new Date(nextYear, nextMonth, day);
            const disabled = this.isDateDisabled(date);

            html += `
                <button type="button" class="md-date-picker-day other-month ${disabled ? 'disabled' : ''}"
                        data-day="${day}" data-month="${nextMonth}" data-year="${nextYear}"
                        ${disabled ? 'disabled' : ''}>
                    ${day}
                </button>
            `;
        }

        this.calendarContainer.innerHTML = html;
    }

    isDateDisabled(date) {
        if (this.minDate && date < this.setToMidnight(this.minDate)) {
            return true;
        }
        if (this.maxDate && date > this.setToMidnight(this.maxDate)) {
            return true;
        }
        return false;
    }

    setToMidnight(date) {
        const d = new Date(date);
        d.setHours(0, 0, 0, 0);
        return d;
    }

    isSameDate(date1, date2) {
        return date1.getFullYear() === date2.getFullYear() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getDate() === date2.getDate();
    }

    open() {
        this.yearViewActive = false;
        this.calendarView.style.display = 'block';
        this.yearsView.style.display = 'none';

        const arrow = this.dialog.querySelector('.md-date-picker-year md-icon');
        arrow.textContent = 'arrow_drop_down';

        this.render();
        this.dialog.show();
    }

    close() {
        this.dialog.close();
    }

    setSelectedDate(date) {
        this.selectedDate = date ? new Date(date) : new Date();
        this.viewDate = new Date(this.selectedDate);
    }

    setMinDate(date) {
        this.minDate = date ? new Date(date) : null;
    }

    setMaxDate(date) {
        this.maxDate = date ? new Date(date) : null;
    }

    destroy() {
        if (this.dialog && this.dialog.parentNode) {
            this.dialog.parentNode.removeChild(this.dialog);
        }
    }
}

/**
 * Initialize date picker on input elements
 * @param {HTMLElement} input - The input element to attach the date picker to
 * @param {Object} options - Configuration options
 */
function initDatePicker(input, options = {}) {
    const displayElement = options.displayElement || input;

    const picker = new MaterialDatePicker({
        selectedDate: input.value ? new Date(input.value) : new Date(),
        minDate: options.minDate,
        maxDate: options.maxDate,
        onSelect: (date) => {
            // Format as YYYY-MM-DD for the hidden input
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            input.value = `${year}-${month}-${day}`;

            // Update display element if different from input
            if (displayElement !== input) {
                displayElement.textContent = formatDisplayDate(date);
            } else if (input.tagName === 'MD-OUTLINED-TEXT-FIELD' || input.tagName === 'MD-FILLED-TEXT-FIELD') {
                // Material Web text fields
                input.value = `${year}-${month}-${day}`;
            }

            // Trigger change event
            input.dispatchEvent(new Event('change', { bubbles: true }));

            if (options.onSelect) {
                options.onSelect(date);
            }
        },
        onCancel: options.onCancel
    });

    // Open picker on click/focus
    const trigger = options.trigger || displayElement;
    trigger.addEventListener('click', (e) => {
        e.preventDefault();

        // Update picker with current value
        if (input.value) {
            picker.setSelectedDate(new Date(input.value));
        }

        // Update min/max if they're dynamic
        if (options.getMinDate) {
            picker.setMinDate(options.getMinDate());
        }
        if (options.getMaxDate) {
            picker.setMaxDate(options.getMaxDate());
        }

        picker.open();
    });

    // Prevent keyboard input on mobile (optional)
    if (input.tagName === 'INPUT') {
        input.setAttribute('readonly', 'readonly');
    }

    return picker;
}

/**
 * Format date for display
 */
function formatDisplayDate(date) {
    const day = date.getDate();
    const month = MONTHS_ES_SHORT[date.getMonth()];
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
}

// Export for use in other files
export { MaterialDatePicker, initDatePicker, formatDisplayDate };
