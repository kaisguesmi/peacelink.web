// ===================== CONFIG =====================
const API_URL = "http://localhost/2A4/projet/peacelink/api_events.php";

let events = [];          // événements de la BDD
let currentRole = "client";
let currentEvent = null;  // événement actuellement consulté


// ===================== FETCH EVENTS (depuis MySQL) =====================
async function fetchEvents() {
    try {
        const res = await fetch(API_URL + "?action=list");
        events = await res.json();
        renderEventsList();
        renderAdminList();
    } catch (err) {
        console.error("Erreur lors du chargement des initiatives :", err);
    }
}


// ===================== FORMAT DATE =====================
function formatDate(dateStr) {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("fr-FR", {
        year: "numeric",
        month: "long",
        day: "numeric"
    });
}


// ===================== LISTE POUR CLIENT / ORGANISATION =====================
function renderEventsList() {
    const container = document.getElementById("events-list");
    if (!container) return;

    container.innerHTML = "";

    // Filtrage selon le rôle
    const visibleEvents = events.filter(e => {
        if (currentRole === "client") {
            return e.status === "validé"; // le client ne voit que les initiatives validées
        }
        // organisation & admin voient tout
        return true;
    });

    if (!visibleEvents.length) {
        container.innerHTML = "<p>Aucune initiative pour le moment.</p>";
        return;
    }

    visibleEvents.forEach(evt => {
        const card = document.createElement("article");
        card.className = "mission-card event-card";

        const statusLabel =
            evt.status === "validé" ? "Validée" :
            evt.status === "en_attente" ? "En attente de validation" :
            "Refusée";

        card.innerHTML = `
            <h3>${evt.title}</h3>
            <p class="event-meta">
                <strong>${evt.category}</strong> • 
                ${evt.location} • 
                ${formatDate(evt.date)}
            </p>
            <p class="event-desc">${evt.description || ""}</p>
            <p class="event-status">
                Statut : <span class="status-badge ${evt.status}">${statusLabel}</span>
            </p>
            <button class="btn-secondary btn-view-event" data-id="${evt.id}">
                Voir les détails
            </button>
        `;

        // Pour l'admin : actions inline dans la liste si en attente
        if (currentRole === "admin" && evt.status === "en_attente") {
            const actionsAdmin = document.createElement("div");
            actionsAdmin.className = "event-admin-inline";

            const btnValider = document.createElement("button");
            btnValider.className = "btn-success btn-small";
            btnValider.textContent = "Valider";
            btnValider.addEventListener("click", () => updateEventStatus(evt.id, "validé"));

            const btnRefuser = document.createElement("button");
            btnRefuser.className = "btn-danger btn-small";
            btnRefuser.textContent = "Refuser";
            btnRefuser.addEventListener("click", () => updateEventStatus(evt.id, "refusé"));

            actionsAdmin.appendChild(btnValider);
            actionsAdmin.appendChild(btnRefuser);
            card.appendChild(actionsAdmin);
        }

        container.appendChild(card);
    });

    // boutons "Voir les détails"
    document.querySelectorAll(".btn-view-event").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            openEventDetail(id);
        });
    });
}


// ===================== LISTE ADMIN (section du bas) =====================
function renderAdminList() {
    const container = document.getElementById("admin-events-list");
    if (!container) return;

    container.innerHTML = "";

    const pendingEvents = events.filter(e => e.status === "en_attente");

    if (!pendingEvents.length) {
        container.innerHTML = "<p>Aucune initiative en attente pour le moment.</p>";
        return;
    }

    pendingEvents.forEach(evt => {
        const card = document.createElement("article");
        card.className = "mission-card event-card";

        card.innerHTML = `
            <h3>${evt.title}</h3>
            <p><strong>Catégorie :</strong> ${evt.category}</p>
            <p><strong>Lieu :</strong> ${evt.location}</p>
            <p><strong>Date :</strong> ${formatDate(evt.date)}</p>
            <p><strong>Statut :</strong> ${evt.status}</p>
        `;

        const actions = document.createElement("div");
        actions.className = "event-card-footer";

        const btnValider = document.createElement("button");
        btnValider.className = "btn-success btn-small";
        btnValider.textContent = "Valider";
        btnValider.addEventListener("click", () => updateEventStatus(evt.id, "validé"));

        const btnRefuser = document.createElement("button");
        btnRefuser.className = "btn-danger btn-small";
        btnRefuser.textContent = "Refuser";
        btnRefuser.addEventListener("click", () => updateEventStatus(evt.id, "refusé"));

        actions.appendChild(btnValider);
        actions.appendChild(btnRefuser);
        card.appendChild(actions);

        container.appendChild(card);
    });
}


