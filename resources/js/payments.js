import Swal from 'sweetalert2';

// ------------------------------
// Profile Image Preview
// ------------------------------
const profileInput = document.getElementById('profileImageInput');
const profilePreview = document.getElementById('profileImagePreview');

if (profileInput) {
    profileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
}

// ------------------------------
// SweetAlert2 success on page load (for non-AJAX redirects)
// ------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const successMessage = document.getElementById('successMessage')?.value;
    const hasSuccess = document.getElementById('hasSuccess')?.value === '1';
    const hasErrors = document.getElementById('hasErrors')?.value === '1';
    const errorMessages = document.getElementById('errorMessages')?.value;

    if (hasSuccess && successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: successMessage,
            confirmButtonColor: '#3085d6',
            draggable: true
        });
    }

    if (hasErrors && errorMessages) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessages,
            confirmButtonColor: '#d33',
            draggable: true
        });
    }

    // ------------------------------
    // Eye icon behavior for password fields
    // ------------------------------
    document.querySelectorAll('.toggle-password').forEach(button => {
        const targetId = button.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const svg = button.querySelector('svg');

        svg.style.opacity = input.value ? '1' : '0';

        input.addEventListener('input', () => {
            svg.style.opacity = input.value ? '1' : '0';
        });

        button.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
});

// ------------------------------
// AJAX form submission with SweetAlert2
// Works for both update and delete
// ------------------------------
document.addEventListener('submit', async function (e) {
    const form = e.target;

    if (form.classList.contains('ajax-form')) {
        e.preventDefault();

        // DELETE confirmation
        if (form.classList.contains('delete-payment-form')) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This payment will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                draggable: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await submitForm(form);
                }
            });
        } else {
            // normal update
            await submitForm(form);
        }
    }
});

// ------------------------------
// Helper function to submit AJAX form
// ------------------------------
async function submitForm(form) {
    const action = form.action;
    const method = form.method.toUpperCase();
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await fetch(action, {
            method: method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: data.icon || 'success',
                title: data.title || 'Success',
                text: data.message || 'Operation completed successfully.',
                confirmButtonColor: '#3085d6',
                draggable: true
            }).then(() => location.reload());
        } else {
            Swal.fire({
                icon: data.icon || 'error',
                title: data.title || 'Error',
                text: data.message || 'Something went wrong.',
                confirmButtonColor: '#d33',
                draggable: true
            });
        }

    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong.',
            confirmButtonColor: '#d33',
            draggable: true
        });
    }
}
// Toggle partial amount input visibility & required state
window.togglePartialInput = function (id) {
    const select   = document.getElementById('paymentType' + id);
    const div      = document.getElementById('partialAmountDiv' + id);
    const input    = document.getElementById('partialAmount' + id);

    if (select.value === 'partial_current') {
        div.classList.remove('d-none');
        input.setAttribute('required', 'required');
    } else {
        div.classList.add('d-none');
        input.removeAttribute('required');
        input.value = '';               // reset unused value
    }
};

// Run once per modal when the page is ready
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[id^="editModal"]').forEach(modal => {
        const id = modal.id.replace(/\D/g, ''); // extract numeric ID
        togglePartialInput(id);
    });
});