<?php
session_start(); // Important pour utiliser $_SESSION

try {
    $pdo = new PDO("mysql:host=localhost;dbname=dev_livre;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $accept = isset($_POST['accept']);

    if (!$accept) {
        $message = "<div class='alert alert-danger'>Vous devez accepter la politique de confidentialité.</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>Les mots de passe ne correspondent pas.</div>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $message = "<div class='alert alert-danger'>Cet email est déjà utilisé.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, password) VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$nom, $prenom, $email, $hashed_password])) {
                // Définir la session pour le flash message
                $_SESSION['success'] = "Inscription réussie ! Vous pouvez vous connecter.";
                header("Location: login.php");
                exit();
            } else {
                $message = "<div class='alert alert-danger'>Erreur lors de l'inscription.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription - InspiLivres</title>
  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet"><style>
:root {
    --primary-color: #e96308;
    --secondary-color: #2c3e50;
    --light-bg: #f8f9fa;
}

body {
    font-family: 'Roboto', sans-serif;
    background-color: var(--light-bg);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
}

.registration-container {
    max-width: 600px;
    width: 100%;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.registration-header {
    background: linear-gradient(135deg, var(--primary-color), #ff8c42);
    color: white;
    padding: 30px;
    text-align: center;
}

.registration-header h1 {
    font-weight: 700;
    margin-bottom: 10px;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s forwards 0.3s;
}

.registration-header p {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s forwards 0.5s;
}

.registration-form {
    padding: 40px;
}

.form-group {
    margin-bottom: 20px;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s forwards;
}

.form-group:nth-child(1) { animation-delay: 0.7s; }
.form-group:nth-child(2) { animation-delay: 0.8s; }
.form-group:nth-child(3) { animation-delay: 0.9s; }
.form-group:nth-child(4) { animation-delay: 1.0s; }
.form-group:nth-child(5) { animation-delay: 1.1s; }

.form-control {
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(233, 99, 8, 0.25);
}

.form-label {
    font-weight: 500;
    margin-bottom: 8px;
    color: var(--secondary-color);
}

.btn-register {
    background: var(--primary-color);
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    width: 100%;
    transition: all 0.3s;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s forwards 1.4s;
}

.btn-register:hover {
    background: #d15807;
    transform: translateY(-2px);
}

.login-redirect {
    text-align: center;
    margin-top: 20px;
    color: #6c757d;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s forwards 1.6s;
}

.login-redirect a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.password-toggle {
    cursor: pointer;
    position: absolute;
    right: 15px;
    top: 42px;
    color: #6c757d;
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.alert {
    border-radius: 8px;
    margin-bottom: 20px;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s forwards 0.2s;
}

.policy-link {
    color: var(--primary-color) !important;
    text-decoration: none;
    font-weight: 500;
}

.policy-link:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    body {
        padding: 10px;
    }
    
    .registration-form {
        padding: 25px;
    }
    
    .registration-header {
        padding: 20px;
    }
    
    .registration-header h1 {
        font-size: 1.5rem;
    }
    
    .form-group {
        animation-delay: 0.5s !important;
    }
    
    .form-group:nth-child(1) { animation-delay: 0.6s; }
    .form-group:nth-child(2) { animation-delay: 0.7s; }
    .form-group:nth-child(3) { animation-delay: 0.8s; }
    .form-group:nth-child(4) { animation-delay: 0.9s; }
    .form-group:nth-child(5) { animation-delay: 1.0s; }
    
    .btn-register {
        animation-delay: 1.2s;
    }
    
    .login-redirect {
        animation-delay: 1.4s;
    }
}
</style>
</head>

<body>
  <div class="registration-container">
    <div class="registration-header">
      <h1><i class="bi bi-person-plus"></i> Créer un compte</h1>
      <p>Rejoignez la communauté InspiLivres et accédez à tous nos livres de développement personnel</p>
    </div>
    
    <div class="registration-form">
      <?php echo $message; ?>
      <form id="registerForm" method="POST" action="">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="prenom" class="form-label">Prénom *</label>
              <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="nom" class="form-label">Nom *</label>
              <input type="text" class="form-control" id="nom" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="email" class="form-label">Adresse email *</label>
          <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <div class="form-group position-relative">
              <label for="password" class="form-label">Mot de passe *</label>
              <input type="password" class="form-control" id="password" name="password" required minlength="4" maxlength="12">
              <span class="password-toggle" id="togglePassword">
                <i class="bi bi-eye"></i>
              </span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group position-relative">
              <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="4" maxlength="12">
              <span class="password-toggle" id="toggleConfirmPassword">
                <i class="bi bi-eye"></i>
              </span>
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="accept" name="accept" <?php echo (isset($_POST['accept']) && $_POST['accept']) ? 'checked' : ''; ?> required>
            <label class="form-check-label" for="accept">
              J'accepte la <a href="politique.html" target="_blank" class="policy-link">politique de confidentialité</a> *
            </label>
          </div>
        </div>
        
        <button type="submit" class="btn btn-register">S'inscrire</button>
        
        <div class="login-redirect">
          <p>Vous avez déjà un compte ? <a href="login.php" class="policy-link">Connectez-vous ici</a></p>
        </div>
      </form>
    </div>
  </div>

  <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
        class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Fonctionnalité d'affichage/masquage du mot de passe
      const togglePassword = document.querySelector('#togglePassword');
      const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
      const password = document.querySelector('#password');
      const confirmPassword = document.querySelector('#confirm_password');
      
      if (togglePassword) {
        togglePassword.addEventListener('click', function() {
          const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
          password.setAttribute('type', type);
          this.querySelector('i').classList.toggle('bi-eye');
          this.querySelector('i').classList.toggle('bi-eye-slash');
        });
      }
      
      if (toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener('click', function() {
          const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
          confirmPassword.setAttribute('type', type);
          this.querySelector('i').classList.toggle('bi-eye');
          this.querySelector('i').classList.toggle('bi-eye-slash');
        });
      }
      
      // Validation du formulaire
      const registerForm = document.getElementById('registerForm');
      
      if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
          // Validation basique
          const passwordValue = password.value;
          const confirmPasswordValue = confirmPassword.value;
          
          if (passwordValue !== confirmPasswordValue) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return;
          }
          
          if (passwordValue.length < 4) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 4 caractères.');
            return;
          }
        });
      }
    });
  </script>
  <!-- Main JS File -->
    <script src="assets/js/main.js"></script>
</body>
</html>