// ===================== MISE À JOUR STATUT (Admin, via API) =====================
async function updateEventStatus(eventId, status) {
    try {
        const res = await fetch(API_URL + "?action=updateStatus", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ eventId, status })
        });

        const result = await res.json();
        if (!result.success) {
            alert("Erreur lors de la mise à jour du statut.");
            return;
        }

        // Mettre à jour localement
        const evt = events.find(e => String(e.id) === String(eventId));
        if (evt) evt.status = status;

        renderEventsList();
        renderAdminList();

        // si on est sur la page détail, recharger le détail
        if (currentEvent && String(currentEvent.id) === String(eventId)) {
            openEventDetail(eventId);
        }
    } catch (err) {
        console.error("Erreur update statut :", err);
        alert("Erreur réseau lors de la mise à jour.");
    }
}


// ===================== DÉTAIL D’UNE INITIATIVE =====================
function openEventDetail(id) {
    currentEvent = events.find(e => String(e.id) === String(id));
    if (!currentEvent) return;

    const detailSection = document.getElementById("event-detail-section");
    const detailCard    = document.getElementById("event-detail-card");
    const listSection   = document.querySelector(".initiatives-main .mission-section");
    const createSection = document.getElementById("event-create-section");
    const adminSection  = document.getElementById("admin-section");

    const statusLabel =
        currentEvent.status === "validé" ? "Validée" :
        currentEvent.status === "en_attente" ? "En attente de validation" :
        "Refusée";

    detailCard.innerHTML = `
        <h2>${currentEvent.title}</h2>
        <p class="event-meta">
            <strong>${currentEvent.category}</strong> • 
            ${currentEvent.location} • 
            ${formatDate(currentEvent.date)}
        </p>
        <p>${currentEvent.description || ""}</p>
        <p class="event-status">
            Statut : <span class="status-badge ${currentEvent.status}">${statusLabel}</span>
        </p>
    `;

    // Participation : seulement si validé ET rôle = client
    const partWrapper = document.querySelector(".participation-form-wrapper");
    const feedback    = document.getElementById("participation-feedback");
    const submitBtn   = document.querySelector("#participation-form button[type='submit']");

    if (currentEvent.status !== "validé" || currentRole !== "client") {
        if (partWrapper) partWrapper.style.display = "none";
        if (feedback) {
            feedback.textContent = "La participation n'est possible que pour les initiatives validées.";
            feedback.style.color = "red";
        }
    } else {
        if (partWrapper) partWrapper.style.display = "block";
        if (feedback) {
            feedback.textContent = "";
            feedback.style.color = "";
        }
    }

    // Afficher / masquer sections
    if (listSection)   listSection.classList.add("hidden");
    if (createSection) createSection.classList.add("hidden");
    if (adminSection)  adminSection.classList.add("hidden");
    if (detailSection) detailSection.classList.remove("hidden");
}


// ===================== FORMULAIRE PARTICIPATION (simulé) =====================
function setupParticipationForm() {
    const form = document.getElementById("participation-form");
    if (!form) return;

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const feedback = document.getElementById("participation-feedback");

        if (!currentEvent || currentEvent.status !== "validé") {
            if (feedback) {
                feedback.textContent = "Impossible de participer à une initiative non validée.";
                feedback.style.color = "red";
            }
            return;
        }

        // On pourrait ici envoyer vers une autre API (participations)
        if (feedback) {
            feedback.textContent = "Votre participation est enregistrée (simulation).";
            feedback.style.color = "green";
        }
        form.reset();
    });
}


