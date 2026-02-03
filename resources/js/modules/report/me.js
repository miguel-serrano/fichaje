/**
 * Report page module - Pie charts for hours and holidays.
 */

export function init() {
    const pageData = window.__pageData || {};

    initPieChart(
        'hoursChart',
        'Horas del Mes',
        pageData.hoursWorked || 0,
        pageData.hoursTarget || 160,
        'h'
    );

    initPieChart(
        'holidaysChart',
        'Vacaciones',
        pageData.approvedDays || 0,
        pageData.holidaysTarget || 22,
        ' días'
    );
}

function initPieChart(canvasId, title, used, total, unit) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || typeof Chart === 'undefined') {
        return;
    }

    const remaining = Math.max(0, total - used);

    function getThemeColors() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const style = getComputedStyle(document.documentElement);
        return {
            isDark,
            textColor: style.getPropertyValue('--text-primary').trim() || (isDark ? 'rgba(255, 255, 255, 0.87)' : 'rgba(0, 0, 0, 0.87)'),
            primaryColor: style.getPropertyValue('--claude-primary').trim() || '#0336FF',
            tooltipBg: isDark ? 'rgba(30, 30, 30, 0.95)' : 'rgba(255, 255, 255, 0.95)',
            tooltipText: isDark ? 'rgba(255, 255, 255, 0.87)' : 'rgba(0, 0, 0, 0.87)',
            remainingColor: isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.08)',
            borderColor: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
        };
    }

    let colors = getThemeColors();

    const usedLabel = used >= total ? 'Completado' : 'Usado';
    const remainingLabel = 'Restante';

    const chart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [usedLabel, remainingLabel],
            datasets: [{
                data: [used, remaining],
                backgroundColor: [colors.primaryColor, colors.remainingColor],
                borderColor: [colors.borderColor, colors.borderColor],
                borderWidth: 1,
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: colors.textColor,
                        padding: 16,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 13 },
                    },
                },
                tooltip: {
                    backgroundColor: colors.tooltipBg,
                    titleColor: colors.tooltipText,
                    bodyColor: colors.tooltipText,
                    borderColor: colors.borderColor,
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function (context) {
                            const value = context.parsed;
                            const pct = Math.round((value / total) * 100);
                            return ' ' + value + unit + ' (' + pct + '%)';
                        },
                    },
                },
            },
        },
    });

    // Update chart colors when theme changes
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'data-theme') {
                colors = getThemeColors();
                chart.data.datasets[0].backgroundColor = [colors.primaryColor, colors.remainingColor];
                chart.data.datasets[0].borderColor = [colors.borderColor, colors.borderColor];
                chart.options.plugins.legend.labels.color = colors.textColor;
                chart.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
                chart.options.plugins.tooltip.titleColor = colors.tooltipText;
                chart.options.plugins.tooltip.bodyColor = colors.tooltipText;
                chart.options.plugins.tooltip.borderColor = colors.borderColor;
                chart.update();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
}
