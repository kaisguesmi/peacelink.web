document.addEventListener('DOMContentLoaded', function() {
    
    // --- GESTION DE LA SIDEBAR ---
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => sidebar.classList.toggle('collapsed'));
    }

    // --- GESTION DES NOTIFICATIONS (TOASTS) ---
    const handleStatusMessages = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status) {
            let message = '';
            let type = 'success';
            switch (status) {
                case 'created': message = 'Offre publiée avec succès !'; break;
                case 'updated': message = 'Offre mise à jour avec succès !'; break;
                case 'deleted': message = 'Offre supprimée.'; break;
                case 'applied': message = 'Votre candidature a été envoyée !'; break;
                case 'app_updated': message = 'Le statut de la candidature a été mis à jour.'; break;
                case 'error': message = 'Une erreur est survenue.'; type = 'error'; break;
                default: return;
            }
            const notification = document.createElement('div');
            notification.className = `status-notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => { notification.remove(); }, 500);
            }, 4000);
            const role = urlParams.get('role');
            const newUrl = window.location.pathname + (role ? `?role=${role}` : '');
            history.replaceState({}, '', newUrl);
        }
    };
    handleStatusMessages();

    // --- GESTION DE LA VALIDATION JS DES FORMULAIRES ---
    const applicationForm = document.getElementById('application-form');
    const offerForm = document.getElementById('offer-form');

    function showError(input, message) {
        const errorDiv = input.parentElement.querySelector('.error-message');
        input.classList.add('input-error');
        errorDiv.textContent = message;
        errorDiv.classList.add('visible');
    }

    function clearError(input) {
        const errorDiv = input.parentElement.querySelector('.error-message');
        input.classList.remove('input-error');
        errorDiv.classList.remove('visible');
        errorDiv.textContent = '';
    }

    if (applicationForm) {
        applicationForm.addEventListener('submit', function(e) {
            let isValid = true;
            const name = document.getElementById('candidate_name');
            const email = document.getElementById('candidate_email');
            const motivation = document.getElementById('motivation');
            [name, email, motivation].forEach(input => clearError(input));

            if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(name.value.trim())) {
                isValid = false;
                showError(name, 'Le nom ne doit contenir que des lettres et des espaces.');
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                isValid = false;
                showError(email, 'Veuillez entrer une adresse email valide.');
            }
            if (motivation.value.trim().length < 20) {
                isValid = false;
                showError(motivation, 'La motivation doit faire au moins 20 caractères.');
            }
            if (!isValid) e.preventDefault();
        });
    }

    if (offerForm) {
        offerForm.addEventListener('submit', function(e) {
            let isValid = true;
            const title = document.getElementById('title');
            const description = document.getElementById('description');
            [title, description].forEach(input => clearError(input));

            if (title.value.trim().length < 5) {
                isValid = false;
                showError(title, 'Le titre doit faire au moins 5 caractères.');
            }
            if (description.value.trim().length < 20) {
                isValid = false;
                showError(description, 'La description doit faire au moins 20 caractères.');
            }
            if (!isValid) e.preventDefault();
        });
    }
});