// ===================== FORMULAIRE CRÉATION (Organisation -> BDD) =====================
function setupCreateEventForm() {
    const form = document.getElementById("create-event-form");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const feedback = document.getElementById("create-event-feedback");
        if (feedback) {
            feedback.textContent = "";
            feedback.style.color = "";
        }

        const data = {
            title:       document.getElementById("event-title").value.trim(),
            category:    document.getElementById("event-category").value,
            location:    document.getElementById("event-location").value.trim(),
            date:        document.getElementById("event-date").value,
            capacity:    parseInt(document.getElementById("event-capacity").value, 10),
            description: document.getElementById("event-description").value.trim(),
            created_by:  "Organisation" // tu peux mettre le vrai nom plus tard
        };

        if (!data.title || !data.location || !data.date || !data.description || !data.capacity) {
            if (feedback) {
                feedback.textContent = "Merci de remplir tous les champs.";
                feedback.style.color = "red";
            }
            return;
        }

        try {
            const res = await fetch(API_URL + "?action=create", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            const result = await res.json();

            if (result.success) {
                if (feedback) {
                    feedback.textContent = "Initiative créée et en attente de validation par l'admin.";
                    feedback.style.color = "green";
                }
                form.reset();
                await fetchEvents(); // recharger depuis la BDD
            } else {
                if (feedback) {
                    feedback.textContent = "Erreur : " + (result.error || "Impossible d'enregistrer.");
                    feedback.style.color = "red";
                }
            }
        } catch (err) {
            console.error("Erreur création initiative :", err);
            if (feedback) {
                feedback.textContent = "Erreur réseau lors de l'envoi.";
                feedback.style.color = "red";
            }
        }
    });
}


// ===================== ROLE SWITCHER =====================
function setupRoleSwitcher() {
    const select = document.getElementById("role-select");
    const createBtn = document.getElementById("btn-open-create");
    const participationWrapper = document.querySelector(".participation-form-wrapper");
    const adminSection = document.getElementById("admin-section");

    if (!select) return;

    function applyRole(role) {
        currentRole = role;

        // Client
        if (role === "client") {
            if (createBtn) createBtn.style.display = "none";
            if (participationWrapper) participationWrapper.style.display = "block";
            if (adminSection) adminSection.classList.add("hidden");
        }
        // Organisation
        else if (role === "organisation") {
            if (createBtn) createBtn.style.display = "inline-flex";
            if (participationWrapper) participationWrapper.style.display = "none";
            if (adminSection) adminSection.classList.add("hidden");
        }
        // Admin
        else if (role === "admin") {
            if (createBtn) createBtn.style.display = "none";
            if (participationWrapper) participationWrapper.style.display = "none";
            if (adminSection) adminSection.classList.remove("hidden");
        }

        renderEventsList();
        renderAdminList();
    }

    applyRole(select.value);

    select.addEventListener("change", () => {
        applyRole(select.value);
    });
}


// ===================== BOUTONS NAVIGATION (liste / détail / création) =====================
function setupNavigationButtons() {
    const listSection   = document.querySelector(".initiatives-main .mission-section");
    const detailSection = document.getElementById("event-detail-section");
    const createSection = document.getElementById("event-create-section");
    const adminSection  = document.getElementById("admin-section");

    const btnOpenCreate  = document.getElementById("btn-open-create");
    const btnCloseCreate = document.getElementById("btn-close-create");
    const btnBackToList  = document.getElementById("btn-back-to-list");

    if (btnOpenCreate) {
        btnOpenCreate.addEventListener("click", () => {
            if (listSection)   listSection.classList.add("hidden");
            if (detailSection) detailSection.classList.add("hidden");
            if (adminSection)  adminSection.classList.add("hidden");
            if (createSection) createSection.classList.remove("hidden");
        });
    }

    if (btnCloseCreate) {
        btnCloseCreate.addEventListener("click", () => {
            if (createSection) createSection.classList.add("hidden");
            if (detailSection) detailSection.classList.add("hidden");
            if (adminSection)  adminSection.classList.add("hidden");
            if (listSection)   listSection.classList.remove("hidden");
        });
    }

    if (btnBackToList) {
        btnBackToList.addEventListener("click", () => {
            if (detailSection) detailSection.classList.add("hidden");
            if (createSection) createSection.classList.add("hidden");
            if (adminSection)  adminSection.classList.add("hidden");
            if (listSection)   listSection.classList.remove("hidden");
        });
    }
}


// ===================== INIT =====================
document.addEventListener("DOMContentLoaded", async () => {
    setupRoleSwitcher();
    setupCreateEventForm();
    setupParticipationForm();
    setupNavigationButtons();
    await fetchEvents(); // charge les initiatives depuis MySQL
});
