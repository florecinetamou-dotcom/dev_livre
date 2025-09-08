<?php
session_start();
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Mes Téléchargements - InspiLivres";

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dev_livre";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les téléchargements de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "
    SELECT t.*, p.titre, p.image, p.description, p.fichier, c.date_commande 
    FROM telechargements t
    JOIN produits p ON t.id_produit = p.id
    JOIN commande c ON t.id_commande = c.id_commande
    WHERE t.id_utilisateur = ? AND c.statut = 'paye' AND t.date_expiration > NOW()
    ORDER BY c.date_commande DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$telechargements = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo $pageTitle; ?></title>

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
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/telechargements.css" rel="stylesheet">
</head>

<body>
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="accueil.php" class="logo d-flex align-items-center me-auto">
                <h1 class="sitename" style="color: #e96308;">InspiLivres</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="accueil.php">Accueil</a></li>
                    <li><a href="catalogue.php">Catalogue</a></li>
                    <li><a href="about.php">À propos</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="mes_telechargements.php" class="active">Téléchargements</a></li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Utilisateur connecté - Déconnexion seulement dans le nav -->
                        <li><a href="logout.php" class="color:white;">Déconnexion</a></li>
                    <?php else: ?>
                        <!-- Utilisateur non connecté -->
                        <li><a href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Connexion</a></li>
                        <li><a href="register.php"><i class="bi bi-person-plus me-1"></i>Inscription</a></li>
                    <?php endif; ?>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list" style="color: #e96308;"></i>
            </nav>

            <div class="header-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Bouton "Mon espace" pour utilisateur connecté (dans les boutons d'header) -->
                    <a href="mon_espace.php" class="btn-mon-espace">
                        <i class="bi bi-person-circle me-1"></i>Mon espace
                    </a>
                <?php endif; ?>

                <!-- Bouton Panier -->
                <a class="btn-panier" href="panier.php">
                    <i class="bi bi-cart"></i> Panier
                    <span class="cart-count-badge">
                        <?php
                        $total_articles = 0;
                        $servername = "localhost";
                        $username = "root";
                        $password = "";
                        $dbname = "dev_livre";

                        if (isset($_SESSION['user_id'])) {
                            $conn = new mysqli($servername, $username, $password, $dbname);
                            if (!$conn->connect_error) {
                                $id_utilisateur = $_SESSION['user_id'];
                                $sql = "SELECT SUM(quantite) AS total FROM panier WHERE id_utilisateur = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $id_utilisateur);
                                $stmt->execute();
                                $res = $stmt->get_result()->fetch_assoc();
                                $total_articles = $res['total'] ?? 0;
                                $stmt->close();
                                $conn->close();
                            }
                        } else {
                            foreach ($_SESSION['panier'] as $item) {
                                $total_articles += $item['quantite'] ?? 0;
                            }
                        }
                        echo $total_articles;
                        ?>
                    </span>
                </a>
            </div>
        </div>
    </header>

    <main class="main">
        
        <!-- Téléchargements Content -->
        <section class="section py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="telechargements-container">
                            <?php if (count($telechargements) > 0): ?>
                                <?php foreach ($telechargements as $telechargement): ?>
                                    <div class="telechargement-item">
                                        <?php if (!empty($telechargement['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($telechargement['image']); ?>"
                                                alt="<?php echo htmlspecialchars($telechargement['titre']); ?>"
                                                class="telechargement-image">
                                        <?php else: ?>
                                            <div class="telechargement-image"
                                                style="background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-book" style="font-size: 2rem; color: #6c757d;"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div class="telechargement-content">
                                            <div class="telechargement-header">
                                                <div>
                                                    <h3 class="telechargement-titre">
                                                        <?php echo htmlspecialchars($telechargement['titre']); ?></h3>
                                                    <p class="telechargement-description">
                                                        <?php echo htmlspecialchars($telechargement['description']); ?></p>
                                                </div>
                                            </div>

                                            <div class="telechargement-details">
                                                <div class="detail-item">
                                                    <span class="detail-label">Acheté le</span>
                                                    <span
                                                        class="detail-value"><?php echo date('d/m/Y', strtotime($telechargement['date_commande'])); ?></span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Expire le</span>
                                                    <span
                                                        class="detail-value"><?php echo date('d/m/Y', strtotime($telechargement['date_expiration'])); ?></span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">Téléchargements</span>
                                                    <span
                                                        class="detail-value"><?php echo $telechargement['nombre_telechargements']; ?>
                                                        / illimité</span>
                                                </div>
                                            </div>

                                            <div class="telechargement-actions">
                                                <a href="telecharger.php?token=<?php echo $telechargement['token']; ?>"
                                                    class="btn-telecharger">
                                                    <i class="bi bi-download"></i>Télécharger
                                                </a>
                                            </div>

                                            <?php
                                            $jours_restants = floor((strtotime($telechargement['date_expiration']) - time()) / (60 * 60 * 24));
                                            if ($jours_restants < 7): ?>
                                                <p class="expiration-warning">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                    Expire dans <?php echo $jours_restants; ?>
                                                    jour<?php echo $jours_restants > 1 ? 's' : ''; ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-cloud-download"></i>
                                    <h3>Aucun téléchargement disponible</h3>
                                    <p>Vous n'avez pas encore acheté de livres numériques ou vos téléchargements ont expiré.
                                    </p>
                                    <a href="catalogue.php" class="btn-getstarted mt-3">Découvrir les livres numériques</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- footer -->
    <footer id="footer" class="footer dark-background">
        <div class="container">
            <div class="row gy-5">

                <!-- Marque -->
                <div class="col-lg-4">
                    <div class="footer-brand">
                        <a href="accueil.php" class="logo d-flex align-items-center mb-3">
                            <span class="sitename" style="color:#e96308;">InspiLivres</span>
                        </a>
                        <p class="tagline">Des livres de développement personnel pour transformer votre vie.</p>

                        <div class="social-links mt-4">
                            <a href="#" aria-label="Facebook" class="social-icon"><i class="bi bi-facebook"></i></a>
                            <a href="#" aria-label="Instagram" class="social-icon"><i class="bi bi-instagram"></i></a>
                            <a href="#" aria-label="LinkedIn" class="social-icon"><i class="bi bi-linkedin"></i></a>
                            <a href="#" aria-label="Twitter" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Liens utiles -->
                <div class="col-lg-6">
                    <div class="footer-links-grid">
                        <div class="row">
                            <div class="col-6 col-md-4">
                                <h5 style="color:#e96308;">Nos Livres</h5>
                                <ul class="list-unstyled">
                                    <li><a href="catalogue.php?filter=new">Nouveautés</a></li>
                                    <li><a href="catalogue.php?filter=bestsellers">Tous nos livres</a></li>
                                    <li><a href="catalogue.php?filter=promo">Offres Spéciales</a></li>
                                </ul>
                            </div>
                            <div class="col-6 col-md-4">
                                <h5 style="color:#e96308;">InspiLivres</h5>
                                <ul class="list-unstyled">
                                    <li><a href="accueil.php">Accueil</a></li>
                                    <li><a href="about.php">À propos</a></li>
                                    <li><a href="catalogue.php">Catalogue</a></li>
                                    <li><a href="contact.php">Contact</a></li>
                                </ul>
                            </div>
                            <div class="col-6 col-md-4">
                                <h5 style="color:#e96308;">Aide & Support</h5>
                                <ul class="list-unstyled">
                                    <li><a href="faq.php">FAQ</a></li>
                                    <li><a href="contact.php">Contact</a></li>
                                    <li><a href="conditions.php">Conditions générales</a></li>
                                    <li><a href="confidentialite.php">Politique de confidentialité</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="col-lg-2">
                    <div class="footer-cta">
                        <h5 style="color:#e96308;">Newsletter</h5>
                        <p class="small">Recevez nos nouveautés et offres exclusives</p>
                        <form class="newsletter-form" method="POST" id="newsletterForm">
                            <div class="input-group-vertical">
                                <input type="email" name="email" class="form-control mb-2" placeholder="Votre email"
                                    required>
                                <button class="btn btn-outline w-100" type="submit" name="subscribe"
                                    style="border-color: #e96308; color:#fff; background-color: #e96308;">
                                    S'abonner
                                </button>
                            </div>
                        </form>

                        <?php
                        // Traitement du formulaire newsletter
                        if (isset($_POST['subscribe'])) {
                            $host = "localhost";
                            $user = "root";
                            $password = "";
                            $dbname = "dev_livre";

                            try {
                                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                                $email = trim($_POST['email']);

                                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    // Vérifier si l'email existe déjà
                                    $check = $pdo->prepare("SELECT id FROM newsletter WHERE email = ?");
                                    $check->execute([$email]);

                                    if ($check->rowCount() > 0) {
                                        echo '<script>document.addEventListener("DOMContentLoaded", function() { showNewsletterMessage("⚠️ Cet email est déjà abonné.", "warning"); });</script>';
                                    } else {
                                        $insert = $pdo->prepare("INSERT INTO newsletter (email) VALUES (?)");
                                        $insert->execute([$email]);
                                        echo '<script>document.addEventListener("DOMContentLoaded", function() { showNewsletterMessage("✅ Merci ! Vous êtes abonné à la newsletter.", "success"); });</script>';
                                    }
                                } else {
                                    echo '<script>document.addEventListener("DOMContentLoaded", function() { showNewsletterMessage("❌ Adresse email invalide.", "error"); });</script>';
                                }
                            } catch (PDOException $e) {
                                echo '<script>document.addEventListener("DOMContentLoaded", function() { showNewsletterMessage("Erreur de connexion à la base de données.", "error"); });</script>';
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="footer-bottom-content d-flex justify-content-between align-items-center flex-wrap">
                            <p class="mb-0">© 2025 <span class="sitename" style="color:#e96308;">InspiLivres</span>.
                                Tous droits
                                réservés.</p>
                            <div class="credits">
                                Propulsé avec passion 💡 par <a href="accueil.php"
                                    style="color:#e96308;">InspiLivres</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- footer end -->

    <!-- Conteneur pour les messages flottants -->
    <div id="newsletter-message"
        style="display: none; position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 9999; opacity: 0; transition: opacity 0.5s ease; font-weight: 500; max-width: 300px;">
    </div>
    <style>
      /* Styles pour les messages flottants */
      .newsletter-success {
        background-color: rgba(0, 0, 0, 0.68);
        color: white;
      }

      .newsletter-warning {
        background-color: #ffc107;
        color: #333;
      }

      .newsletter-error {
        background-color: #dc3545;
        color: white;
      }
    </style>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

    <script>
        // Fonction pour afficher les messages flottants
        function showNewsletterMessage(message, type) {
            const messageElement = document.getElementById('newsletter-message');

            if (messageElement) {
                // Configurer le message
                messageElement.textContent = message;
                messageElement.className = ''; // Reset classes
                messageElement.classList.add('newsletter-' + type);

                // Afficher le message avec animation
                messageElement.style.display = 'block';
                setTimeout(() => {
                    messageElement.style.opacity = '1';
                }, 10);

                // Cacher le message après 4 secondes
                setTimeout(() => {
                    messageElement.style.opacity = '0';
                    setTimeout(() => {
                        messageElement.style.display = 'none';
                    }, 500);
                }, 4000);
            }
        }

        // Validation côté client
        document.addEventListener('DOMContentLoaded', function () {
            const newsletterForm = document.getElementById('newsletterForm');

            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function (e) {
                    const emailInput = this.querySelector('input[name="email"]');
                    const email = emailInput.value;

                    // Validation basique côté client
                    if (!isValidEmail(email)) {
                        showNewsletterMessage("❌ Veuillez entrer une adresse email valide.", "error");
                        e.preventDefault();
                    }
                });
            }

            // Fonction de validation d'email
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
        });
    </script>
</body>

</html>