/**
 * Time tracking detail page module (users/detail).
 * - Personal info toggle (show/hide masked data)
 * - Collapsible expand/collapse controls
 * - Live timers for open time entries
 * - Chart.js daily hours chart with theme-aware colors
 */

import { startLiveTimers } from '../shared/live-timer.js';
import { initCollapsibleControls } from '../shared/collapsible.js';

export function init() {
    initPersonalInfoToggle();
    initCollapsibleControls('daily-collapsible');
    startLiveTimers();

    const pageData = window.__pageData || {};
    if (pageData.chartData && pageData.chartData.hasData) {
        initChart(pageData.chartData);
    }
}

function initPersonalInfoToggle() {
    const toggle = document.getElementById('show-full-info');
    const icon = document.getElementById('visibility-icon');

    if (!toggle || !icon) {
        return;
    }

    toggle.addEventListener('change', function () {
        const masked = document.querySelectorAll('.masked-info');
        const full = document.querySelectorAll('.full-info');

        if (this.selected) {
            masked.forEach(function (el) { el.style.display = 'none'; });
            full.forEach(function (el) { el.style.display = 'inline'; });
            icon.textContent = 'visibility';
            icon.style.color = 'var(--claude-primary)';
        } else {
            masked.forEach(function (el) { el.style.display = 'inline'; });
            full.forEach(function (el) { el.style.display = 'none'; });
            icon.textContent = 'visibility_off';
            icon.style.color = 'var(--text-secondary)';
        }
    });
}

function initChart(chartData) {
    const ctx = document.getElementById('dailyHoursChart');
    if (!ctx || typeof Chart === 'undefined') {
        return;
    }

    function getThemeColors() {
        const style = getComputedStyle(document.documentElement);
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            isDark,
            textColor: style.getPropertyValue('--text-primary').trim() || (isDark ? 'rgba(255, 255, 255, 0.87)' : 'rgba(0, 0, 0, 0.87)'),
            gridColor: style.getPropertyValue('--border-color').trim() || (isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.12)'),
            primaryColor: style.getPropertyValue('--claude-primary').trim() || '#0336FF',
            tooltipBg: isDark ? 'rgba(30, 30, 30, 0.95)' : 'rgba(255, 255, 255, 0.95)',
            tooltipText: isDark ? 'rgba(255, 255, 255, 0.87)' : 'rgba(0, 0, 0, 0.87)',
            pointBorder: isDark ? '#1E1E1E' : '#FFFFFF'
        };
    }

    let colors = getThemeColors();

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Horas trabajadas',
                data: chartData.data,
                borderColor: colors.primaryColor,
                backgroundColor: colors.primaryColor + '20',
                borderWidth: 2,
                fill: true,
                tension: 0,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: colors.primaryColor,
                pointBorderColor: colors.pointBorder,
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: colors.tooltipBg,
                    titleColor: colors.tooltipText,
                    bodyColor: colors.tooltipText,
                    borderColor: colors.gridColor,
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        title: function (tooltipItems) {
                            return tooltipItems[0].label;
                        },
                        label: function (context) {
                            const hours = context.parsed.y;
                            const h = Math.floor(hours);
                            const m = Math.round((hours - h) * 60);
                            return h + 'h ' + m + 'm';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: colors.gridColor, drawBorder: false },
                    ticks: { color: colors.textColor, maxRotation: 45, minRotation: 0, autoSkip: true, maxTicksLimit: 15 }
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: 10,
                    grid: { color: colors.gridColor, drawBorder: false },
                    ticks: { color: colors.textColor, callback: function (value) { return value + 'h'; } }
                }
            }
        }
    });

    // Update chart colors when theme changes
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'data-theme') {
                colors = getThemeColors();
                chart.data.datasets[0].borderColor = colors.primaryColor;
                chart.data.datasets[0].backgroundColor = colors.primaryColor + '20';
                chart.data.datasets[0].pointBackgroundColor = colors.primaryColor;
                chart.data.datasets[0].pointBorderColor = colors.pointBorder;
                chart.options.scales.x.grid.color = colors.gridColor;
                chart.options.scales.x.ticks.color = colors.textColor;
                chart.options.scales.y.grid.color = colors.gridColor;
                chart.options.scales.y.ticks.color = colors.textColor;
                chart.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
                chart.options.plugins.tooltip.titleColor = colors.tooltipText;
                chart.options.plugins.tooltip.bodyColor = colors.tooltipText;
                chart.options.plugins.tooltip.borderColor = colors.gridColor;
                chart.update();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
}
