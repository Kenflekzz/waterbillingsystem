import Swal from 'sweetalert2';

document.addEventListener("DOMContentLoaded", function () {
    // Check if we need to show Add Client Modal (any add-related error)
    const hasAddErrors = document.querySelector('#addClientModal .alert-danger') !== null;
    const hasOldInput = document.querySelector('input[name="_method"]') === null && 
                        document.querySelectorAll('#addClientModal input[value]').length > 0;

    // Show Add Client Modal if there are errors or old input (except success)
    if (hasAddErrors || (hasOldInput && !document.querySelector('.alert-success'))) {
        const addClientModalEl = document.getElementById('addClientModal');
        if (addClientModalEl) {
            setTimeout(() => {
                const addClientModal = new bootstrap.Modal(addClientModalEl);
                addClientModal.show();
            }, 100);
        }
    }

    // Show Edit Client Modal if there were errors
    const editModalTrigger = document.querySelector('[data-show-edit-modal]');
    if (editModalTrigger) {
        const editClientId = editModalTrigger.dataset.showEditModal;
        if (editClientId) {
            const editModalEl = document.getElementById('editClientModal' + editClientId);
            if (editModalEl) {
                setTimeout(() => {
                    const editModal = new bootstrap.Modal(editModalEl);
                    editModal.show();
                }, 100);
            }
        }
    }

    // SweetAlert for success messages only
    const successAlert = document.querySelector('.alert-success');
    if (successAlert && !hasAddErrors) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            draggable: true,
            text: successAlert.textContent.trim(),
            confirmButtonColor: '#3085d6'
        });
    }

    // SweetAlert for general errors (not add client errors)
    const generalErrorAlert = document.querySelector('.alert-danger:not(#addClientModal .alert-danger)');
    if (generalErrorAlert) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            draggable: true,
            text: generalErrorAlert.textContent.trim(),
            confirmButtonColor: '#d33'
        });
    }

    // SweetAlert confirm before delete
    document.addEventListener('submit', function (e) {
        if (e.target.matches('.delete-client-form')) {
            e.preventDefault();
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                draggable: true,
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    e.target.submit();
                }
            });
        }
    });

    // Automatic filter on change
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.addEventListener('change', function () {
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.submit();
            }
        });
    }
});