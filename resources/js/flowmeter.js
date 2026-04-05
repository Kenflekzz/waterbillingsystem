import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', function () {

    /* ========================================================
       ELEMENTS
    ======================================================== */
    const deviceSelect    = document.getElementById('deviceSelect');
    const flowRateEl      = document.getElementById('live-flow-rate');
    const totalVolumeEl   = document.getElementById('live-total-volume');
    const cubicMeterEl    = document.getElementById('live-cubic-meter');
    const statusDot       = document.getElementById('live-status-dot');
    const statusText      = document.getElementById('live-status-text');

    let pollingInterval = null;
    let currentDeviceId = null;

    const THRESHOLD = 50;

    /* ========================================================
       CHART SETUP
    ======================================================== */
    const ctx = document.getElementById('flowChart').getContext('2d');
    const flowChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Flow Rate (L/min)',
                data: [],
                borderColor: '#1565c0',
                backgroundColor: 'rgba(21, 101, 192, 0.1)',
                borderWidth: 2,
                pointRadius: 3,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            animation: false,
            scales: {
                x: { ticks: { maxTicksLimit: 10 } },
                y: { beginAtZero: true, title: { display: true, text: 'L/min' } }
            },
            plugins: { legend: { display: true } }
        },
        plugins: [{
            id: 'thresholdLine',
            afterDraw(chart) {
                const threshold = THRESHOLD;
                const yScale    = chart.scales.y;
                const xScale    = chart.scales.x;
                const ctx       = chart.ctx;
                const y         = yScale.getPixelForValue(threshold);

                if (y < yScale.top || y > yScale.bottom) return;

                ctx.save();
                ctx.beginPath();
                ctx.moveTo(xScale.left, y);
                ctx.lineTo(xScale.right, y);
                ctx.lineWidth   = 2;
                ctx.strokeStyle = '#e53935';
                ctx.setLineDash([6, 4]);
                ctx.stroke();

                ctx.fillStyle = '#e53935';
                ctx.font      = 'bold 11px Arial';
                ctx.fillText(`⚠ Alert Threshold (${THRESHOLD} L/min)`, xScale.left + 8, y - 6);
                ctx.restore();
            }
        }]
    });

    function addChartData(label, value) {
        if (flowChart.data.labels.length >= 30) {
            flowChart.data.labels.shift();
            flowChart.data.datasets[0].data.shift();
        }
        flowChart.data.labels.push(label);
        flowChart.data.datasets[0].data.push(value);
        flowChart.update();
    }

    /* ========================================================
       STATUS HELPERS
    ======================================================== */
    function setConnected() {
        statusDot.style.background  = '#4caf50';
        statusText.style.color      = '#4caf50';
        statusText.textContent      = 'Live';
    }

    function setDisconnected() {
        statusDot.style.background  = '#f44336';
        statusText.style.color      = '#f44336';
        statusText.textContent      = 'Disconnected';
        flowRateEl.textContent      = '--';
        totalVolumeEl.textContent   = '--';
        cubicMeterEl.textContent    = '--';
    }

    /* ========================================================
       POLLING - replaces WebSocket
    ======================================================== */
    function startPolling(deviceId) {
        if (pollingInterval) clearInterval(pollingInterval);
        currentDeviceId = deviceId;
        setConnected();

        pollingInterval = setInterval(() => {
            fetch(`/admin/flow-readings/latest/${deviceId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data) {
                        setDisconnected();
                        return;
                    }

                    const rate       = parseFloat(data.flow_rate    ?? 0).toFixed(2);
                    const volume     = parseFloat(data.total_volume ?? 0).toFixed(2);
                    const cubicMeter = (parseFloat(volume) / 1000).toFixed(4);
                    const timeLabel  = new Date().toLocaleTimeString();

                    flowRateEl.textContent    = rate;
                    totalVolumeEl.textContent = volume;
                    cubicMeterEl.textContent  = cubicMeter;

                    addChartData(timeLabel, parseFloat(rate));
                })
                .catch(err => {
                    console.error('Polling failed:', err);
                    setDisconnected();
                });
        }, 3000); // poll every 5 seconds
    }

    /* ========================================================
       DEVICE SELECT
    ======================================================== */
    deviceSelect?.addEventListener('change', function () {
        const deviceId = this.value;
        if (!deviceId) return;

        flowChart.data.labels = [];
        flowChart.data.datasets[0].data = [];
        flowChart.update();

        startPolling(deviceId);
    });

    /* ========================================================
       ASSIGN CONSUMER
    ======================================================== */
    document.querySelectorAll('.save-assign-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const deviceId = this.dataset.deviceId;
            const modal    = document.getElementById('assignModal' + deviceId);
            const select   = modal.querySelector('.assign-client-select');
            const clientId = select.value;

            if (!clientId) {
                Swal.fire('Warning', 'Please select a consumer.', 'warning');
                return;
            }

            fetch(`/admin/flowmeter/devices/${deviceId}/assign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ client_id: clientId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Consumer assigned successfully.', 'success')
                        .then(() => window.location.reload());
                }
            })
            .catch(() => Swal.fire('Error', 'Failed to assign consumer.', 'error'));
        });
    });

    /* ========================================================
       DELETE DEVICE CONFIRM
    ======================================================== */
    document.addEventListener('submit', e => {
        if (e.target.matches('.delete-device-form')) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete the device permanently.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(result => { if (result.isConfirmed) e.target.submit(); });
        }
    });

    /* ========================================================
       SESSION MESSAGES
    ======================================================== */
    const hasSuccess = document.getElementById('hasSuccess');
    const hasErrors  = document.getElementById('hasErrors');

    if (hasSuccess?.value === '1') {
        Swal.fire({ icon: 'success', title: 'Success', text: document.getElementById('successMessage').value, confirmButtonColor: '#3085d6' });
    }
    if (hasErrors?.value === '1') {
        Swal.fire({ icon: 'error', title: 'Error', text: document.getElementById('errorMessages').value, confirmButtonColor: '#d33' });
    }

    /* ========================================================
       LIVE CONSUMPTION TABLE REFRESH
    ======================================================== */
    function refreshConsumptionTable() {
        fetch('/admin/flowmeter/consumptions')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#consumptionTable tbody');
                if (!tbody) return;

                if (!data.length) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center">No readings yet.</td></tr>`;
                    return;
                }

                tbody.innerHTML = data.map(row => `
                    <tr>
                        <td>${row.device_name ?? 'N/A'}</td>
                        <td>${row.consumer ?? 'Unassigned'}</td>
                        <td>${row.barangay ?? '--'}</td>
                        <td>${row.purok ?? '--'}</td>
                        <td>${parseFloat(row.total_volume ?? 0).toFixed(2)}</td>
                        <td><strong>${parseFloat(row.total_cubic_meter ?? 0).toFixed(4)}</strong></td>
                        <td>${row.updated_at ?? '--'}</td>
                    </tr>
                `).join('');
            })
            .catch(err => console.error('Failed to refresh table:', err));
    }

    refreshConsumptionTable();
    setInterval(refreshConsumptionTable, 5000);

});