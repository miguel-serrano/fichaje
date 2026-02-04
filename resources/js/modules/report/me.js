/**
 * Report page module - Pie charts for hours and holidays.
 */

export function init() {
    const pageData = window.__pageData || {};

    if (pageData.chartData && pageData.chartData.hasData) {
        initLineChart(pageData.chartData);
    }

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

function initLineChart(chartData) {
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
