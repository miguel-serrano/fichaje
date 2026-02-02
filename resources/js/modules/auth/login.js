/**
 * Login page module.
 * - Splash screen animation
 * - Material Web field sync
 * - Interactive clock hands on input
 */

import { syncMdFieldsToHiddenInputs } from '../shared/form-field-sync.js';

export function init() {
    const splashScreen = document.getElementById('splash-screen');
    const loginContainer = document.getElementById('login-container');

    // Hide splash and show login after animation
    setTimeout(function () {
        if (splashScreen) {
            splashScreen.classList.add('fade-out');
        }
        if (loginContainer) {
            loginContainer.classList.add('visible');
        }

        // Remove splash from DOM after fade
        setTimeout(function () {
            if (splashScreen) {
                splashScreen.style.display = 'none';
            }
        }, 500);
    }, 2200);

    // Field sync
    syncMdFieldsToHiddenInputs('login-form', ['email', 'password']);

    // Interactive clock hands
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    const hourHand = document.getElementById('hour-hand');
    const minuteHand = document.getElementById('minute-hand');

    let hourAngle = 60;
    let minuteAngle = 180;

    // Enable interactive mode after splash ends
    setTimeout(function () {
        if (hourHand) {
            hourHand.classList.add('interactive');
        }
        if (minuteHand) {
            minuteHand.classList.add('interactive');
        }
    }, 2700);

    if (emailField && hourHand) {
        emailField.addEventListener('input', function () {
            if (hourHand.classList.contains('interactive')) {
                hourAngle += 15;
                hourHand.style.transform = 'rotate(' + hourAngle + 'deg)';
            }
        });
    }

    if (passwordField && minuteHand) {
        passwordField.addEventListener('input', function () {
            if (minuteHand.classList.contains('interactive')) {
                minuteAngle += 30;
                minuteHand.style.transform = 'rotate(' + minuteAngle + 'deg)';
            }
        });
    }
}
