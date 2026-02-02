/**
 * Layout module - Funcionalidad comun del layout principal.
 * Extraido de layouts/app.blade.php
 *
 * - Sidenav mobile (abrir/cerrar)
 * - Menus md-menu (notificaciones, usuario)
 * - Logout (desktop y mobile)
 * - Theme toggle (light/dark)
 * - Notificaciones (marcar como leida)
 */

export function init() {
    // --- Sidenav ---
    const sidenavTrigger = document.getElementById('sidenav-trigger');
    const sidenav = document.getElementById('mobile-nav');
    const sidenavOverlay = document.getElementById('sidenav-overlay');

    function openSidenav() {
        if (sidenav) {
            sidenav.classList.add('open');
        }
        if (sidenavOverlay) {
            sidenavOverlay.classList.add('open');
        }
        document.body.style.overflow = 'hidden';
    }

    function closeSidenav() {
        if (sidenav) {
            sidenav.classList.remove('open');
        }
        if (sidenavOverlay) {
            sidenavOverlay.classList.remove('open');
        }
        document.body.style.overflow = '';
    }

    if (sidenavTrigger) {
        sidenavTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            openSidenav();
        });
    }

    if (sidenavOverlay) {
        sidenavOverlay.addEventListener('click', closeSidenav);
    }

    if (sidenav) {
        sidenav.querySelectorAll('a[href]:not([href="#"])').forEach(function (link) {
            link.addEventListener('click', closeSidenav);
        });
    }

    // --- md-menu initialization ---
    const notificationsTrigger = document.getElementById('notifications-trigger');
    const notificationsMenu = document.getElementById('notifications-menu');
    const userTrigger = document.getElementById('user-trigger');
    const userMenu = document.getElementById('user-menu');

    if (notificationsTrigger && notificationsMenu) {
        notificationsMenu.anchorElement = notificationsTrigger;
        notificationsTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            notificationsMenu.open = !notificationsMenu.open;
        });
    }

    if (userTrigger && userMenu) {
        userMenu.anchorElement = userTrigger;
        userTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            userMenu.open = !userMenu.open;
        });
    }

    // --- Logout ---
    const logoutMenuItem = document.getElementById('logout-menu-item');
    const logoutForm = document.getElementById('logout-form');
    const logoutMobile = document.getElementById('logout-mobile');

    if (logoutMenuItem && logoutForm) {
        logoutMenuItem.addEventListener('click', function () {
            logoutForm.submit();
        });
    }

    if (logoutMobile && logoutForm) {
        logoutMobile.addEventListener('click', function (e) {
            e.preventDefault();
            logoutForm.submit();
        });
    }

    // --- Theme toggle ---
    const themeToggle = document.getElementById('theme-toggle');
    const themeToggleMobile = document.getElementById('theme-toggle-mobile');
    const themeIcon = document.getElementById('theme-icon');
    const themeIconMobile = document.getElementById('theme-icon-mobile');
    const savedTheme = localStorage.getItem('theme') || 'light';

    function updateIcons(theme) {
        const icon = theme === 'dark' ? 'light_mode' : 'dark_mode';
        if (themeIcon) {
            themeIcon.textContent = icon;
        }
        if (themeIconMobile) {
            themeIconMobile.textContent = icon;
        }
    }

    updateIcons(savedTheme);

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        updateIcons(theme);
    }

    function toggleTheme(e) {
        e.preventDefault();
        const currentTheme = localStorage.getItem('theme') || 'light';
        setTheme(currentTheme === 'dark' ? 'light' : 'dark');
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    if (themeToggleMobile) {
        themeToggleMobile.addEventListener('click', toggleTheme);
    }

    // --- Notification clicks ---
    document.querySelectorAll('.notification-item').forEach(function (item) {
        item.addEventListener('click', function () {
            const notificationId = this.dataset.id;

            fetch('/notifications/' + notificationId + '/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(function (response) {
                if (!response.ok) {
                    console.error('Error marcando notificacion como leida:', response.status);
                    return;
                }
                item.remove();
                const badge = document.querySelector('.badge.red');
                if (badge) {
                    const count = parseInt(badge.textContent) - 1;
                    if (count <= 0) {
                        badge.remove();
                    } else {
                        badge.textContent = count;
                    }
                }
            }).catch(function (error) {
                console.error('Error de red:', error);
            });
        });
    });
}
