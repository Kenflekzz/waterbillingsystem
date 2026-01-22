import Swal from 'sweetalert2';
import '@fortawesome/fontawesome-free/css/all.min.css';

// =========================
// PROFILE IMAGE PREVIEW + CHANGE BUTTON
// =========================
const profileInput = document.getElementById('profileImageInput');
const profilePreview = document.getElementById('profileImagePreview');
const changeProfileBtn = document.getElementById('changeProfileBtn');

// When "Change Profile" button is clicked → open hidden file input
if (changeProfileBtn && profileInput) {
    changeProfileBtn.addEventListener('click', () => {
        profileInput.click();
    });
}

// Update image preview when a file is selected
if (profileInput) {
    profileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                profilePreview.src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
}

// =========================
// PASSWORD SHOW/HIDE LOGIC
// =========================
document.querySelectorAll('.password-field').forEach(input => {
    const parentDiv = input.closest('.position-relative');
    const toggleBtn = parentDiv.querySelector('.toggle-password');
    const icon = toggleBtn.querySelector('i');

    input.addEventListener('input', () => {
        toggleBtn.style.display = input.value.length > 0 ? 'block' : 'none';
        if (!input.value) {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    toggleBtn.addEventListener('click', () => {
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});

// =========================
// AJAX FORM SUBMISSION
// =========================
const mergedForm = document.getElementById('profileForm');
mergedForm.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(mergedForm);
    const action = mergedForm.action;
    const method = mergedForm.method.toUpperCase();

    try {
        const response = await fetch(action, {
            method: method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            let message = data.message || 'Something went wrong.';
            if (data.errors) {
                message = Object.values(data.errors)[0][0];
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#d33',
                draggable: true
            });
            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data.message,
            confirmButtonColor: '#3085d6',
            draggable: true
        }).then(() => location.reload());

    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong.',
            confirmButtonColor: '#d33',
            draggable: true
        });
    }
});
