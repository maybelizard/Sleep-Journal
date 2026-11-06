import Chart from 'chart.js/auto';

const dataEl = document.getElementById('chart-data');

if (dataEl) {
    const data = JSON.parse(dataEl.textContent);

    // Read colours from theme.css
    const css = getComputedStyle(document.documentElement);
    const color = (name) => css.getPropertyValue(name).trim();
    const pink = color('--pink');

    Chart.defaults.color = color('--muted');
    Chart.defaults.borderColor = color('--border');
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;

    const pad = (n) => String(n).padStart(2, '0');

    const clock = (minutes) => {
        const t = ((Math.round(minutes) % 1440) + 1440) % 1440;
        return `${pad(Math.floor(t / 60))}:${pad(t % 60)}`;
    };

    const duration = (hours) => {
        const m = Math.round(hours * 60);
        return `${Math.floor(m / 60)}h ${pad(m % 60)}m`;
    };

    const quality = (v) => (Number.isInteger(v) ? `${v} (${data.qualityLabels[v]})` : v.toFixed(1));

    // Axis in whole hours for the clock-time charts.
    const hourAxis = (values) => {
        const min = Math.floor(Math.min(...values) / 60) * 60;
        let max = Math.ceil(Math.max(...values) / 60) * 60;
        if (max === min) max += 60;
        return { min, max, ticks: { stepSize: 60 } };
    };

    function lineChart(canvasId, values, { label, format, tick, scale = {} }) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || values.length === 0) return;

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label,
                        data: values,
                        borderColor: pink,
                        backgroundColor: pink,
                        borderWidth: 2,
                        tension: 0.25,
                        pointRadius: values.length > 45 ? 0 : 3,
                        pointHoverRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => data.dates[items[0].dataIndex],
                            label: (item) => `${item.dataset.label}: ${format(item.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 8, maxRotation: 0 } },
                    y: { ...scale, ticks: { callback: tick, ...scale.ticks } },
                },
            },
        });
    }

    lineChart('chart-bedtime', data.bedtime, {
        label: 'Bedtime', format: clock, tick: clock, scale: hourAxis(data.bedtime),
    });
    lineChart('chart-waketime', data.waketime, {
        label: 'Wake time', format: clock, tick: clock, scale: hourAxis(data.waketime),
    });
    lineChart('chart-duration', data.duration, {
        label: 'Duration', format: duration, tick: (v) => `${v}h`,
    });
    lineChart('chart-quality', data.quality, {
        label: 'Quality',
        format: quality,
        tick: (v) => (Number.isInteger(v) ? `${v} ${data.qualityLabels[v]}` : ''),
        scale: { min: 1, max: 5, ticks: { stepSize: 1 } },
    });
}