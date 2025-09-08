<?php
// create_admin.php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dev_livre";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifier si la colonne 'role' existe
$check_column_sql = "SHOW COLUMNS FROM utilisateur LIKE 'role'";
$result = $conn->query($check_column_sql);

if ($result->num_rows === 0) {
    // Ajouter la colonne role si elle n'existe pas
    $alter_sql = "ALTER TABLE utilisateur ADD COLUMN role VARCHAR(20) DEFAULT 'user'";
    if ($conn->query($alter_sql)) {
        echo "<div class='alert alert-success'>Colonne 'role' ajoutée à la table utilisateur</div>";
    } else {
        echo "<div class='alert alert-danger'>Erreur lors de l'ajout de la colonne: " . $conn->error . "</div>";
    }
}

// Créer le compte admin
$email = "admin@inspilivres.com";
$password_hash = password_hash("admin123", PASSWORD_DEFAULT);
$prenom = "Admin";
$nom = "Système";
$role = "admin";

// Vérifier si l'admin existe déjà
$check_sql = "SELECT id_utilisateur FROM utilisateur WHERE email = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$success = false;

if ($result->num_rows === 0) {
    // Créer l'admin (sans profession)
    $insert_sql = "INSERT INTO utilisateur (email, password, prenom, nom, role, date_inscription) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sssss", $email, $password_hash, $prenom, $nom, $role);
    
    if ($stmt->execute()) {
        $success = true;
        $message = "Compte admin créé avec succès !";
    } else {
        $message = "Erreur lors de la création: " . $conn->error;
    }
} else {
    // Mettre à jour le rôle si l'admin existe déjà
    $update_sql = "UPDATE utilisateur SET role = ? WHERE email = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ss", $role, $email);
    
    if ($stmt->execute()) {
        $success = true;
        $message = "Compte admin mis à jour avec succès !";
    } else {
        $message = "Erreur lors de la mise à jour: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création Compte Admin - InspiLivres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .admin-create-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .admin-icon {
            font-size: 4rem;
            color: #e96308;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-create-card text-center">
        <i class="bi bi-person-plus admin-icon"></i>
        <h2 class="mb-4">Création du Compte Administrateur</h2>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="credentials-box bg-light p-4 rounded mb-4">
                <h5>Identifiants de connexion :</h5>
                <div class="mb-2">
                    <strong>Email :</strong> admin@inspilivres.com
                </div>
                <div class="mb-3">
                    <strong>Mot de passe :</strong> admin123
                </div>
                <small class="text-danger">
                    ⚠️ Notez ces identifiants et changez le mot de passe après la première connexion !
                </small>
            </div>
        <?php endif; ?>
        
        <div class="d-grid gap-2">
            <a href="admin_login.php" class="btn btn-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Se connecter à l'administration
            </a>
            <a href="accueil.php" class="btn btn-outline-secondary">
                <i class="bi bi-house me-2"></i>
                Retour à l'accueil
            </a>
        </div>
        
        <?php if (!$success): ?>
            <div class="mt-4">
                <button onclick="window.location.reload()" class="btn btn-warning">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    Réessayer
                </button>
            </div>
        <?php endif; ?>
        
        <div class="mt-4 text-muted">
            <small>
                Cette page doit être supprimée en production pour des raisons de sécurité.
            </small>
        </div>
    </div>
</body>
</html>