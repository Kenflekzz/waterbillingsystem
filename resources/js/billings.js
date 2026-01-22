import Swal from 'sweetalert2';

document.addEventListener("DOMContentLoaded", function () {

    /* ========================================================
       SECTION 1 — BILLING INPUT LOGIC
    ======================================================== */

    const clientSelect = document.getElementById('clientSelect');
    const billingDateInput = document.getElementById('billing_date');
    const arrearsField = document.getElementById('arrears');
    const penaltyField = document.getElementById('total_penalty');
    const currentBillInput = document.querySelector('input[name="current_bill"]');
    const maintenanceCostInput = document.querySelector('input[name="maintenance_cost"]');
    const installationFeeInput = document.querySelector('input[name="installation_fee"]');
    const totalAmountInput = document.querySelector('input[name="total_amount"]');
    const meterNoInput = document.getElementById('meter_no');
    const fullNameInput = document.getElementById('full_name');
    const barangayInput = document.getElementById('barangay');
    const purokInput = document.getElementById('purok');
    const previousInput = document.getElementById('previous_reading');
    const presentInput = document.getElementById('present_reading');
    const consumedInput = document.getElementById('consumed');
    const addModal = document.getElementById('addBillingModal');
    const billingIdInput = document.getElementById('billing_id');

    // Auto-fill client details
    clientSelect?.addEventListener('change', () => {
        const selected = clientSelect.options[clientSelect.selectedIndex];
        meterNoInput.value = selected.dataset.meter || '';
        fullNameInput.value = selected.dataset.fullname || '';
        barangayInput.value = selected.dataset.barangay || '';
        purokInput.value = selected.dataset.purok || '';
        if (billingDateInput.value) fetchArrearsAndPenalty();
    });

    // Calculate consumed water & current bill
    function calculateConsumed() {
        const prev = parseFloat(previousInput.value) || 0;
        const pres = parseFloat(presentInput.value) || 0;
        const cubicMetres = Math.max(0, pres - prev);

        let waterCharge = 0;
        let rem = cubicMetres;

        if (rem > 0) {
            waterCharge += 150; rem -= 10;
            if (rem > 0) { const step = Math.min(10, rem); waterCharge += step * 16; rem -= step; }
            if (rem > 0) { const step = Math.min(10, rem); waterCharge += step * 19; rem -= step; }
            if (rem > 0) { const step = Math.min(10, rem); waterCharge += step * 23; rem -= step; }
            if (rem > 0) { const step = Math.min(10, rem); waterCharge += step * 26; rem -= step; }
            if (rem > 0) waterCharge += rem * 30;
        } else {
            waterCharge = 150;
        }

        consumedInput.value = cubicMetres;
        currentBillInput.value = waterCharge.toFixed(2);
        recalculateTotal();
    }

    // Recalculate grand total
    function recalculateTotal() {
        const currentBill = parseFloat(currentBillInput.value) || 0;
        const arrears     = parseFloat(arrearsField.value) || 0;
        const penalty     = parseFloat(penaltyField.value) || 0;
        const maintenance = parseFloat(maintenanceCostInput.value) || 0;
        const installation= parseFloat(installationFeeInput.value) || 0;

        totalAmountInput.value = (currentBill + arrears + penalty + maintenance + installation).toFixed(2);
    }

    // Fetch arrears & penalty from backend (mirrors back-end logic)
    function fetchArrearsAndPenalty() {
        const clientId = clientSelect.value;
        const billingDate = billingDateInput.value;
        if (!clientId || !billingDate) return;

        fetch(`/admin/billings/${clientId}/previous-current?billing_date=${billingDate}`, { cache: "no-store" })
            .then(res => res.json())
            .then(data => {
                // Backend now returns 'arrears' already computed according to status rules
                arrearsField.value = parseFloat(data.arrears || 0).toFixed(2);

                // Fetch penalty separately
                return fetch(`/admin/billings/${clientId}/penalty?billing_date=${billingDate}`, { cache: "no-store" });
            })
            .then(res => res.json())
            .then(data => {
                penaltyField.value = (parseFloat(data.penalty) || 0).toFixed(2);
                recalculateTotal();
            })
            .catch(() => {
                arrearsField.value = "0.00";
                penaltyField.value = "0.00";
                recalculateTotal();
            });
    }

    // Input event listeners
    [previousInput, presentInput].forEach(input => input?.addEventListener('input', calculateConsumed));
    [maintenanceCostInput, installationFeeInput, consumedInput].forEach(input => input?.addEventListener('input', recalculateTotal));
    billingDateInput?.addEventListener('change', () => { if (clientSelect.value) fetchArrearsAndPenalty(); });

    // Modal logic
    if (addModal) {
        addModal.addEventListener('shown.bs.modal', () => {
            // Reset fields
            [currentBillInput, maintenanceCostInput, installationFeeInput, consumedInput].forEach(input => input.value = '');
            totalAmountInput.value = '0.00';

            // Fetch next billing ID
            fetch("billings/next-id")
                .then(res => res.json())
                .then(data => { billingIdInput.value = data.next_billing_id; })
                .catch(() => { billingIdInput.value = "Error"; });

            // Fetch latest arrears & penalty
            if (clientSelect.value && billingDateInput.value) fetchArrearsAndPenalty();
        });

        addModal.addEventListener('hidden.bs.modal', () => {
            document.querySelector('[data-bs-target="#addBillingModal"]')?.focus();
        });
    }

    /* ========================================================
       SECTION 2 — SWEETALERT SESSION MESSAGES
    ======================================================== */
    if (document.getElementById('hasSuccess')?.value === '1') {
        Swal.fire({ icon: 'success', title: 'Success', draggable: true, text: document.getElementById('successMessage').value, confirmButtonColor: '#3085d6' });
    }
    if (document.getElementById('hasErrors')?.value === '1') {
        Swal.fire({ icon: 'error', title: 'Error', draggable: true, text: document.getElementById('errorMessages').value, confirmButtonColor: '#d33' });
    }

    /* ========================================================
       SECTION 3 — AJAX FORM SUBMIT
    ======================================================== */
    document.querySelectorAll('.ajax-form').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
                });
                const data = await response.json();

                if (response.ok) {
                    const modalEl = form.closest('.modal');
                    if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();
                    Swal.fire("Success", data.message || "Billing saved successfully.", "success").then(() => window.location.reload());
                } else {
                    Swal.fire("Error", data.message || "Something went wrong.", "error");
                }
            } catch {
                Swal.fire("Error", "Failed to save billing.", "error");
            }
        });
    });

    /* ========================================================
       SECTION 4 — DELETE CONFIRM
    ======================================================== */
    document.addEventListener('submit', e => {
        if (e.target.matches('.delete-billing-form')) {
            e.preventDefault();
            Swal.fire({
                title: "Are you sure?",
                text: "This will delete the billing permanently.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then(result => { if (result.isConfirmed) e.target.submit(); });
        }
    });

});
