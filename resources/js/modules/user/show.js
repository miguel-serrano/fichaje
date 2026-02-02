/**
 * User show page module.
 * - Role assignment form sync (md-outlined-select)
 * - Collapsible expand/collapse controls
 */

import { syncMdFieldsToHiddenInputs } from '../shared/form-field-sync.js';
import { initCollapsibleControls } from '../shared/collapsible.js';

export function init() {
    // Role assignment select sync
    syncMdFieldsToHiddenInputs('assign-role-form', ['role_slug']);

    // Collapsible controls
    initCollapsibleControls('daily-collapsible');
}
