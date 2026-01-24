/**
 * Toast Notification System
 * Custom implementation for Material Web migration
 */

class ToastManager {
    constructor() {
        this.container = null;
        this.queue = [];
        this.isShowing = false;
        this.init();
    }

    init() {
        // Create toast container
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        this.container.style.cssText = `
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: center;
            pointer-events: none;
        `;
        document.body.appendChild(this.container);
    }

    /**
     * Show a toast notification
     * @param {string} message - The message to display
     * @param {Object} options - Configuration options
     * @param {number} options.duration - Duration in ms (default: 4000)
     * @param {string} options.type - Toast type: 'default', 'success', 'error', 'warning', 'info'
     * @param {string} options.actionText - Optional action button text
     * @param {Function} options.actionCallback - Optional action callback
     */
    show(message, options = {}) {
        const {
            duration = 4000,
            type = 'default',
            actionText = null,
            actionCallback = null
        } = options;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border-radius: 8px;
            min-width: 280px;
            max-width: 560px;
            font-size: 0.875rem;
            line-height: 1.4;
            pointer-events: auto;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.2s ease, transform 0.2s ease;
            background-color: var(--md-sys-color-surface-container-highest, #3b3a38);
            color: var(--md-sys-color-on-surface, #e7e2d9);
            border: 1px solid var(--md-sys-color-outline-variant, #52443c);
        `;

        // Apply type-specific styles
        const typeStyles = {
            success: {
                background: 'var(--success-bg, #d4e4c4)',
                color: 'var(--success, #788c5d)',
                border: 'var(--success, #788c5d)'
            },
            error: {
                background: 'var(--error-bg, #f5d4d0)',
                color: 'var(--error, #c54a3a)',
                border: 'var(--error, #c54a3a)'
            },
            warning: {
                background: 'var(--warning-bg, #ffedc8)',
                color: 'var(--warning, #d4a248)',
                border: 'var(--warning, #d4a248)'
            },
            info: {
                background: 'var(--info-bg, #cce4f5)',
                color: 'var(--info, #6a9bcc)',
                border: 'var(--info, #6a9bcc)'
            }
        };

        if (typeStyles[type]) {
            toast.style.backgroundColor = typeStyles[type].background;
            toast.style.color = typeStyles[type].color;
            toast.style.borderColor = typeStyles[type].border;
        }

        // Message content
        const messageSpan = document.createElement('span');
        messageSpan.textContent = message;
        messageSpan.style.flex = '1';
        toast.appendChild(messageSpan);

        // Action button (optional)
        if (actionText) {
            const actionBtn = document.createElement('button');
            actionBtn.textContent = actionText;
            actionBtn.style.cssText = `
                background: none;
                border: none;
                padding: 4px 8px;
                margin: -4px -8px -4px 0;
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--claude-primary, #da7756);
                cursor: pointer;
                border-radius: 4px;
                transition: background-color 0.2s;
            `;
            actionBtn.addEventListener('mouseenter', () => {
                actionBtn.style.backgroundColor = 'rgba(218, 119, 86, 0.1)';
            });
            actionBtn.addEventListener('mouseleave', () => {
                actionBtn.style.backgroundColor = 'transparent';
            });
            actionBtn.addEventListener('click', () => {
                if (actionCallback) actionCallback();
                this.dismiss(toast);
            });
            toast.appendChild(actionBtn);
        }

        this.container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => this.dismiss(toast), duration);
        }

        return toast;
    }

    dismiss(toast) {
        if (!toast || !toast.parentNode) return;

        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 200);
    }

    success(message, options = {}) {
        return this.show(message, { ...options, type: 'success' });
    }

    error(message, options = {}) {
        return this.show(message, { ...options, type: 'error' });
    }

    warning(message, options = {}) {
        return this.show(message, { ...options, type: 'warning' });
    }

    info(message, options = {}) {
        return this.show(message, { ...options, type: 'info' });
    }
}

// Create global instance
const toast = new ToastManager();

// Export for module usage
export { toast, ToastManager };

// Also attach to window for non-module scripts
if (typeof window !== 'undefined') {
    window.toast = toast;
}
