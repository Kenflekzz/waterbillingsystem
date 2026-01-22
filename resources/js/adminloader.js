const loader = document.getElementById("global-loader");

window.showLoader = () => {
    if (!loader) return;
    loader.style.display = "flex";
    requestAnimationFrame(() => loader.style.opacity = "1");
};

window.hideLoader = () => {
    if (!loader) return;
    loader.style.opacity = "0";
    setTimeout(() => loader.style.display = "none", 300);
};

// Hide loader on page load/back-forward
window.addEventListener("load", window.hideLoader);
window.addEventListener("pageshow", window.hideLoader);

let blockLoader = false;

// --------------------
// Link Clicks
// --------------------
document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || link.target === '_blank') return;

    // Stop default and show loader
    e.preventDefault();
    if (typeof window.showLoader === 'function') window.showLoader();

    // Delay navigation
    setTimeout(() => {
        window.location.href = href;
    }, 50);
});

// --------------------
// Forms (AJAX-safe)
// --------------------
document.addEventListener("submit", (e) => {
    const ajaxForm = e.target.closest(".ajax-form, .delete-payment-form, .delete-billing-form");
    if (ajaxForm) blockLoader = true;
});

// --------------------
// Override fetch to prevent loader for AJAX
// --------------------
window.fetch = new Proxy(window.fetch, {
    apply(target, thisArg, args) {
        blockLoader = true;
        return Reflect.apply(target, thisArg, args);
    }
});
