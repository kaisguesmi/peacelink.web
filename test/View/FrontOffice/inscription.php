<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - PeaceLink</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(105deg, rgba(93, 173, 226, 0.8), rgba(123, 211, 137, 0.8)); }
        .signup-container { background-color: var(--blanc-pur); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--card-shadow); width: 100%; max-width: 500px; transition: height 0.3s ease; }
        .signup-container h1 { font-family: var(--font-titre); text-align: center; color: var(--vert-doux); margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px 15px; border: 1px solid var(--gris-moyen); border-radius: 8px; font-size: 16px; }
        .error-message { color: var(--rouge-alerte); font-size: 14px; margin-top: 5px; min-height: 20px; }
        .role-specific-fields { display: none; } /* Caché par défaut */
    </style>
</head>
<body>

    <div class="signup-container">
        <h1>Join PeaceLink</h1>
        
        <?php 
            session_start();
            if (isset($_SESSION['errors'])) {
                foreach ($_SESSION['errors'] as $error) {
                    echo "<p class='error-message' style='text-align:center;'>$error</p>";
                }
                unset($_SESSION['errors']);
            }
        ?>

        <form id="signup-form" action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <!-- Sélecteur de rôle -->
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label for="role">Join as:</label>
                <select id="role" name="role">
                    <option value="client" selected>Client</option>
                    <option value="organisation">Organization</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- Champs pour Client (visibles par défaut) -->
            <div id="client-fields" class="role-specific-fields" style="display: block;">
                <div class="form-group">
                    <label for="nom_complet">Full Name</label>
                    <input type="text" id="nom_complet" name="nom_complet">
                    <div class="error-message" id="error-nom"></div>
                </div>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="3"></textarea>
                </div>
            </div>

            <!-- Champs pour Organisation -->
            <div id="organisation-fields" class="role-specific-fields">
                <div class="form-group">
                    <label for="nom_organisation">Organization Name</label>
                    <input type="text" id="nom_organisation" name="nom_organisation">
                    <div class="error-message" id="error-orga-nom"></div>
                </div>
                 <div class="form-group">
                    <label for="adresse">Adress</label>
                    <input type="text" id="adresse" name="adresse">
                    <div class="error-message" id="error-orga-adresse"></div>
                </div>
            </div>

             <!-- Champs pour Admin -->
            <div id="admin-fields" class="role-specific-fields">
                 <div class="form-group">
                    <label for="niveau_permission">Permission level</label>
                    <input type="number" id="niveau_permission" name="niveau_permission" value="1">
                    <div class="error-message" id="error-admin-level"></div>
                </div>
            </div>

            <hr style="margin: 20px 0; border: 1px solid var(--gris-moyen);">

            <!-- Champs communs -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email">
                <div class="error-message" id="error-email"></div>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Password</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe">
                <div class="error-message" id="error-password"></div>
            </div>
            
            <button type="submit" class="btn-primary" style="width:100%;">Create Account</button>
        </form>
    </div>

    <!-- Script pour la gestion des champs dynamiques -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelector = document.getElementById('role');
            const clientFields = document.getElementById('client-fields');
            const orgaFields = document.getElementById('organisation-fields');
            const adminFields = document.getElementById('admin-fields');

            roleSelector.addEventListener('change', function() {
                // Cacher tous les champs spécifiques
                clientFields.style.display = 'none';
                orgaFields.style.display = 'none';
                adminFields.style.display = 'none';

                // Afficher les champs correspondants au rôle sélectionné
                const selectedRole = this.value;
                if (selectedRole === 'client') {
                    clientFields.style.display = 'block';
                } else if (selectedRole === 'organisation') {
                    orgaFields.style.display = 'block';
                } else if (selectedRole === 'admin') {
                    adminFields.style.display = 'block';
                }
            });
        });
    </script>
    <!-- On garde le même script de validation, mais on va le modifier -->
    <script src="js/validation.js"></script>

</body>
</html>