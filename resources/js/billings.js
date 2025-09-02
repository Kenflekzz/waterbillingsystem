import Swal from 'sweetalert2';

document.addEventListener("DOMContentLoaded", function () {
    // Elements
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

    // ✅ Auto-fill fields when client changes
    if (clientSelect) {
        clientSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            meterNoInput.value = selected.dataset.meter || '';
            fullNameInput.value = selected.dataset.fullname || '';
            barangayInput.value = selected.dataset.barangay || '';
            purokInput.value = selected.dataset.purok || '';
            if (billingDateInput.value) fetchArrearsAndPenalty();
        });
    }

    // ✅ Recalculate consumed based on readings
        function calculateConsumed() {
        const prev = parseFloat(previousInput.value) || 0;
        const pres = parseFloat(presentInput.value) || 0;
        let diff = pres - prev;
        diff = diff > 0 ? diff : 0;

        let amount = 0;

        if (diff <= 10) {
            amount = 150;
        } else {
            amount = 150; // first 0–10 cu.m.
            diff -= 10;

            let tier = Math.min(diff, 10); // next 10 cu.m. at ₱16
            amount += tier * 16;
            diff -= tier;

            tier = Math.min(diff, 10); // next 10 cu.m. at ₱19
            amount += tier * 19;
            diff -= tier;

            tier = Math.min(diff, 10); // next 10 cu.m. at ₱23
            amount += tier * 23;
            diff -= tier;

            tier = Math.min(diff, 10); // next 10 cu.m. at ₱26 (corrected)
            amount += tier * 26;
            diff -= tier;

            // Remaining cu.m. above 50, at ₱30 each (adjust if your rule differs)
            if (diff > 0) {
                amount += diff * 30;
            }
        }

        consumedInput.value = amount.toFixed(2);
        recalculateTotal();
    }




    // ✅ Compute total amount
    function recalculateTotal() {
        const consumed = parseFloat(consumedInput.value) || 0;
        const currentBill = parseFloat(currentBillInput.value) || 0;
        const arrears = parseFloat(arrearsField.value) || 0;
        const penalty = parseFloat(penaltyField.value) || 0;
        const maintenance = parseFloat(maintenanceCostInput.value) || 0;
        const installation = parseFloat(installationFeeInput.value) || 0;

        const total = consumed + currentBill + arrears + penalty + maintenance + installation;
        totalAmountInput.value = total.toFixed(2);
    }

    // ✅ Fetch arrears and penalty from server
    function fetchArrearsAndPenalty() {
        const clientId = clientSelect.value;
        const billingDate = billingDateInput.value;

        if (clientId && billingDate) {
            fetch(`/admin/billings/${clientId}/arrears?billing_date=${billingDate}`)
                .then(response => response.json())
                .then(data => {
                    arrearsField.value = parseFloat(data.arrears).toFixed(2);
                    return fetch(`/admin/billings/${clientId}/penalty?billing_date=${billingDate}`);
                })
                .then(response => response.json())
                .then(data => {
                    penaltyField.value = parseFloat(data.penalty).toFixed(2);
                    recalculateTotal();
                })
                .catch(error => {
                    console.error('Error fetching arrears/penalty:', error);
                    arrearsField.value = '0.00';
                    penaltyField.value = '0.00';
                    recalculateTotal();
                });
        } else {
            arrearsField.value = '0.00';
            penaltyField.value = '0.00';
            recalculateTotal();
        }
    }

    // ✅ Event listeners
    previousInput?.addEventListener('input', calculateConsumed);
    presentInput?.addEventListener('input', calculateConsumed);
    currentBillInput?.addEventListener('input', recalculateTotal);
    maintenanceCostInput?.addEventListener('input', recalculateTotal);
    installationFeeInput?.addEventListener('input', recalculateTotal);
    billingDateInput?.addEventListener('change', () => {
        if (clientSelect.value) fetchArrearsAndPenalty();
    });

    // ✅ On modal show: reset fields & fetch next billing ID
    if (addModal) {
        addModal.addEventListener('shown.bs.modal', () => {
            arrearsField.value = '0.00';
            penaltyField.value = '0.00';
            totalAmountInput.value = '0.00';
            currentBillInput.value = '';
            maintenanceCostInput.value = '';
            installationFeeInput.value = '';
            consumedInput.value = '';

            fetch("billings/next-id")
                .then(response => response.json())
                .then(data => { billingIdInput.value = data.next_billing_id; })
                .catch(error => {
                    console.error(error);
                    billingIdInput.value = "Error";
                });

            if (clientSelect.value && billingDateInput.value) fetchArrearsAndPenalty();
        });

        addModal.addEventListener('hidden.bs.modal', () => {
            document.querySelector('[data-bs-target="#addBillingModal"]')?.focus();
        });
    }

    // ✅ SweetAlert2 success or error
    if (document.getElementById('hasSuccess')?.value === '1') {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            draggable: true,
            text: document.getElementById('successMessage').value,
            confirmButtonColor: '#3085d6'
        });
    }
    if (document.getElementById('hasErrors')?.value === '1') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: document.getElementById('errorMessages').value,
            confirmButtonColor: '#d33'
        });
    }

    // ✅ Confirm delete with SweetAlert2
    document.addEventListener('submit', function (e) {
        if (e.target.matches('.delete-billing-form')) {
            e.preventDefault();
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                draggable: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) e.target.submit();
            });
        }
    });
});
