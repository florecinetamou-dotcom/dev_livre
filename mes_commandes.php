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

$pageTitle = "Mes Commandes - InspiLivres";

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

// Récupérer les commandes de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM commande WHERE id_utilisateur = ? ORDER BY date_commande DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$commandes = $result->fetch_all(MYSQLI_ASSOC);

// Pour chaque commande, récupérer le nombre d'articles
foreach ($commandes as &$commande) {
  $sql_articles = "SELECT COUNT(*) as nb_articles FROM ligne_commande WHERE id_commande = ?";
  $stmt_articles = $conn->prepare($sql_articles);
  $stmt_articles->bind_param("i", $commande['id_commande']);
  $stmt_articles->execute();
  $result_articles = $stmt_articles->get_result();
  $data_articles = $result_articles->fetch_assoc();
  $commande['nb_articles'] = $data_articles['nb_articles'];
  $stmt_articles->close();
}

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

  <style>
    :root {
      --orange-primary: #e96308;
      --orange-hover: #ff7b20;
    }


    /* Header fixe avec fond */
    .header {
      background: rgba(0, 0, 0, 0.68) !important;
      backdrop-filter: blur(10px);
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
      height: 70px;
    }


    /* Style pour le bouton de déconnexion */
    .header .navmenu ul li a[href="logout.php"] {
      border-radius: 5px;
      padding: 3px 10px;
      transition: all 0.3s ease;
    }

    .header .navmenu ul li a[href="logout.php"]:hover {
      color: #fff !important;
      background-color: var(--orange-primary);
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
      background-color: #e96308;
      color: white !important;
      border-color: #e96308;
    }

    .btn-panier {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 3px 10px !important;
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

    .cart-count-badge {
      background-color: white;
      color: var(--orange-primary);
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 0.65rem;
      margin-left: 4px;
      font-weight: bold;
    }

    /* Commandes Styles */
    main section {
      position: relative;
      top:1.5rem
    }
    .commandes-container {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      padding: 30px;
      margin-top: 30px;
    }

    .commande-item {
      border: 1px solid #eee;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }

    .commande-item:hover {
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transform: translateY(-3px);
    }

    .commande-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid #f0f0f0;
    }

    .commande-id {
      font-weight: bold;
      color: var(--orange-primary);
      font-size: 1.2rem;
    }

    .commande-date {
      color: #666;
    }

    .btn-getstarted {
      background-color: var(--orange-primary);
      color: white !important;
      border: none;
      padding: 0.4rem 0.8rem;
      border-radius: 0.375rem;
      font-weight: 500;
    }

    .commande-statut {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }

    .statut-livree {
      background-color: #e1f7e3;
      color: #28a745;
    }

    .statut-expediee {
      background-color: #e3f2fd;
      color: #2196f3;
    }

    .statut-traitement {
      background-color: #fff3cd;
      color: #ffc107;
    }

    .commande-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 15px;
    }

    .detail-item {
      display: flex;
      flex-direction: column;
    }

    .detail-label {
      font-size: 0.85rem;
      color: #888;
      margin-bottom: 5px;
    }

    .detail-value {
      font-weight: 500;
    }

    .commande-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .btn-details {
      background-color: transparent;
      color: var(--orange-primary);
      border: 1px solid var(--orange-primary);
      padding: 5px 15px;
      border-radius: 4px;
      transition: all 0.3s;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .btn-details:hover {
      background-color: var(--orange-primary);
      color: white;
    }

    .empty-state {
      text-align: center;
      padding: 40px 0;
    }

    .empty-state i {
      font-size: 4rem;
      color: #ddd;
      margin-bottom: 20px;
    }

    .empty-state h3 {
      color: #888;
      margin-bottom: 15px;
    }

    /* Footer */
    .dark-background {
      background-color: #1a1a1a;
      color: white;
    }

    .footer {
      padding: 60px 0 30px;
    }

    .footer-links-grid a {
      color: #ccc;
      text-decoration: none;
      transition: color 0.3s;
    }

    .footer-links-grid a:hover {
      color: var(--orange-primary);
    }

    .footer-bottom {
      border-top: 1px solid #333;
      padding: 20px 0;
      margin-top: 30px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .commande-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      .commande-details {
        grid-template-columns: 1fr;
      }

      .commande-actions {
        justify-content: flex-start;
      }

      .header-buttons {
        margin-left: 15px;
      }

      .btn-mon-espace span {
        display: none;
      }

      .btn-mon-espace {
        padding: 0.5rem;
        margin-right: 10px;
      }
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
          <li><a href="mes_commandes.php" class="active">Commandes</a></li>

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

  <main>
    <!-- Commandes Content -->
    <section class="section py-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="commandes-container">
              <?php if (count($commandes) > 0): ?>
                <?php foreach ($commandes as $commande): ?>
                  <div class="commande-item">
                    <div class="commande-header">
                      <div>
                        <span class="commande-id">Commande #<?php echo $commande['id_commande']; ?></span>
                        <span class="commande-date">Passée le
                          <?php echo date('d/m/Y à H:i', strtotime($commande['date_commande'])); ?></span>
                      </div>
                      <span class="commande-statut 
                                                <?php
                                                if ($commande['statut'] == 'livrée')
                                                  echo 'statut-livree';
                                                elseif ($commande['statut'] == 'expédiée')
                                                  echo 'statut-expediee';
                                                else
                                                  echo 'statut-traitement';
                                                ?>">
                        <?php echo ucfirst($commande['statut']); ?>
                      </span>
                    </div>

                    <div class="commande-details">
                      <div class="detail-item">
                        <span class="detail-label">Total TTC</span>
                        <span class="detail-value"><?php echo number_format($commande['total'], 2, ',', ' '); ?> €</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Nombre d'articles</span>
                        <span class="detail-value"><?php echo $commande['nb_articles']; ?></span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Mode de livraison</span>
                        <span class="detail-value">Standard</span>
                      </div>
                    </div>

                    <div class="commande-actions">
                      <a href="detail_commande.php?id=<?php echo $commande['id_commande']; ?>" class="btn-details">
                        <i class="bi bi-eye me-1"></i>Voir les détails
                      </a>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="empty-state">
                  <i class="bi bi-cart-x"></i>
                  <h3>Vous n'avez pas encore passé de commande</h3>
                  <p>Découvrez notre catalogue et trouvez le livre qui vous inspirera.</p>
                  <a href="catalogue.php" class="btn-getstarted mt-3">Découvrir les livres</a>
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
      background-color: rgba(0, 0, 0, 0.68) color: white;
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


  <!-- Bootstrap JS -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>