// ===================== CONFIG =====================
const API_URL = "http://localhost/2A4/projet/peacelink/api_events.php";
let events = [];

// ===================== FETCH =====================
async function fetchEventsFromServer() {
    try {
        const response = await fetch(API_URL + "?action=list");
        events = await response.json();
        renderInitiativesTable();
        updateDashboardStats();
    } catch (err) {
        console.error("Erreur API LIST :", err);
    }
}

// ===================== UPDATE =====================
async function setEventStatus(eventId, status) {
    try {
        const res = await fetch(API_URL + "?action=updateStatus", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ eventId, status })
        });

        const data = await res.json();
        if (!data.success) {
            alert("Erreur lors du changement de statut.");
            return;
        }

        const ev = events.find(e => e.id == eventId);
        if (ev) ev.status = status;

        renderInitiativesTable();
        updateDashboardStats();

    } catch (err) {
        console.error("Erreur UPDATE :", err);
    }
}

// ===================== RENDER TABLE =====================
function formatDate(dateStr) {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("fr-FR", {
        year: "numeric",
        month: "long",
        day: "numeric"
    });
}

function renderInitiativesTable() {
    const tbody = document.getElementById("initiatives-table-body");
    if (!tbody) return;

    tbody.innerHTML = "";

    events.forEach(event => {
        const tr = document.createElement("tr");
        const orgName = event.created_by || event.createdBy || "Organisation";

        tr.innerHTML = `
            <td>${event.title}</td>
            <td>${orgName}</td>
            <td>${event.category}</td>
            <td>${event.location}</td>
            <td>${formatDate(event.date)}</td>
            <td>
                <span class="status-badge ${event.status}">
                    ${event.status === 'validé' ? 'Validée' : 
                       event.status === 'en_attente' ? 'En attente' : 
                       'Refusée'}
                </span>
            </td>
            <td class="actions-cell">
                <button class="action-btn" title="Voir"><i class="fa-solid fa-eye"></i></button>
                <button class="action-btn success" title="Valider" onclick="setEventStatus(${event.id}, 'validé')"><i class="fa-solid fa-check"></i></button>
                <button class="action-btn danger" title="Refuser" onclick="setEventStatus(${event.id}, 'refusé')"><i class="fa-solid fa-xmark"></i></button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

// ===================== STATS =====================
function updateDashboardStats() {
    document.getElementById("stat-active-initiatives").textContent =
        events.filter(e => e.status === "validé").length;

    document.getElementById("stat-pending-initiatives").textContent =
        events.filter(e => e.status === "en_attente").length;
}

// ===================== NAVIGATION =====================
function setupNavigation() {
    const navItems = document.querySelectorAll(".nav-item");
    const pages = document.querySelectorAll(".page-content");

    navItems.forEach(item => {
        item.addEventListener("click", e => {
            e.preventDefault();
            const pageName = item.dataset.page;

            navItems.forEach(n => n.classList.remove("active"));
            item.classList.add("active");

            pages.forEach(p => p.classList.remove("active"));
            document.getElementById("page-" + pageName).classList.add("active");
        });
    });
}

function setupSidebarToggle() {
    document.getElementById("menu-toggle").addEventListener("click", () => {
        document.querySelector(".sidebar").classList.toggle("collapsed");
    });
}

// ===================== INIT =====================
document.addEventListener("DOMContentLoaded", async () => {
    setupNavigation();
    setupSidebarToggle();
    await fetchEventsFromServer();
});
