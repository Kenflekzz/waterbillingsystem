document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filterForm');
    ['status', 'billing_date'].forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            field.addEventListener('change', () => {
                form.submit();
            });
        }
    });
});
