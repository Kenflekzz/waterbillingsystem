document.addEventListener("DOMContentLoaded", () => {
    const notifBtn = document.getElementById("notifBellBtn");
    const notifList = document.getElementById("notifList");
    const notifCount = document.getElementById("notifCount");

    if (!notifBtn || !notifList || !notifCount) return;

    const fetchNotifications = async () => {
        try {
            const res = await fetch("notifications", { headers: { "Accept": "application/json" } });
            if (!res.ok) throw new Error("Failed to fetch notifications");
            const data = await res.json();
            const items = data.notifications || [];

            // Update unread count
            const unread = items.filter(n => !n.is_read).length;
            notifCount.textContent = unread;
            unread > 0 ? notifCount.classList.remove("d-none") : notifCount.classList.add("d-none");

            // Populate dropdown
            notifList.innerHTML = "";
            if (items.length === 0) {
                notifList.innerHTML = `<li class="text-center text-muted small py-2">No notifications</li>`;
                return;
            }

            items.forEach(n => {
                const li = document.createElement("li");
                li.classList.add("dropdown-item", "small", "notif-item");
                li.style.cursor = "pointer";
                li.dataset.id = n.id;
                li.dataset.type = n.type;

                // Bold title if unread
                const titleStyle = n.is_read ? "color: grey;" : "font-weight: bold;";
                li.innerHTML = `<strong style="${titleStyle}">${n.title}</strong><br><span class="text-muted">${n.message}</span>`;
                notifList.appendChild(li);

                // Divider
                const divider = document.createElement("li");
                divider.innerHTML = '<hr class="dropdown-divider">';
                notifList.appendChild(divider);

                // Click handler
                li.addEventListener("click", () => {
                    // Redirect based on type
                    if (n.type === "billing") {
                        window.location.href = "/user/billing";
                    } else if (n.type === "report_resolved") {
                        window.location.href = "/user/billing"; // Page where My Reports modal exists
                    }

                    // Mark notification as read
                    fetch(`notifications/${n.id}/read`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            "Accept": "application/json"
                        }
                    }).then(() => {
                        // Change title style to grey after marking as read
                        li.querySelector("strong").style.fontWeight = "normal";
                        li.querySelector("strong").style.color = "grey";
                    }).catch(e => console.error("Failed to mark notification as read", e));

                    // Close dropdown
                    const dropdown = bootstrap.Dropdown.getInstance(notifBtn);
                    dropdown && dropdown.hide();
                });
            });
        } catch (e) {
            console.error("Error loading notifications:", e);
            notifList.innerHTML = `<li class="text-danger text-center small py-2">Error loading notifications</li>`;
        }
    };

    // Initial fetch
    fetchNotifications();

    // Fetch again when bell clicked
    notifBtn.addEventListener("click", fetchNotifications);


});
document.addEventListener("DOMContentLoaded", () => {
    const reportsBtn = document.getElementById("btnMyReports");
    const tableBody = document.getElementById("myReportsTableBody");
    const pagination = document.getElementById("reportsPagination");

    const reportsModalEl = document.getElementById("myReportsModal");
    const reportsModal = new bootstrap.Modal(reportsModalEl, { backdrop: true });

    const detailsModalEl = document.getElementById("reportDetailsModal");
    const detailsModal = new bootstrap.Modal(detailsModalEl, { backdrop: true });

    const detailsSubject = document.getElementById("detailsSubject");
    const detailsStatus = document.getElementById("detailsStatus");
    const detailsDate = document.getElementById("detailsDate");
    const detailsDescription = document.getElementById("detailsDescription");
    const detailsImage = document.getElementById("detailsImage");

    let currentPage = 1;

    function loadReports(page = 1) {
        currentPage = page;

        fetch(`/user/reports?page=${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            tableBody.innerHTML = "";

            if (data.reports && data.reports.length > 0) {
                data.reports.forEach(r => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${r.subject}</td>
                        <td>${r.status}</td>
                        <td>${new Date(r.created_at).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-sm btn-info view-report-btn"
                                data-subject="${r.subject}"
                                data-status="${r.status}"
                                data-date="${new Date(r.created_at).toLocaleDateString()}"
                                data-description="${r.description}"
                                data-image="${r.image ? '/storage/' + r.image : ''}">
                                View
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No reports found.</td></tr>`;
            }

            // Pagination
            pagination.innerHTML = "";
            if (data.last_page && data.last_page > 1) {
                for (let i = 1; i <= data.last_page; i++) {
                    const li = document.createElement("li");
                    li.className = `page-item ${i === data.current_page ? 'active' : ''}`;
                    li.innerHTML = `<button class="page-link">${i}</button>`;
                    li.querySelector("button").addEventListener("click", () => loadReports(i));
                    pagination.appendChild(li);
                }
            }
        })
        .catch(err => {
            console.error("Failed to fetch reports:", err);
            tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Failed to load reports</td></tr>`;
        });
    }

    // Open modal on button click
    reportsBtn.addEventListener("click", () => {
        loadReports(1);
        reportsModal.show();
    });

    // View report details
    tableBody.addEventListener("click", e => {
        if (e.target.classList.contains("view-report-btn")) {
            const btn = e.target;
            detailsSubject.textContent = btn.dataset.subject;
            detailsStatus.textContent = btn.dataset.status;
            detailsDate.textContent = btn.dataset.date;
            detailsDescription.textContent = btn.dataset.description;

            if (btn.dataset.image) {
                detailsImage.src = btn.dataset.image;
                detailsImage.classList.remove("d-none");
            } else {
                detailsImage.classList.add("d-none");
            }

            detailsModal.show();
        }
    });
});




