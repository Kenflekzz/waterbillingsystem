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

    // ------------------------------
    // Link Click Handler (with exclusions)
    // ------------------------------
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;
        
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || link.target === '_blank') return;
        
        // 🔴 FIX: Skip loader for print/download links
        if (href.includes('/print') || href.includes('download') || link.classList.contains('no-loader')) {
            return; // Allow normal navigation without loader
        }
        
        e.preventDefault();
        showLoader();
        setTimeout(() => window.location.href = href, 50);
    });

    // ------------------------------
    // Form Submit Handler (with exclusions)
    // ------------------------------
    document.addEventListener('submit', (e) => {
        const form = e.target;
        // Skip loader for GCash forms (they use AJAX/modal)
        if (form.classList.contains('gcash-form')) {
            return;
        }
        showLoader();
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

    // ------------------------------
    // Logout Form
    // ------------------------------
    const userLogoutForm = document.getElementById("user-logout-form");  // ✅ ADD THIS LINE BACK
    if (userLogoutForm) {
        userLogoutForm.addEventListener("submit", function(e) {
            if (typeof window.showLoader === "function") window.showLoader();
        });
    }

});