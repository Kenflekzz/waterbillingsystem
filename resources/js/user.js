document.addEventListener('DOMContentLoaded', () => {

    // ------------------------------
    // Global Loader
    // ------------------------------
    const loader = document.getElementById('global-loader');
    window.showLoader = () => {
        if (!loader) return;
        loader.classList.add('show');
        requestAnimationFrame(() => loader.style.opacity = '1');
    };
    window.hideLoader = () => {
        if (!loader) return;
        loader.style.opacity = '0';
        setTimeout(() => loader.classList.remove('show'), 300);
    };

    // Optional: trigger loader on internal links
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || link.target === '_blank') return;
        e.preventDefault();
        showLoader();
        setTimeout(() => window.location.href = href, 50);
    });

    // ------------------------------
    // Collapse Previous Bills button toggle
    // ------------------------------
    const btn = document.querySelector("[data-bs-target='#previousBillsCollapse']");
    const collapse = document.getElementById("previousBillsCollapse");

    if (collapse && btn) {
        collapse.addEventListener("show.bs.collapse", () => {
            btn.innerHTML = '<i class="bi bi-chevron-up"></i> Hide Previous Bills';
        });
        collapse.addEventListener("hide.bs.collapse", () => {
            btn.innerHTML = '<i class="bi bi-chevron-down"></i> Show Previous Bills';
        });
    }

    const userLogoutForm = document.getElementById("user-logout-form");

    if (userLogoutForm) {
    
        userLogoutForm.addEventListener("submit", function(e) {
            // Show loader immediately
            if (typeof window.showLoader === "function") window.showLoader();
            // Allow form to submit normally after showing loader
        });
    }


    // ------------------------------
    // Notifications & My Reports (existing code)
    // ------------------------------
    // You can also move your usernotification.js code here
});
