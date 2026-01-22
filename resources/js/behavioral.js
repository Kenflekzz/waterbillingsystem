import Chart from 'chart.js/auto';
import zoomPlugin from 'chartjs-plugin-zoom';
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/* ==================== LEVEL BACKGROUND ==================== */
const levelBackground = {
  id: 'levelBackground',
  beforeDraw(chart, args, options) {
    const { ctx, chartArea } = chart;
    if (!chartArea) return;
    const { left, right, top, bottom } = chartArea;
    const yScale = chart.scales.y;
    const goodLevel = options.good || 150;
    const dangerousLevel = options.dangerous || 500;
    const greenY = yScale.getPixelForValue(goodLevel);
    const redY = yScale.getPixelForValue(dangerousLevel);

    ctx.fillStyle = 'rgba(0, 255, 255, 0.3)';
    ctx.fillRect(left, greenY, right - left, bottom - greenY);

    const middleY = yScale.getPixelForValue((goodLevel + dangerousLevel) / 2);
    ctx.fillStyle = 'rgba(0, 200, 255, 0.2)';
    ctx.fillRect(left, greenY, right - left, middleY - greenY);

    ctx.fillStyle = 'rgba(255, 0, 0, 0.3)';
    ctx.fillRect(left, top, right - left, redY - top);
  }
};

Chart.register(zoomPlugin, levelBackground);

/* ==================== PUSHER ==================== */
const PUSHER_KEY = import.meta.env.VITE_PUSHER_APP_KEY;
const PUSHER_CLUSTER = import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1';
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'pusher',
  key: PUSHER_KEY,
  cluster: PUSHER_CLUSTER,
  forceTLS: true,
});

/* ==================== CHART ==================== */
const ctx = document.getElementById('behavior-chart')?.getContext('2d');
let behaviorChart = null;
let allConsumerData = {};
const STORAGE_KEY = window.IS_USER_PAGE
                  ? 'userConsumption_v2'
                  : 'adminConsumption_v2';

/* ---------------- LOCAL TIMESTAMP FORMAT ---------------- */
function formatLocalTimestamp(dt) {
  return dt.getFullYear() + "-" + (dt.getMonth() + 1).toString().padStart(2, '0') + "-" +
    dt.getDate().toString().padStart(2, '0') + " " +
    dt.getHours().toString().padStart(2, '0') + ":" +
    dt.getMinutes().toString().padStart(2, '0') + ":" +
    dt.getSeconds().toString().padStart(2, '0');
}

/* ==================== STORAGE ==================== */
function saveAllConsumerData() {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(allConsumerData));
  } catch (e) {
    console.warn('Failed to save chart data', e);
  }
}

function loadAllConsumerData() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return false;
    const parsed = JSON.parse(raw);
    if (typeof parsed === 'object' && parsed !== null) {
      allConsumerData = parsed;
      return true;
    }
    return false;
  } catch (e) {
    console.warn('Failed to load chart data', e);
    return false;
  }
}

/* ---------------- WEEK NUMBER ---------------- */
function getWeekNumber(d) {
  // ISO week number: Monday = 0
  const target = new Date(d.valueOf());
  const dayNr = (d.getDay() + 6) % 7; // Monday = 0, Sunday = 6
  target.setDate(target.getDate() - dayNr + 3);
  const firstThursday = new Date(target.getFullYear(), 0, 4);
  const diff = target - firstThursday;
  return 1 + Math.round(diff / (7 * 24 * 60 * 60 * 1000));
}

/* ---------------- MERGE DATA POINT ---------------- */
function addDataPoint(userId, datetime, value, barangay = null) {
  if (!userId) return;
  const cid   = String(userId);
  const d     = new Date(datetime);          // original UTC stamp
  const ts    = d.toISOString();
  const label = d.toLocaleString();

  /* ----- Manila calendar values (UTC+8) ----- */
  const manila = new Date(d.getTime() + 8 * 3600 * 1000); // shift +8 h
  const week   = getISOWeek(manila);      // ISO week, Monday-based
  const day    = manila.getUTCDate();        // 1-31  (already shifted)
  const month  = manila.getUTCMonth() + 1;   // 1-12
  const year   = manila.getUTCFullYear();

  if (!allConsumerData[cid]) allConsumerData[cid] = [];

  const exists = allConsumerData[cid].some(e => e.ts === ts);
  if (!exists) {
    allConsumerData[cid].push({ ts, label, value: Number(value), barangay, week, year, month, day });
    allConsumerData[cid].sort((a, b) => new Date(a.ts) - new Date(b.ts));
    saveAllConsumerData();
  }
}

