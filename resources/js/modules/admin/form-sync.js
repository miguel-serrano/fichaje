/**
 * Admin form sync module.
 * Sincroniza campos Material Web con hidden inputs en formularios CRUD de admin.
 * Lee configuracion de window.__pageData: { formId, fieldNames }
 *
 * Tambien soporta el caso especial de checkboxes (roles/show permissions form).
 */

import { syncMdFieldsToHiddenInputs } from '../shared/form-field-sync.js';

export function init() {
    const pageData = window.__pageData || {};

    // Standard text field sync
    if (pageData.formId && pageData.fieldNames && pageData.fieldNames.length) {
        syncMdFieldsToHiddenInputs(pageData.formId, pageData.fieldNames);
    }

    // Checkbox sync for permissions form (roles/show)
    if (pageData.checkboxFormId) {
        initCheckboxSync(pageData.checkboxFormId);
    }
}

function initCheckboxSync(formId) {
    const form = document.getElementById(formId);
    if (!form) {
        return;
    }

    form.addEventListener('submit', function () {
        // Remove old hidden permission inputs
        form.querySelectorAll('input[name="permissions[]"]').forEach(function (input) {
            input.remove();
        });

        // Create hidden inputs for checked checkboxes
        form.querySelectorAll('md-checkbox').forEach(function (checkbox) {
            if (checkbox.checked) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'permissions[]';
                hidden.value = checkbox.dataset.permissionId || checkbox.value;
                form.appendChild(hidden);
            }
        });
    });
}
