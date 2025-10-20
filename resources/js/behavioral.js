// resources/js/behavioral.js
import Chart from 'chart.js/auto';
import zoomPlugin from 'chartjs-plugin-zoom';
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

Chart.register(zoomPlugin);

// Vite env variables
const PUSHER_KEY = import.meta.env.VITE_PUSHER_APP_KEY;
const PUSHER_CLUSTER = import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1';

// configure axios CSRF
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]').getAttribute('content');

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'pusher',
  key: PUSHER_KEY,
  cluster: PUSHER_CLUSTER,
  forceTLS: true,
});

const ctx = document.getElementById('behavior-chart')?.getContext('2d');
let behaviorChart = null;

function createChart() {
  const gradient = ctx.createLinearGradient(0, 0, 0, 300);
  gradient.addColorStop(0, 'rgba(116, 26, 172, 0.4)');
  gradient.addColorStop(1, 'rgba(116, 26, 172, 0)');

  behaviorChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        {
          label: 'Water Consumption',
          data: [],
          borderColor: '#741aac',
          backgroundColor: gradient,
          borderWidth: 3,
          pointBackgroundColor: '#9b6ade',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#741aac',
          pointHoverBorderColor: '#fff',
          pointRadius: 5,
          pointHoverRadius: 7,
          fill: true,
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        zoom: {
          pan: {
            enabled: true,
            mode: 'x',         // allow horizontal panning
          },
          zoom: {
            wheel: {
              enabled: true,
            },
            mode: 'x',
          },
        },
        legend: {
          display: true,
          labels: {
            color: '#333',
            font: { weight: 'bold' },
          },
        },
        title: {
          display: true,
          text: 'Consumer Behavior Trends',
          color: '#741aac',
          font: { size: 18, weight: 'bold' },
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleFont: { size: 14 },
          bodyFont: { size: 13 },
          padding: 10,
          displayColors: false,
          callbacks: {
            label: (context) => `Value: ${context.parsed.y}`,
          },
        },
      },
      scales: {
        x: {
          ticks: {
            color: '#741aac',
            font: { size: 12 },
          },
          grid: { color: 'rgba(200,200,200,0.2)' },
          title: {
            display: true,
            text: 'Time',
            color: '#741aac',
            font: { weight: 'bold' },
          },
        },
        y: {
          beginAtZero: true,
          ticks: {
            color: '#741aac',
            font: { size: 12 },
          },
          grid: { color: 'rgba(200,200,200,0.2)' },
          title: {
            display: true,
            text: 'Consumption (m³)',
            color: '#741aac',
            font: { weight: 'bold' },
          },
        },
      },
    },
  });
}

function addPoint(label, value, maxPoints = 200) {
  if (!behaviorChart) return;
  behaviorChart.data.labels.push(label);
  behaviorChart.data.datasets[0].data.push(value);
  if (behaviorChart.data.labels.length > maxPoints) {
    behaviorChart.data.labels.shift();
    behaviorChart.data.datasets[0].data.shift();
  }
  behaviorChart.update();
}

async function loadData() {
  const params = {
    year: document.getElementById('filter-year')?.value || '',
    month: document.getElementById('filter-month')?.value || '',
    week: document.getElementById('filter-week')?.value || '',
    day: document.getElementById('filter-day')?.value || '',
    barangay: document.getElementById('filter-barangay')?.value || '',
    consumer: document.getElementById('filter-consumer')?.value || '',
  };

  try {
    const res = await axios.get('/admin/behavior/data', { params });
    behaviorChart.data.labels = [];
    behaviorChart.data.datasets[0].data = [];
    res.data.forEach((r) => {
      behaviorChart.data.labels.push(new Date(r.created_at).toLocaleString());
      behaviorChart.data.datasets[0].data.push(r.value);
    });
    behaviorChart.update();
  } catch (err) {
    console.error('Load data failed', err);
  }
}

function initFeedButton() {
  const btn = document.getElementById('feed-random');
  if (!btn) return;

  btn.addEventListener('click', async () => {
    const randomValue = Math.floor(Math.random() * 500);
    try {
      const res = await axios.post('/admin/behavior/data', {
        metric_name: 'manual',
        value: randomValue,
      });
      const row = res.data;
      addPoint(new Date(row.created_at).toLocaleTimeString(), row.value);
    } catch (err) {
      console.error(err);
      alert('Failed to post random data');
    }
  });
}

function initEchoListener() {
  echo.channel('sensor-data').listen('SensorDataReceived', (payload) => {
    try {
      const label = new Date(payload.created_at).toLocaleTimeString();
      addPoint(label, Number(payload.value));
    } catch (e) {
      console.warn('Malformed sensor payload', payload);
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  if (!ctx) return;
  createChart();
  loadData();
  initFeedButton();
  initEchoListener();

  ['filter-year', 'filter-month', 'filter-week', 'filter-day', 'filter-barangay', 'filter-consumer']
    .forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('change', loadData);
    });
});
