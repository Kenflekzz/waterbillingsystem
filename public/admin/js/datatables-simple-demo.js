window.addEventListener('DOMContentLoaded', event => {
    const datatablesSimple = document.getElementById('datatablesSimple');

    if (datatablesSimple) {
        const dt = new simpleDatatables.DataTable(datatablesSimple);

        // Hide loader when table is ready (FIRST LOAD)
        dt.on("datatable.init", () => hideLoader());

        // Hide loader on paginating
        dt.on("datatable.page", () => hideLoader());

        // Hide loader on sorting
        dt.on("datatable.sort", () => hideLoader());
    }
});
