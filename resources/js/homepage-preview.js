// === GENERIC TEXT/INPUT PREVIEW HANDLER ===
document.querySelectorAll('.live-update').forEach(input => {
    const targetId = input.dataset.target;
    const preview = document.getElementById(targetId);
    if (!preview) return;

    const defaultValue = preview.dataset.default || '';

    const update = () => {
        preview.textContent = input.value || defaultValue;
    };

    input.addEventListener('input', update);
    update(); // initialize on load
});

// === GENERIC IMAGE PREVIEW HANDLER ===
document.querySelectorAll('.image-preview').forEach(input => {
    const targetId = input.dataset.target;
    const preview = document.getElementById(targetId);
    if (!preview) return;

    const existingSrc = input.dataset.existing || '';
    if (existingSrc) {
        preview.src = existingSrc;
        preview.style.display = 'block';
    }

    input.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else {
            preview.src = existingSrc || '';
            preview.style.display = existingSrc ? 'block' : 'none';
        }
    });
});
