import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', function () {
    // ------------------------------
    // Handle all GCash forms
    // ------------------------------
    document.querySelectorAll('.gcash-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const billId = form.dataset.billId; 
            const btn   = form.querySelector('button');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Populate modal
                    const modalTitle = document.getElementById('qrModalTitle');
                    const modalAmount = document.getElementById('qrModalAmount');
                    const modalImage = document.getElementById('qrModalImage');
                    const modalLink = document.getElementById('qrModalLink');

                    modalTitle.textContent = data.title || 'Pay with GCash';
                    modalAmount.textContent = `₱${Number(data.amount).toFixed(2)}`;
                    modalImage.src = `data:image/svg+xml;base64,${data.qrCode}`;
                    modalLink.href = data.checkoutUrl;

                    // Show Bootstrap modal
                    const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
                    qrModal.show();

                    // Optionally disable the button after opening modal
                    btn.disabled = true;
                    btn.classList.remove('btn-success', 'btn-warning');
                    btn.classList.add('btn-secondary');
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Ready to Pay';
                } else {
                    Swal.fire('Error', data.message || 'Payment link failed', 'error');
                }

            } catch (err) {
                Swal.fire('Error', 'GCash payment failed. Please try again.', 'error');
                console.error(err);
            }
        });
    });

    // ------------------------------
    // User Reports Modal
    // ------------------------------
    const btnReports = document.getElementById('btnMyReports');
    const reportsModalEl = document.getElementById('myReportsModal');
    const reportsModal = new bootstrap.Modal(reportsModalEl);
    const reportsTableBody = document.getElementById('myReportsTableBody');

    if (btnReports && reportsModalEl && reportsTableBody) {
        btnReports.addEventListener('click', async function () {
            try {
                const url = btnReports.dataset.reportsUrl;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

                if (!res.ok) throw new Error('Failed to fetch reports');

                const data = await res.json();
                reportsTableBody.innerHTML = '';

                if (!data.reports || data.reports.length === 0) {
                    reportsTableBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">You haven't submitted any reports yet.</td></tr>`;
                } else {
                    data.reports.forEach(report => {
                        reportsTableBody.innerHTML += `
                            <tr>
                                <td>${report.subject}</td>
                                <td>
                                    <span class="badge ${report.status === 'pending' ? 'bg-warning text-dark' : report.status === 'resolved' ? 'bg-success' : 'bg-secondary'}">
                                        ${report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                                    </span>
                                </td>
                                <td>${new Date(report.created_at).toLocaleDateString()}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#reportDetailModal${report.id}">
                                        <i class="bi bi-eye-fill"></i> View
                                    </button>
                                </td>
                            </tr>
                        `;

                        if (!document.getElementById(`reportDetailModal${report.id}`)) {
                            document.body.insertAdjacentHTML('beforeend', `
                                <div class="modal fade" id="reportDetailModal${report.id}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content p-3">
                                            <div class="modal-header">
                                                <h5 class="modal-title">${report.subject}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Message:</strong></p>
                                                <div class="border p-2 rounded">${report.description}</div>
                                                ${report.image ? `<p class="mt-3"><strong>Attached Image:</strong></p>
                                                <img src="/storage/${report.image}" class="img-fluid rounded border" alt="Report Image">` : ''}
                                                <p class="mt-3"><strong>Status:</strong>
                                                    <span class="badge ${report.status === 'pending' ? 'bg-warning text-dark' : report.status === 'resolved' ? 'bg-success' : 'bg-secondary'}">
                                                        ${report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                        }
                    });
                }

                reportsModal.show();
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Failed to load your reports. Please try again.', 'error');
            }
        });
    }

    // ------------------------------
    // Handle Flash Session: SweetAlert + Auto-Download
    // ------------------------------
    const flashSuccess = document.getElementById('flash-success')?.value;
    const flashError = document.getElementById('flash-error')?.value;
    const pdfUrl = document.getElementById('flash-pdfUrl')?.value;

    if (flashSuccess) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: flashSuccess,
            confirmButtonColor: '#3085d6',
            draggable: true
        });
    }

    if (flashError) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: flashError,
            confirmButtonColor: '#d33',
            draggable: true
        });
    }

    if (pdfUrl) {
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        link.remove();
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.querySelector("[data-bs-target='#previousBillsCollapse']");
    const collapse = document.getElementById("previousBillsCollapse");

    collapse.addEventListener("show.bs.collapse", () => {
        btn.innerHTML = '<i class="bi bi-chevron-up"></i> Hide Previous Bills';
    });

    collapse.addEventListener("hide.bs.collapse", () => {
        btn.innerHTML = '<i class="bi bi-chevron-down"></i> Show Previous Bills';
    });
});