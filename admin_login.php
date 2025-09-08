<?php
session_start();

// Rediriger si déjà connecté en tant qu'admin
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: gestion_livres.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connexion à la base de données
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "dev_livre";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Rechercher l'utilisateur avec rôle admin
    $sql = "SELECT * FROM utilisateur WHERE email = ? AND role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Vérifier le mot de passe
        if (password_verify($password, $user['password'])) {
            // Connexion admin réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_role'] = $user['role'];

            header('Location: gestion_livres.php');
            exit();
        } else {
            $error = "Mot de passe incorrect";
        }
    } else {
        $error = "Accès administrateur non autorisé";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur - InspiLivres</title>

    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #e96308;
            --primary-hover: #d45607;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --border-color: #ecf0f1;
        }
        
        body {
            background:#ecf0f1 ;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .admin-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .admin-logo {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .admin-title {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .admin-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(233, 99, 8, 0.25);
        }

        .input-group {
            position: relative;
        }

        .input-group .form-control {
            padding-right: 50px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            z-index: 5;
            font-size: 1.2rem;
        }

        .btn-admin {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            color: white;
            width: 100%;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(233, 99, 8, 0.3);
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-color) 100%);
        }

        .alert-admin {
            border-radius: 12px;
            border: none;
            font-weight: 500;
            padding: 15px;
        }

        .admin-footer {
            text-align: center;
            margin-top: 30px;
            color: var(--text-light);
        }

        .admin-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .admin-footer a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .admin-login-card {
            animation: fadeIn 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .admin-login-card {
                padding: 25px;
            }
            
            .admin-title {
                font-size: 1.5rem;
            }
            
            .admin-logo {
                font-size: 2.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="admin-login-card">
        <!-- Header -->
        <div class="admin-header">
            <div class="admin-logo">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="admin-title">Espace Administrateur</h1>
            <p class="admin-subtitle">InspiLivres - Panel de gestion</p>
        </div>

        <!-- Messages d'erreur -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-admin mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form method="POST">
            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="form-label">Email administrateur</label>
                <input type="email" class="form-control" id="email" name="email" required 
                       placeholder="votre@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <!-- Mot de passe -->
            <div class="mb-4">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required
                           placeholder="Votre mot de passe">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Bouton de connexion -->
            <button type="submit" class="btn btn-admin">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Se connecter
            </button>
        </form>

        <!-- Footer -->
        <div class="admin-footer">
            <p>© 2025 InspiLivres - Administration</p>
            <p><a href="accueil.php">← Retour au site public</a></p>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Fonction pour afficher/masquer le mot de passe
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        }

        // Focus sur le champ email au chargement
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>

</html>