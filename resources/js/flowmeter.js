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

    let ws              = null;
    let reconnectTimer  = null;
    let currentDeviceId = null;
    let currentWsUrl    = null;

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
            plugins: {
                legend: { display: true },
            }
        }
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
       WEBSOCKET
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

    function setConnecting() {
        statusDot.style.background  = '#ff9800';
        statusText.style.color      = '#ff9800';
        statusText.textContent      = 'Connecting...';
    }

    function saveReading(deviceId, flowRate, totalVolume) {
        if (!window._lastSaved || Date.now() - window._lastSaved >= 60000) {
            window._lastSaved = Date.now();

            fetch('/admin/flow-readings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    iot_device_id: deviceId,
                    flow_rate:     flowRate,
                    total_volume:  totalVolume,
                })
            }).catch(err => console.error('Failed to save reading:', err));
        }
    }

    function connect(deviceId, wsUrl) {
        // Close existing connection
        if (ws) {
            ws.onclose = null;
            ws.close();
        }
        clearTimeout(reconnectTimer);

        currentDeviceId = deviceId;
        currentWsUrl    = wsUrl;

        setConnecting();
        ws = new WebSocket(wsUrl);

        ws.onopen = () => {
            setConnected();
            clearTimeout(reconnectTimer);
        };

        ws.onmessage = (event) => {
            try {
                const data       = JSON.parse(event.data);
                const rate       = parseFloat(data.flow_rate    ?? 0).toFixed(2);
                const volume     = parseFloat(data.total_volume ?? 0).toFixed(2);
                const cubicMeter = (parseFloat(volume) / 1000).toFixed(4);
                const timeLabel  = new Date().toLocaleTimeString();

                // Update display
                flowRateEl.textContent    = rate;
                totalVolumeEl.textContent = volume;
                cubicMeterEl.textContent  = cubicMeter;

                // Update chart
                addChartData(timeLabel, parseFloat(rate));

                // Save to DB
                saveReading(currentDeviceId, rate, volume);

            } catch {
                flowRateEl.textContent = parseFloat(event.data).toFixed(2);
            }
        };

        ws.onerror = () => setDisconnected();

        ws.onclose = () => {
            setDisconnected();
            // Auto-reconnect every 5 seconds
            reconnectTimer = setTimeout(() => connect(currentDeviceId, currentWsUrl), 5000);
        };
    }

    // Connect when device is selected
    deviceSelect?.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const deviceId = this.value;
        const wsUrl    = selected.dataset.ws;

        if (!deviceId || !wsUrl) return;

        // Reset chart
        flowChart.data.labels = [];
        flowChart.data.datasets[0].data = [];
        flowChart.update();

        connect(deviceId, wsUrl);
    });

    /* ========================================================
       ASSIGN CONSUMER
    ======================================================== */
    document.querySelectorAll('.save-assign-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const deviceId  = this.dataset.deviceId;
            const modal     = document.getElementById('assignModal' + deviceId);
            const select    = modal.querySelector('.assign-client-select');
            const clientId  = select.value;

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

});