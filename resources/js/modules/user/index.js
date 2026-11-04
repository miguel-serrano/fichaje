/**
 * User index page module.
 * - Actions menu (activate/deactivate user)
 * - Delete user confirmation dialog
 */

function initActionsMenus() {
    document.querySelectorAll('.user-actions-menu').forEach(function (wrapper) {
        const trigger = wrapper.querySelector('.btn-user-actions');
        const menu = wrapper.querySelector('md-menu');
        const toggleItem = wrapper.querySelector('.btn-toggle-active');
        const toggleForm = wrapper.querySelector('form');

        if (!trigger || !menu) {
            return;
        }

        menu.anchorElement = trigger;
        trigger.addEventListener('click', function () {
            menu.open = !menu.open;
        });

        if (toggleItem && toggleForm) {
            toggleItem.addEventListener('click', function () {
                toggleForm.submit();
            });
        }
    });
}

export function init() {
    initActionsMenus();

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
