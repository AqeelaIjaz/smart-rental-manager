// ===========================================
// Smart Rental Manager
// Notifications JavaScript (Member 5 — real backend wiring)
// ===========================================

const API_BASE = "../backend";

const clearBtn = document.getElementById("clearNotifications");
const emptyBox = document.getElementById("emptyNotification");
const listContainer = document.getElementById("notificationsList");

const ICONS = {
    rent_reminder: "fa-calendar-days",
    payment:       "fa-money-check-dollar",
    complaint:     "fa-comment-dots",
    repair:        "fa-screwdriver-wrench",
    system:        "fa-bell",
};

const STYLE_CLASS = {
    rent_reminder: "reminder",
    payment:       "success",
    complaint:     "warning",
    repair:        "warning",
    system:        "",
};

function timeAgo(dateString) {
    const date = new Date(dateString.replace(" ", "T"));
    const diffMs = Date.now() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return "Just now";
    if (diffMins < 60) return `${diffMins}m ago`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h ago`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays === 1) return "Yesterday";
    return `${diffDays} days ago`;
}

function renderNotifications(notifications) {
    listContainer.innerHTML = "";

    if (!notifications.length) {
        emptyBox.style.display = "block";
        return;
    }

    emptyBox.style.display = "none";

    notifications.forEach(n => {
        const card = document.createElement("div");
        card.className = `notification-card ${STYLE_CLASS[n.type] || ""}`.trim();
        card.dataset.id = n.id;
        if (n.is_read == 1) card.style.opacity = "0.7";

        card.innerHTML = `
            <div class="icon-box">
                <i class="fa-solid ${ICONS[n.type] || "fa-bell"}"></i>
            </div>
            <div>
                <h3>${n.title}</h3>
                <p>${n.message.replace(/\[agreement:\d+\]/, "").trim()}</p>
                <span>${timeAgo(n.created_at)}</span>
            </div>
        `;

        card.addEventListener("click", () => markAsRead(n.id, card));

        listContainer.appendChild(card);
    });
}

// ===============================
// Load real notifications
// ===============================
async function loadNotifications() {
    try {
        const res = await fetch(`${API_BASE}/notifications/list.php`, {
            method: "GET",
            credentials: "include",
        });
        const data = await res.json();

        if (!data.success) {
            emptyBox.style.display = "block";
            return;
        }

        renderNotifications(data.data || []);
    } catch (err) {
        console.error(err);
        emptyBox.style.display = "block";
    }
}

// ===============================
// Mark single notification as read
// ===============================
async function markAsRead(id, cardEl) {
    try {
        const res = await fetch(`${API_BASE}/notifications/mark-read.php`, {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id }),
        });
        const data = await res.json();
        if (data.success) {
            cardEl.style.opacity = "0.7";
        }
    } catch (err) {
        console.error(err);
    }
}

// ===============================
// Clear (mark all as read)
// ===============================
clearBtn.addEventListener("click", async () => {
    const confirmClear = confirm("Mark all notifications as read?");
    if (!confirmClear) return;

    try {
        const res = await fetch(`${API_BASE}/notifications/mark-read.php`, {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ all: true }),
        });
        const data = await res.json();

        if (data.success) {
            document.querySelectorAll(".notification-card").forEach(card => {
                card.style.opacity = "0.7";
            });
            alert("All notifications marked as read.");
        } else {
            alert(data.message || "Could not clear notifications.");
        }
    } catch (err) {
        console.error(err);
        alert("Could not connect to the server.");
    }
});

loadNotifications();
