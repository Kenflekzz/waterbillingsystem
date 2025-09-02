import Swal from 'sweetalert2';

document.addEventListener("DOMContentLoaded", function () {
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
            draggable: true,
            text: document.getElementById('errorMessages').value,
            confirmButtonColor: '#d33'
        });
    }

    // Optional: confirm before delete, if you have delete buttons
    document.addEventListener('submit', function (e) {
        if (e.target.matches('.delete-payment-form')) {
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
                if (result.isConfirmed) {
                    e.target.submit();
                }
            });
        }
    });

});
window.togglePartialInput = function(id) {
    const paymentType = document.getElementById('paymentType' + id).value;
    const partialDiv = document.getElementById('partialAmountDiv' + id);
    if (paymentType === 'partial_current') {
        partialDiv.classList.remove('d-none');
    } else {
        partialDiv.classList.add('d-none');
    }
};

