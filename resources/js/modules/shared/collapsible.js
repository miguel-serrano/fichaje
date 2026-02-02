/**
 * Control de expand/collapse para secciones colapsables
 * basadas en el elemento HTML <details>.
 */

/**
 * Expande todos los <details> dentro de un contenedor.
 * @param {string} containerId - ID del contenedor
 */
export function expandAll(containerId) {
    document.querySelectorAll('#' + containerId + ' details').forEach(function (details) {
        details.open = true;
    });
}

/**
 * Colapsa todos los <details> dentro de un contenedor.
 * @param {string} containerId - ID del contenedor
 */
export function collapseAll(containerId) {
    document.querySelectorAll('#' + containerId + ' details').forEach(function (details) {
        details.open = false;
    });
}

/**
 * Registra funciones globales expandAll/collapseAll para uso en onclick de Blade.
 * @param {string} containerId - ID del contenedor por defecto
 */
export function initCollapsibleControls(containerId) {
    window.expandAll = function (id) {
        expandAll(id || containerId);
    };
    window.collapseAll = function (id) {
        collapseAll(id || containerId);
    };
}
