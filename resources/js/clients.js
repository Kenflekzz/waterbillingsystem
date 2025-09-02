import Swal from 'sweetalert2';

document.addEventListener("DOMContentLoaded", function () {
    // Show success modal if session exists
    if (window.showSuccessModal && window.successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            draggable: true,
            text: window.successMessage,
            confirmButtonColor: '#3085d6'
        });
    }

    // Show error modal if session exists
    if (window.showErrorModal && window.errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            draggable: true,
            text: window.errorMessage,
            confirmButtonColor: '#d33'
        });
    }

    // Show Add Client Modal if there are validation errors
    if (window.showAddClientModalOnLoad) {
        const addClientModalEl = document.getElementById('addClientModal');
        if (addClientModalEl) {
            const addClientModal = new bootstrap.Modal(addClientModalEl);
            addClientModal.show();
        }
    }

    // SweetAlert2 confirm before delete
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

    // ✅ Automatic filter on change
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });
    }
});
