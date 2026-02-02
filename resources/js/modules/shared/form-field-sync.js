/**
 * Sincroniza campos Material Web (md-outlined-text-field, md-outlined-select)
 * con hidden inputs para que los valores se envien correctamente en formularios.
 *
 * Material Web components no usan el atributo `name` nativo de HTML,
 * por lo que necesitamos sincronizar sus valores a inputs hidden.
 */

/**
 * @param {string} formId - ID del formulario
 * @param {string[]} fieldNames - Array de nombres de campos a sincronizar
 */
export function syncMdFieldsToHiddenInputs(formId, fieldNames) {
    const form = document.getElementById(formId);
    if (!form) {
        return;
    }

    // Sincronizacion en tiempo real
    fieldNames.forEach(function (fieldName) {
        const field = document.getElementById(fieldName);
        const hidden = form.querySelector('input[name="' + fieldName + '"][type="hidden"]');

        if (field && hidden) {
            field.addEventListener('input', function () {
                hidden.value = field.value;
            });
            // Sincronizar valor inicial (por si hay old() values)
            hidden.value = field.value;
        }
    });

    // Sincronizacion al enviar (crea hidden inputs si no existen)
    form.addEventListener('submit', function () {
        fieldNames.forEach(function (fieldName) {
            const field = document.getElementById(fieldName);
            if (field && field.value !== undefined) {
                let hiddenInput = form.querySelector('input[name="' + fieldName + '"][type="hidden"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = fieldName;
                    form.appendChild(hiddenInput);
                }
                hiddenInput.value = field.value;
            }
        });
    });
}
