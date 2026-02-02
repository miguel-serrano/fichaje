/**
 * Register page module.
 * - Material Web field sync for registration form
 */

import { syncMdFieldsToHiddenInputs } from '../shared/form-field-sync.js';

export function init() {
    syncMdFieldsToHiddenInputs('register-form', ['name', 'email', 'password', 'password_confirmation']);
}
