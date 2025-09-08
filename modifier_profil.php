<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Configuration et connexion à la base de données
$config = [
    'db_host' => 'localhost',
    'db_name' => 'dev_livre',
    'db_user' => 'root',
    'db_pass' => '',
    'db_charset' => 'utf8'
];

try {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset={$config['db_charset']}";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die("Une erreur est survenue. Veuillez réessayer plus tard.");
}

// Fonctions de validation
function validateName($name) {
    $name = trim($name);
    if (empty($name)) {
        return "Ce champ est obligatoire.";
    }
    if (!preg_match("/^[a-zA-ZÀ-ÿ\s\-']{2,50}$/u", $name)) {
        return "Le nom contient des caractères invalides.";
    }
    return true;
}

function validateEmail($email, $pdo, $currentUserId) {
    $email = trim($email);
    if (empty($email)) {
        return "L'email est obligatoire.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Format d'email invalide.";
    }
    
    // Vérifier si l'email existe déjà pour un autre utilisateur
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ? AND id_utilisateur != ?");
    $stmt->execute([$email, $currentUserId]);
    if ($stmt->fetch()) {
        return "Cet email est déjà utilisé par un autre compte.";
    }
    
    return true;
}

function validatePhone($phone) {
    if (empty($phone)) {
        return true; // Le téléphone est optionnel
    }
    
    // Nettoyer le numéro de téléphone
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Validation basique des numéros français et internationaux
    if (!preg_match('/^(\+[0-9]{1,3})?[0-9]{9,15}$/', $phone)) {
        return "Format de téléphone invalide.";
    }
    
    return true;
}

// Récupérer les informations actuelles de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des données utilisateur: " . $e->getMessage());
    die("Une erreur est survenue lors du chargement de votre profil.");
}

// Variables pour messages
$errors = [];
$success = '';

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Token de sécurité invalide. Veuillez réessayer.";
    } else {
        $prenom = trim($_POST['prenom'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        
        // Validation des données
        $prenomValidation = validateName($prenom);
        $nomValidation = validateName($nom);
        $emailValidation = validateEmail($email, $pdo, $_SESSION['user_id']);
        $phoneValidation = validatePhone($telephone);
        
        if ($prenomValidation !== true) $errors['prenom'] = $prenomValidation;
        if ($nomValidation !== true) $errors['nom'] = $nomValidation;
        if ($emailValidation !== true) $errors['email'] = $emailValidation;
        if ($phoneValidation !== true) $errors['telephone'] = $phoneValidation;
        
        // Si aucune erreur de validation, procéder à la mise à jour
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE utilisateur SET prenom = ?, nom = ?, email = ?, telephone = ? WHERE id_utilisateur = ?");
                $updated = $stmt->execute([$prenom, $nom, $email, $telephone, $_SESSION['user_id']]);
                
                if ($updated) {
                    $success = "Profil mis à jour avec succès.";
                    // Rafraîchir les infos utilisateur
                    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour. Veuillez réessayer.";
                }
            } catch (PDOException $e) {
                error_log("Erreur lors de la mise à jour du profil: " . $e->getMessage());
                $errors[] = "Une erreur est survenue lors de la mise à jour. Veuillez réessayer.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier profil - InspiLivres</title>
    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        .form-container { 
            max-width: 600px; 
            margin: 50px auto; 
            background: #fff; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .alert { margin-top: 20px; }
        .form-label {
            font-weight: 500;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
    </style>
</head>
<body>


    <div class="container py-5">
        <div class="form-container">
            <h2 class="mb-4"><i class="bi bi-person-gear me-2"></i>Modifier votre profil</h2>

            <?php if(!empty($errors) && is_array($errors)): ?>
                <div class="alert alert-danger">
                    <h5 class="alert-heading">Veuillez corriger les erreurs suivantes :</h5>
                    <ul class="mb-0">
                        <?php foreach($errors as $error): ?>
                            <?php if(is_array($error)): ?>
                                <?php foreach($error as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="profileForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-3">
                    <label for="prenom" class="form-label required-field">Prénom</label>
                    <input type="text" id="prenom" name="prenom" class="form-control <?php echo isset($errors['prenom']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" required>
                    <?php if(isset($errors['prenom'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['prenom']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="nom" class="form-label required-field">Nom</label>
                    <input type="text" id="nom" name="nom" class="form-control <?php echo isset($errors['nom']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
                    <?php if(isset($errors['nom'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['nom']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label required-field">Email</label>
                    <input type="email" id="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    <?php if(isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" class="form-control <?php echo isset($errors['telephone']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" 
                           placeholder="Ex: +33123456789 ou 0123456789">
                    <?php if(isset($errors['telephone'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['telephone']); ?></div>
                    <?php endif; ?>
                    <div class="form-text">Format international ou national (optionnel)</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="mon_espace.php" class="btn btn-secondary me-md-2"><i class="bi bi-arrow-left me-1"></i> Retour</a>
                    <button type="submit" class="btn " style="background-color:#e96308; color:white"><i class="bi bi-check-circle me-1"></i> Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-light text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">© <?php echo date('Y'); ?> InspiLivres. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation côté client basique
        document.getElementById('profileForm').addEventListener('submit', function(event) {
            let isValid = true;
            const prenom = document.getElementById('prenom');
            const nom = document.getElementById('nom');
            const email = document.getElementById('email');
            
            // Validation des champs requis
            if (!prenom.value.trim()) {
                prenom.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!nom.value.trim()) {
                nom.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!email.value.trim()) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                // Faire défiler jusqu'au premier champ invalide
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        // Retirer la classe d'erreur quand l'utilisateur commence à taper
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });
    </script>
</body>
</html>