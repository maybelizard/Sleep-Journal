

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Load Chart.js only on pages that have chart data
if (document.getElementById('chart-data')) {
    import('./charts.js');
}
