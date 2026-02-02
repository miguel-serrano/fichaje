/**
 * User index page module.
 * - Delete user confirmation dialog
 */

export function init() {
    const dialog = document.getElementById('delete-user-dialog');
    const deleteButtons = document.querySelectorAll('.btn-delete-user');
    const deleteForm = document.getElementById('delete-user-form');
    const deleteUserName = document.getElementById('delete-user-name');
    const cancelBtn = document.getElementById('cancel-delete-btn');

    const pageData = window.__pageData || {};

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;

            if (deleteForm && pageData.deleteBaseUrl) {
                deleteForm.action = pageData.deleteBaseUrl + '/' + userId;
            }
            if (deleteUserName) {
                deleteUserName.textContent = userName;
            }
            if (dialog) {
                dialog.open = true;
            }
        });
    });

    if (cancelBtn && dialog) {
        cancelBtn.addEventListener('click', function () {
            dialog.open = false;
        });
    }

    if (dialog) {
        dialog.addEventListener('close', function () {
            dialog.open = false;
        });
    }
}