/* ==================== CHART CREATION ==================== */
function createChart() {
  if (!ctx) return;

  const gradient = ctx.createLinearGradient(0, 0, 0, 300);
  gradient.addColorStop(0, 'rgba(0, 255, 255, 0.4)');
  gradient.addColorStop(1, 'rgba(0, 255, 255, 0.05)');

  behaviorChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [],
      datasets: [{
        label: 'Water Consumption (All Consumers)',
        data: [],
        borderColor: '#00FFFF',
        backgroundColor: gradient,
        borderWidth: 3,
        pointBackgroundColor: '#00FFFF',
        pointBorderColor: '#fff',
        pointRadius: 4,
        fill: true,
        tension: 0.4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 700,
        easing: 'easeInOutQuart'
      },
      plugins: {
        zoom: {
          pan: {
            enabled: true,
            mode: 'x'
          },
          zoom: {
            wheel: {
              enabled: true
            },
            mode: 'x'
          }
        },
        title: {
          display: true,
          text: 'Consumer Water Consumption Pattern',
          color: '#00FFFF',
          font: { size: 18, weight: 'bold' },
        },
        levelBackground: { good: 150, dangerous: 350 },
      },
      scales: {
        x: {
          title: {
            display: true,
            text: 'Time',
            color: '#00FFFF',
            font: { weight: 'bold' }
          }
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Consumption (m³)',
            color: '#00FFFF',
            font: { weight: 'bold' }
          }
        }
      }
    }
  });
}

function smoothUpdate() {
  if (!behaviorChart) return;
  behaviorChart.update({
    duration: 700,
    easing: 'easeInOutCubic'
  });
}

/* ---------------- DISPLAY DATA WITH FILTERS ---------------- */
function updateChartDisplay(consumerId = '') {
  if (!behaviorChart) return;

  const year  = document.getElementById('filter-year')?.value   || '';
  const month = document.getElementById('filter-month')?.value  || '';
  const week  = document.getElementById('filter-week')?.value   || '';
  const day   = document.getElementById('filter-day')?.value    || '';
  const barangay = document.getElementById('filter-barangay')?.value || '';
  const selected = consumerId || '';
  const isAll = selected === '' || selected.toLowerCase() === 'all';

  const records = isAll
    ? Object.values(allConsumerData).flat()
    : (allConsumerData[selected] || []);

  const filtered = records.filter(p => {
    if (year  && p.year  !== Number(year))  return false;
    if (month && p.month !== Number(month)) return false;
    if (week  && p.week  !== Number(week))  return false;
    if (day   && p.day   !== Number(day))   return false;
    if (barangay && p.barangay?.toString() !== barangay) return false;
    return true;
  });

  let merged = [];
  if (isAll) {
    /* ---------- aggregate by timestamp ---------- */
    const sumMap = {};
    filtered.forEach(p => {
      if (!sumMap[p.ts]) sumMap[p.ts] = { ts: p.ts, value: 0 };
      sumMap[p.ts].value += p.value;
    });
    merged = Object.values(sumMap);
  } else {
    merged = filtered;          // single consumer – no aggregation
  }

  /* ---------- always sort by ISO ts ---------- */
  merged.sort((a, b) => new Date(a.ts) - new Date(b.ts));

  /* ---------- feed Chart.js ---------- */
  behaviorChart.data.labels = merged.map(p => formatLocalTimestamp(new Date(p.ts))); // pretty label
  behaviorChart.data.datasets[0].data = merged.map(p => p.value);
  behaviorChart.data.datasets[0].label = isAll
    ? 'Water Consumption (All Consumers)'
    : `Water Consumption (Consumer ${selected})`;

  smoothUpdate();
}

/* ==================== LOAD SERVER DATA ==================== */
async function loadData(consumerId = '') {

   const activeConsumer = consumerId ||
                         document.getElementById('filter-consumer')?.value ||
                         window.DEMO_CONSUMER ||   // blade can echo session here
                         '';
  const params = {
    year: document.getElementById('filter-year')?.value || '',
    month: document.getElementById('filter-month')?.value || '',
    week: document.getElementById('filter-week')?.value || '',
    day: document.getElementById('filter-day')?.value || '',
    barangay: document.getElementById('filter-barangay')?.value || '',
    consumer: consumerId || '',
  };

  try {
    const res = await axios.get('/admin/behavior/data', { params });
    const serverData = res.data || [];
    serverData.forEach(r => {
      const user = r.user_id ?? r.consumer ?? 'unknown';
      addDataPoint(user, r.created_at, Number(r.value), r.barangay ?? null);
    });
    updateChartDisplay(consumerId);
  } catch (err) {
    console.error('Load data failed:', err);
  }
}

function initFeedButton() {
  const btn = document.getElementById('feed-random');
  if (!btn) return; // safety

  btn.addEventListener('click', async () => {
    const consumerId = document.getElementById('feed-consumer')?.value;
    const barangay   = document.getElementById('feed-barangay')?.value;
    const dateStr    = document.getElementById('feed-date')?.value;
    const timeStr    = document.getElementById('feed-time')?.value;

    if (!consumerId || !barangay || !dateStr || !timeStr) {
      alert('Please select consumer, barangay, date and time.');
      return;
    }

    const dt  = new Date(`${dateStr}T${timeStr}`);
    const iso = dt.toISOString();
    const randomValue = Math.floor(Math.random() * 500);

    try {
  /* 1. store the data point */
  const res = await axios.post('/admin/behavior/data', {
    user_id: consumerId,
    value: randomValue,
    metric_name: 'consumption',
    meta: { barangay },
    created_at: iso
  });

  /* 2. remember the consumer for the user side */
  await fetch('/admin/set-demo-consumer', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    body: new URLSearchParams({ consumer_id: consumerId })
  });

  /* 3. continue with local chart update … */
  const row = res.data;
  const createdAt = row.created_at ? new Date(row.created_at) : new Date();
  const finalValue = Number(row.value ?? randomValue);
  addDataPoint(consumerId, createdAt, finalValue, barangay);
  updateChartDisplay(document.getElementById('filter-consumer')?.value || '');

  if (finalValue >= 350) {
    alert(`⚠️ Warning: High consumption (${finalValue} C.U.)`);
  }
} catch (err) {
  console.error('Failed to feed / set demo consumer', err);
  alert('Failed – see console.');
}
  });
}


