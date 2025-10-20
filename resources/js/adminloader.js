const loader = document.getElementById("global-loader");

// Delay before hiding loader (matches progress duration + fade)
const LOADER_DELAY = 500;

function showLoader() {
    loader.style.display = "flex";
    loader.style.opacity = "1"; // reset opacity
}

function hideLoader() {
    setTimeout(() => {
        loader.style.opacity = "0"; // fade out
        setTimeout(() => {
            loader.style.display = "none";
        }, 500); // wait for fade-out transition
    }, LOADER_DELAY);
}

// Hide loader once page fully loads
window.addEventListener("load", () => {
    hideLoader();
});

// ✅ Fetch Patch
const originalFetch = window.fetch;
window.fetch = async (...args) => {
    try {
        showLoader();
        const response = await originalFetch(...args);
        hideLoader();
        return response;
    } catch (error) {
        hideLoader();
        throw error;
    }
};

// ✅ Axios Support
if (window.axios) {
    window.axios.interceptors.request.use(config => {
        showLoader();
        return config;
    }, error => {
        hideLoader();
        return Promise.reject(error);
    });

    window.axios.interceptors.response.use(response => {
        hideLoader();
        return response;
    }, error => {
        hideLoader();
        return Promise.reject(error);
    });
}

// ✅ jQuery AJAX Support
if (window.jQuery) {
    $(document).ajaxStart(() => showLoader());
    $(document).ajaxStop(() => hideLoader());
}
