/**
 * Live timer para fichajes abiertos.
 * Actualiza contadores en tiempo real cada segundo.
 */

/**
 * Formatea segundos a HH:MM:SS
 * @param {number} seconds
 * @returns {string}
 */
export function formatTime(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return String(h).padStart(2, '0') + ':' +
           String(m).padStart(2, '0') + ':' +
           String(s).padStart(2, '0');
}

/**
 * Actualiza todos los live timers individuales (.live-timer)
 * y totales del dia (.live-timer-total)
 */
export function updateLiveTimers() {
    // Timers individuales
    document.querySelectorAll('.live-timer').forEach(function (el) {
        const startTime = parseInt(el.getAttribute('data-start-time'));
        const now = Math.floor(Date.now() / 1000);
        const seconds = now - startTime;

        // Preservar icono md-icon si existe dentro del elemento
        const icon = el.querySelector('md-icon');
        if (icon) {
            el.innerHTML = '';
            el.appendChild(icon);
            el.appendChild(document.createTextNode(' ' + formatTime(seconds)));
        } else {
            el.textContent = formatTime(seconds);
        }
    });

    // Timers de total diario con fichaje abierto
    document.querySelectorAll('.live-timer-total').forEach(function (el) {
        const startTime = parseInt(el.getAttribute('data-start-time'));
        const baseSeconds = parseInt(el.getAttribute('data-base-seconds')) || 0;
        const now = Math.floor(Date.now() / 1000);
        const secondsFromOpen = now - startTime;

        if (!el.dataset.baseClosed) {
            const openAtLoad = now - startTime;
            el.dataset.baseClosed = baseSeconds - openAtLoad;
        }

        const totalSeconds = parseInt(el.dataset.baseClosed) + secondsFromOpen;
        el.textContent = formatTime(totalSeconds);
    });
}

/**
 * Inicia la actualizacion periodica de live timers si existen en la pagina.
 */
export function startLiveTimers() {
    if (document.querySelector('.live-timer') || document.querySelector('.live-timer-total')) {
        setInterval(updateLiveTimers, 1000);
        updateLiveTimers();
    }
}