/* ==================== PUSHER REALTIME LISTENER ==================== */
function initEchoListener() {
  echo.channel('sensor-data').listen('SensorDataReceived', (payload) => {
    try {
      const consumerId = payload.consumer_id || 'unknown';
      const createdAt = payload.created_at ? new Date(payload.created_at) : new Date();
      addDataPoint(consumerId, createdAt, Number(payload.value));

      const selectedConsumer = document.getElementById('filter-consumer')?.value || '';
      updateChartDisplay(selectedConsumer);
    } catch (e) {
      console.warn('Malformed payload', e);
    }
  });
}

function getISOWeek(manilaDate) {
  const d = new Date(Date.UTC(manilaDate.getUTCFullYear(), manilaDate.getUTCMonth(), manilaDate.getUTCDate()));
  const dayNr = (d.getUTCDay() + 6) % 7; // Mon=0 … Sun=6
  d.setUTCDate(d.getUTCDate() - dayNr + 3);
  const firstThu = new Date(Date.UTC(d.getUTCFullYear(), 0, 4));
  return 1 + Math.round((d - firstThu) / 864e5 / 7);
}

function rebuildDayWeekOptions() {
  const weekSel = document.getElementById('filter-week');
  const monthSel = document.getElementById('filter-month');
  const yearSel = document.getElementById('filter-year');
  const daySel = document.getElementById('filter-day');
  if (!weekSel || !monthSel || !yearSel || !daySel) return;

  const y = Number(document.getElementById('filter-year')?.value || new Date().getFullYear());
  const m = Number(document.getElementById('filter-month')?.value || new Date().getMonth() + 1);

  weekSel.innerHTML = '<option value="">All Weeks</option>';

  /* first Monday of the month (or the Monday before) */
  const firstDay = new Date(Date.UTC(y, m - 1, 1));
  const mon1 = new Date(firstDay.getTime() + 8 * 3600 * 1000);
  mon1.setUTCDate(mon1.getUTCDate() - (mon1.getUTCDay() + 6) % 7);

  /* last Sunday of the month (or the Sunday after) */
  const lastDay = new Date(Date.UTC(y, m, 0));
  const sun1 = new Date(lastDay.getTime() + 8 * 3600 * 1000);
  sun1.setUTCDate(sun1.getUTCDate() + (7 - sun1.getUTCDay()) % 7);

  /* ----  quick check  ---- */
  console.log(`Month ${m}-${y}  weeks ${getISOWeek(mon1)} → ${getISOWeek(sun1)}`);

  /* walk week-by-week until we pass the last Sunday */
  let current = new Date(mon1);
  const stop  = new Date(sun1);
  while (current <= stop) {
    const w = getISOWeek(current);
    weekSel.insertAdjacentHTML('beforeend', `<option value="${w}">Week ${w}</option>`);
    current.setUTCDate(current.getUTCDate() + 7); // next Monday
  }

  /* days in month – unchanged */
  const daysInMonth = new Date(y, m, 0).getDate();
  daySel.innerHTML = '<option value="">All Days</option>';
  for (let d = 1; d <= daysInMonth; d++) {
    daySel.insertAdjacentHTML('beforeend', `<option value="${d}">${d}</option>`);
  }
}
/* ==================== INIT ==================== */
document.addEventListener('DOMContentLoaded', () => {
  if (!ctx) return;
  createChart();
  loadAllConsumerData();

  const initialConsumer = document.getElementById('filter-consumer')?.value || 'all';
  updateChartDisplay(initialConsumer);

  loadData(initialConsumer);
  initFeedButton();
  initEchoListener();

  ['filter-year', 'filter-month', 'filter-week', 'filter-day', 'filter-barangay', 'filter-consumer']
    .forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('change', () => {

        if (document.getElementById('filter-date')) return;
        
        const sel = document.getElementById('filter-consumer')?.value || '';
        updateChartDisplay(sel);
      });
    });

  /* ---------- dynamic week / day lists ---------- */
  rebuildDayWeekOptions(); // initial fill
  document.getElementById('filter-year')?.addEventListener('change', rebuildDayWeekOptions);
  document.getElementById('filter-month')?.addEventListener('change', rebuildDayWeekOptions);
});
/* return ISO week (1-53) for a Manila date */

window.allConsumerData = allConsumerData;   // expose for console


/* make them globally available */
window.addDataPoint   = addDataPoint;
window.updateChartDisplay = updateChartDisplay;