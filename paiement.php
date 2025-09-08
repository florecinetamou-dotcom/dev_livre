<?php
session_start();

// Vérifier si l'utilisateur a des articles dans son panier
if (!isset($_SESSION['panier']) || count($_SESSION['panier']) === 0) {
  header("Location: panier.php");
  exit();
}

// Traitement du formulaire de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Récupérer les données du formulaire
  $nom = htmlspecialchars($_POST['nom']);
  $prenom = htmlspecialchars($_POST['prenom']);
  $email = htmlspecialchars($_POST['email']);
  $telephone = htmlspecialchars($_POST['telephone']);
  $adresse = htmlspecialchars($_POST['adresse']);
  $ville = htmlspecialchars($_POST['ville']);
  $methode_paiement = htmlspecialchars($_POST['methode_paiement']);

  // Ici vous devriez normalement enregistrer la commande en base de données
  // et traiter le paiement selon la méthode choisie

  // Simulation d'un numéro de commande
  $numero_commande = 'CMD-' . date('Ymd') . '-' . rand(1000, 9999);

  // Redirection vers la page de confirmation
  $_SESSION['numero_commande'] = $numero_commande;
  header("Location: confirmation.php");
  exit();
}

// Calculer le total du panier
$total = 0;
foreach ($_SESSION['panier'] as $article) {
  $total += $article['prix'] * $article['quantite'];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Paiement - InspiLivres</title>
  <meta name="description" content="Finalisez votre commande de livres de développement personnel">
  <meta name="keywords" content="commande, paiement, livraison, momo, moov money, celtiis cash">

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

  <style>
    .btn-panier {
      display:flex;
      justify-content: center;
      align-items: center;
      padding:3px 10px !important;
      color: #e96308 !important;
      border: 1px solid #e96308;
      border-radius: 0.375rem;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.15s ease-in-out;
    }

    .btn-panier:hover {
      background-color: #e96308;
      color: white !important;
      border-color: #e96308;
    }
    /* Styles pour le bouton Mon espace */
    .btn-mon-espace {
      display: inline-flex;
      align-items: center;
      padding: 3px 10px !important;
      background-color: transparent;
      color: #e96308 !important;
      border: 1px solid #e96308;
      border-radius: 0.375rem;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.15s ease-in-out;
      margin-right: 15px;
    }

    .btn-mon-espace:hover {
       background-color:transparent;
      color: white !important;
      border-color: white;
    }

    .checkout-page {
      background-color: #f8f9fa;
      padding-bottom: 50px;
    }

    .checkout-card {
      background: white;
      border-radius: 10px;
      padding: 25px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .payment-method {
      border: 2px solid #e9ecef;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .payment-method:hover,
    .payment-method.selected {
      border-color: #e96308;
      background-color: rgba(233, 99, 8, 0.05);
    }

    .payment-method img {
      height: 40px;
      object-fit: contain;
    }

    .order-summary {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #e9ecef;
    }

    .btn-payment {
      background-color: #e96308;
      border-color: #e96308;
      color: white;
      padding: 12px;
      font-size: 1.1rem;
      font-weight: 600;
    }

    .btn-payment:hover {
      background-color: #d45a07;
      border-color: #d45a07;
      color: white;
    }

    .form-control:focus {
      border-color: #e96308;
      box-shadow: 0 0 0 0.25rem rgba(233, 99, 8, 0.25);
    }
    /* Pour les écrans mobiles */
    @media (max-width: 1200px) {
      .btn-mon-espace span {
        display: none;
      }
      .btn-mon-espace {
        width:10rem;
        margin-bottom:1rem;
        padding:3px 10px !important;
        margin-right: 10px;
      }
      .btn-panier{
        width:10rem;
        padding:3px 10px !important;;
      }
      .header-buttons {
        margin-left: 15px;
      }
    }

  </style>
</head>

<body class="checkout-page">

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
          <li><a href="paiement.php" class="active"></a>Paiement</li>

          <li><a href="login.php">Connexion</a></li>
          <li><a href="register.php">Inscription</a></li>

          <?php if (isset($_SESSION['user_id'])): ?>
            <li>
              <a href="mon_espace.php" class="btn-mon-espace">
                <i class="bi bi-person-circle me-1"></i>Mon espace
              </a>
            </li>
          <?php endif; ?>

          <li>
            <a class="btn-panier" href="panier.php">
              <i class="bi bi-cart"></i> Panier
              <span class="cart-count-badge">
                <?php
                // Connexion pour compter les articles
                $conn = new mysqli("localhost", "root", "", "dev_livre");
                if ($conn->connect_error) {
                  echo "0";
                } else {
                  if (isset($_SESSION['user_id'])) {
                    $id_utilisateur = $_SESSION['user_id'];
                    $sql = "SELECT SUM(quantite) AS total FROM panier WHERE id_utilisateur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id_utilisateur);
                    $stmt->execute();
                    $res = $stmt->get_result()->fetch_assoc();
                    echo $res['total'] ?? 0;
                    $stmt->close();
                  } else {
                    $total = 0;
                    if (isset($_SESSION['panier'])) {
                      foreach ($_SESSION['panier'] as $item) {
                        $total += $item['quantite'];
                      }
                    }
                    echo $total;
                  }
                  $conn->close();
                }
                ?>
              </span>
            </a>
          </li>

        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list" style="color: #e96308;"></i>
      </nav>


    </div>

    </div>
  </header>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title dark-background" data-aos="fade"
      style="background-image: url(assets/img/pexels-element5-1370295.jpg);">
      <div class="container position-relative">
        <h1>Finaliser votre commande</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="panier.php">Panier</a></li>
            <li class="current">Paiement</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Checkout Section -->
    <section id="checkout" class="checkout section">
      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <form method="POST" action="commandes.php">
          <div class="row">
            <!-- Informations client -->
            <div class="col-lg-7">
              <div class="checkout-card">
                <h3 class="mb-4">Informations personnelles</h3>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group mb-3">
                      <label for="nom" class="form-label">Nom *</label>
                      <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group mb-3">
                      <label for="prenom" class="form-label">Prénom *</label>
                      <input type="text" class="form-control" id="prenom" name="prenom" required>
                    </div>
                  </div>
                </div>

                <div class="form-group mb-3">
                  <label for="email" class="form-label">Email *</label>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="form-group mb-3">
                  <label for="telephone" class="form-label">Téléphone *</label>
                  <input type="tel" class="form-control" id="telephone" name="telephone" required>
                </div>

                <div class="form-group mb-3">
                  <label for="adresse" class="form-label">Adresse de livraison *</label>
                  <textarea class="form-control" id="adresse" name="adresse" rows="3" required></textarea>
                </div>

                <div class="form-group mb-4">
                  <label for="ville" class="form-label">Ville *</label>
                  <input type="text" class="form-control" id="ville" name="ville" required>
                </div>

                <h3 class="mb-4">Méthode de paiement</h3>
                <p>Sélectionnez votre méthode de paiement mobile préférée</p>

                <div class="payment-options">
                  <div class="payment-method" onclick="selectPayment('momo')">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="methode_paiement" id="momo" value="momo"
                        required>
                      <label class="form-check-label d-flex align-items-center" for="momo">
                        <img
                          src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiKwMjYxQzEiLz4KPHRleHQgeD0iMTAwIiB5PSIxMTAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk1vTW88L3RleHQ+Cjwvc3ZnPg=="
                          alt="MoMo" class="me-3">
                        <div>
                          <h5 class="mb-1">MTN MoMo</h5>
                          <p class="mb-0 text-muted">Paiement via Mobile Money</p>
                        </div>
                      </label>
                    </div>
                  </div>

                  <div class="payment-method" onclick="selectPayment('moov')">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="methode_paiement" id="moov" value="moov">
                      <label class="form-check-label d-flex align-items-center" for="moov">
                        <img
                          src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMbm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjMDBBQTk5Ii8+Cjx0ZXh0IHg9IjEwMCIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5Nb292IE1vbmV5PC90ZXh0Pgo8L3N2Zz4="
                          alt="Moov Money" class="me-3">
                        <div>
                          <h5 class="mb-1">Moov Money</h5>
                          <p class="mb-0 text-muted">Paiement via Moov Money</p>
                        </div>
                      </label>
                    </div>
                  </div>

                  <div class="payment-method" onclick="selectPayment('celtiis')">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="methode_paiement" id="celtiis" value="celtiis">
                      <label class="form-check-label d-flex align-items-center" for="celtiis">
                        <img
                          src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNGRjAwMDAiLz4KPHRleHQgeD0iMTAwIiB5PSIxMTAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyMCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPkNlbHRpaXMgQ2FzaDwvdGV4dD4KPC9zdmc+"
                          alt="Celtiis Cash" class="me-3">
                        <div>
                          <h5 class="mb-1">Celtiis Cash</h5>
                          <p class="mb-0 text-muted">Paiement via Celtiis Cash</p>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Récapitulatif de commande -->
            <div class="col-lg-5">
              <div class="checkout-card">
                <h3 class="mb-4">Votre commande</h3>

                <div class="order-summary">
                  <?php foreach ($_SESSION['panier'] as $id => $article): ?>
                    <div class="order-item">
                      <div>
                        <h6><?php echo htmlspecialchars($article['titre']); ?></h6>
                        <small class="text-muted">Quantité: <?php echo $article['quantite']; ?></small>
                      </div>
                      <div class="text-end">
                        <h6><?php echo number_format($article['prix'] * $article['quantite'], 0, ',', ' '); ?> FCFA</h6>
                      </div>
                    </div>
                  <?php endforeach; ?>

                  <div class="order-item">
                    <div>
                      <h6>Sous-total</h6>
                    </div>
                    <div class="text-end">
                      <h6><?php echo number_format($total, 0, ',', ' '); ?> FCFA</h6>
                    </div>
                  </div>

                  <div class="order-item">
                    <div>
                      <h6>Livraison</h6>
                    </div>
                    <div class="text-end">
                      <h6>Gratuit</h6>
                    </div>
                  </div>

                  <div class="order-item pt-3" style="border-top: 2px solid #000;">
                    <div>
                      <h5>Total</h5>
                    </div>
                    <div class="text-end">
                      <h5 class="text-primary"><?php echo number_format($total, 0, ',', ' '); ?> FCFA</h5>
                    </div>
                  </div>
                </div>

                <div class="form-check mt-4">
                  <input class="form-check-input" type="checkbox" id="conditions" required>
                  <label class="form-check-label" for="conditions">
                    J'accepte les <a href="conditions.php">conditions générales de vente</a>
                  </label>
                </div>

                <button type="submit" class="btn btn-payment w-100 mt-4">
                  <i class="bi bi-lock-fill"></i> Payer maintenant
                </button>
              </div>
            </div>
          </div>
        </form>

      </div>
    </section><!-- /Checkout Section -->

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
                <input type="email" name="email" class="form-control mb-2" placeholder="Votre email" required>
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
              <p class="mb-0">© 2025 <span class="sitename" style="color:#e96308;">InspiLivres</span>. Tous droits
                réservés.</p>
              <div class="credits">
                Propulsé avec passion 💡 par <a href="accueil.php" style="color:#e96308;">InspiLivres</a>
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
      background-color: blue;
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

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    function selectPayment(method) {
      // Désélectionner toutes les méthodes
      document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
      });

      // Sélectionner la méthode cliquée
      document.querySelector(`.payment-method input[value="${method}"]`).closest('.payment-method').classList.add('selected');

      // Cocher le radio button correspondant
      document.getElementById(method).checked = true;
    }

    // Initialisation des animations
    document.addEventListener('DOMContentLoaded', function () {
      AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
      });
    });
  </script>

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