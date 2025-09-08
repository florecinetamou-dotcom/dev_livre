<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=dev_livre;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Flash message pour inscription réussie
$flash_message = '';
if (isset($_SESSION['success'])) {
    $flash_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

$error = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Chercher l'utilisateur par email
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Stocker les infos en session
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['user_name'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'] ?? 'user'; // par défaut 'user'

            // Redirection selon le rôle
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin_livre.php");
                exit();
            } else {
                header("Location: accueil.php");
                exit();
            }
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - InspiLivres</title>
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
  <link href="assets/css/main.css" rel="stylesheet"> <style>
        :root {
            --primary-color: #e96308;
            --primary-hover: #d65a00;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --dark-text: #333;
            --light-text: #fff;
            --error-color: #dc3545;
            --success-color: #28a745;
            --border-radius: 8px;
            --box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="%23f8f8f8" width="100" height="100"/><path fill="%23e96308" opacity="0.2" d="M0,0 L100,100 M100,0 L0,100" stroke-width="1"/></svg>');
            background-size: 30px;
        }
        
        .login-container {
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            display: flex;
            min-height: 600px;
        }
        
        .login-hero {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            color: var(--light-text);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .login-hero::before, .login-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .login-hero::before {
            width: 150px;
            height: 150px;
            top: -50px;
            right: -50px;
        }
        
        .login-hero::after {
            width: 100px;
            height: 100px;
            bottom: -30px;
            left: -30px;
        }
        
        .hero-content {
            text-align: center;
            z-index: 1;
            max-width: 400px;
        }
        
        .hero-content h2 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .hero-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .login-form-container {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-header {
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            font-size: 1.8rem;
            color: var(--dark-text);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(233, 99, 8, 0.2);
            outline: none;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 42px;
            background: none;
            border: none;
            color: var(--secondary-color);
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            color: white;
            padding: 15px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(214, 90, 0, 0.3);
        }
        
        .form-footer {
            margin-top: 20px;
            text-align: center;
        }
        
        .form-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--secondary-color);
        }
        
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        
        .divider span {
            padding: 0 15px;
            font-size: 0.9rem;
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .social-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg);
            border: 1px solid #ddd;
            transition: var(--transition);
            color: var(--dark-text);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        
        .social-btn i {
            transition: var(--transition);
            position: relative;
            z-index: 2;
        }
        
        .social-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-color);
            border-radius: 50%;
            transform: scale(0);
            transition: var(--transition);
            z-index: 1;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow);
            border-color: var(--primary-color);
        }
        
        .social-btn:hover::before {
            transform: scale(1);
        }
        
        .social-btn:hover i {
            color: white;
            transform: scale(1.2);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: var(--error-color);
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: var(--success-color);
            border: 1px solid #c3e6cb;
        }
        
        .password-strength {
            margin-top: 8px;
            height: 5px;
            border-radius: 5px;
            background: #eee;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            transition: var(--transition);
        }
        
        .strength-weak {
            background-color: var(--error-color);
            width: 33%;
        }
        
        .strength-medium {
            background-color: #ffc107;
            width: 66%;
        }
        
        .strength-strong {
            background-color: var(--success-color);
            width: 100%;
        }
        
        .floating-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.5s ease;
            font-weight: 500;
            color: white;
        }
        
        .floating-success {
            background-color: var(--success-color);
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                min-height: auto;
            }
            
            .login-hero, .login-form-container {
                width: 100%;
                padding: 30px;
            }
            
            .login-hero {
                order: 2;
                border-radius: 0 0 15px 15px;
            }
            
            .login-form-container {
                order: 1;
            }
            
            .hero-content h2 {
                font-size: 1.8rem;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }
        .delay-5 { animation-delay: 1s; }
    </style>
</head>
<body>

<?php if($flash_message): ?>
<div class="floating-message floating-success" id="flash-message">
    <?php echo $flash_message; ?>
</div>
<script>
setTimeout(() => {
    const msg = document.getElementById('flash-message');
    if(msg) {
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 500);
    }
}, 4000);
</script>
<?php endif; ?>

<div class="login-container">
    <div class="login-hero">
        <div class="hero-content">
            <h2>Heureux de vous revoir!</h2>
            <p> <a href="admin_login.php" style="color: white; text-decoration: none;">Connectez-vous</a>  pour accéder à votre compte et découvrir nos livres de développement personnel</p>
        </div>
    </div>
    
    <div class="login-form-container">
        <div class="form-header animate-fade-in">
            <h2>Connectez-vous ici</h2>
        </div>
        
        <?php if($error): ?>
        <div class="alert alert-error animate-fade-in delay-1">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form action="" method="POST" class="animate-fade-in delay-2">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="exemple@gmail.com" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Entrer le mot de passe" required minlength="4" maxlength="12">
                <button type="button" class="password-toggle" id="password-toggle">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-primary">Se connecter</button>
            </div>
        </form>
        
        <div class="form-footer animate-fade-in delay-3">
            <a href="forgot-password.php">Mot de passe oublié ?</a>
        </div>
        
        <div class="divider animate-fade-in delay-4">
            <span>Ou</span>
        </div>
        
        <div class="social-login animate-fade-in delay-4">
            <a href="#" class="social-btn" title="Se connecter avec Facebook">
                <i class="bi bi-facebook"></i>
            </a>
            <a href="#" class="social-btn" title="Se connecter avec Google">
                <i class="bi bi-google"></i>
            </a>
        </div>
        
        <div class="form-footer animate-fade-in delay-5">
            <p>Vous n'avez pas encore de compte ? <a href="register.php">Créez-en un</a></p>
        </div>
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
        // Gestionnaire d'affichage/masquage du mot de passe
        const passwordToggle = document.getElementById('password-toggle');
        const passwordInput = document.getElementById('password');
        
        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    passwordToggle.innerHTML = '<i class="bi bi-eye-slash"></i>';
                } else {
                    passwordInput.type = 'password';
                    passwordToggle.innerHTML = '<i class="bi bi-eye"></i>';
                }
            });
        }
    });
</script>
<!-- Main JS File -->
    <script src="assets/js/main.js"></script>
</body>
</html>