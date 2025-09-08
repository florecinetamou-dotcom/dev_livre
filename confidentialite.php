<?php
session_start();
if (!isset($_SESSION['panier'])) {
  $_SESSION['panier'] = [];
}
$pageTitle = "Politique de Confidentialité - InspiLivres";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?></title>
  <meta name="description" content="Politique de confidentialité d'InspiLivres - Comment nous protégeons vos données personnelles">
  <meta name="keywords" content="confidentialité, données personnelles, politique, protection, vie privée">

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
    .cart-count-badge {
      background-color: #e96308;
      color: white;
      border-radius: 50%;
      padding: 3px 8px;
      font-size: 0.7rem;
      margin-left: 5px;
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

    .header-buttons {
      display: flex;
      align-items: center;
      margin-left: auto;
    }

    /* Style pour le bouton panier */
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

    /* Ajustement de l'en-tête */
    .header .container-fluid {
      display: flex;
      align-items: center;
      justify-content: space-between;
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
    
    /* Styles pour la page de confidentialité */
    .privacy-section {
      padding: 60px 0;
    }
    
    .privacy-header {
      margin-bottom: 40px;
      text-align: center;
    }
    
    .privacy-content {
      background-color: #f9f9f9;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .privacy-section-title {
      color: #e96308;
      border-bottom: 2px solid #e96308;
      padding-bottom: 10px;
      margin-top: 30px;
      margin-bottom: 20px;
    }
    
    .privacy-list {
      list-style-type: none;
      padding-left: 20px;
    }
    
    .privacy-list li {
      margin-bottom: 10px;
      position: relative;
    }
    
    .privacy-list li:before {
      content: "•";
      color: #e96308;
      font-weight: bold;
      display: inline-block;
      width: 1em;
      margin-left: -1em;
    }
    
    .highlight-box {
      background-color: #fff3e0;
      border-left: 4px solid #e96308;
      padding: 20px;
      margin: 20px 0;
      border-radius: 5px;
    }
    
    .contact-info {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 5px;
      margin-top: 30px;
    }
  </style>
</head>

<body class="privacy-page">
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
          <li><a href="confidentialite.php" class="active">Confidentialités</a></li>

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
  </header>

  <main class="main">
    <!-- Page Title -->
    <div class="page-title dark-background" data-aos="fade"
      style="background-image: url(assets/img/pexels-element5-1370295.jpg);">
      <div class="container position-relative">
        <h1>Politique de Confidentialité</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="accueil.php">Accueil</a></li>
            <li class="current">Politique de Confidentialité</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Privacy Section -->
    <section class="privacy-section" data-aos="fade-up">
      <div class="container">
        <div class="privacy-header">
          <h2>Notre engagement envers votre vie privée</h2>
          <p>Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
        </div>

        <div class="privacy-content">
          <div class="highlight-box">
            <strong>Important :</strong> Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos informations personnelles lorsque vous utilisez notre site InspiLivres.
          </div>

          <h3 class="privacy-section-title">1. Introduction</h3>
          <p>Chez InspiLivres, nous accordons une grande importance à la protection de vos données personnelles. Cette politique explique comment nous collectons, utilisons, partageons et protégeons vos informations lorsque vous utilisez notre site web et nos services.</p>
          <p>En utilisant notre site, vous acceptez les pratiques décrites dans cette politique de confidentialité. Nous vous encourageons à lire attentivement cette politique pour comprendre nos pratiques concernant vos données.</p>

          <h3 class="privacy-section-title">2. Données que nous collectons</h3>
          <p>Nous collectons plusieurs types d'informations pour fournir et améliorer nos services :</p>
          <ul class="privacy-list">
            <li><strong>Informations personnelles :</strong> Nom, prénom, adresse e-mail, numéro de téléphone, adresse de livraison et de facturation.</li>
            <li><strong>Informations de paiement :</strong> Données de carte bancaire (traitées de manière sécurisée par nos prestataires de paiement).</li>
            <li><strong>Données de commande :</strong> Historique des achats, préférences et produits consultés.</li>
            <li><strong>Données techniques :</strong> Adresse IP, type de navigateur, pages visitées, durée de visite et autres données de navigation.</li>
            <li><strong>Cookies :</strong> Nous utilisons des cookies pour améliorer votre expérience utilisateur.</li>
          </ul>

          <h3 class="privacy-section-title">3. Utilisation de vos données</h3>
          <p>Nous utilisons vos données personnelles aux fins suivantes :</p>
          <ul class="privacy-list">
            <li>Traiter et livrer vos commandes</li>
            <li>Vous fournir un service client personnalisé</li>
            <li>Vous informer sur les nouveaux produits, promotions et offres spéciales</li>
            <li>Améliorer notre site web et nos services</li>
            <li>Détecter et prévenir la fraude</li>
            <li>Respecter nos obligations légales</li>
          </ul>

          <h3 class="privacy-section-title">4. Partage de vos données</h3>
          <p>Nous ne vendons pas vos données personnelles. Nous pouvons partager vos informations avec :</p>
          <ul class="privacy-list">
            <li><strong>Prestataires de services :</strong> Sociétés de livraison, processeurs de paiement, services marketing.</li>
            <li><strong>Obligations légales :</strong> Lorsque requis par la loi ou pour répondre à une procédure légale.</li>
            <li><strong>Protection de nos droits :</strong> Pour protéger la sécurité de nos utilisateurs et les droits d'InspiLivres.</li>
          </ul>
          <p>Tous nos prestataires sont tenus de respecter la confidentialité de vos informations et de les utiliser uniquement pour les services qu'ils nous fournissent.</p>

          <h3 class="privacy-section-title">5. Conservation des données</h3>
          <p>Nous conservons vos données personnelles aussi longtemps que nécessaire pour fournir nos services et respecter nos obligations légales. Les périodes de conservation spécifiques sont :</p>
          <ul class="privacy-list">
            <li>Données de compte : 5 ans après la dernière activité</li>
            <li>Données de commande : 10 ans (obligation légale comptable)</li>
            <li>Données de prospect : 3 ans après le dernier contact</li>
          </ul>

          <h3 class="privacy-section-title">6. Vos droits</h3>
          <p>Conformément à la réglementation sur la protection des données (RGPD), vous disposez des droits suivants :</p>
          <ul class="privacy-list">
            <li><strong>Droit d'accès :</strong> Obtenir une copie de vos données personnelles.</li>
            <li><strong>Droit de rectification :</strong> Demander la correction de données inexactes.</li>
            <li><strong>Droit à l'effacement :</strong> Demander la suppression de vos données.</li>
            <li><strong>Droit d'opposition :</strong> Vous opposer au traitement de vos données.</li>
            <li><strong>Droit à la portabilité :</strong> Recevoir vos données dans un format structuré.</li>
            <li><strong>Droit de limitation :</strong> Demander la limitation du traitement de vos données.</li>
          </ul>
          <p>Pour exercer ces droits, contactez-nous à l'adresse privacy@inspilivres.com.</p>

          <h3 class="privacy-section-title">7. Cookies</h3>
          <p>Notre site utilise des cookies pour :</p>
          <ul class="privacy-list">
            <li>Mémoriser vos préférences et votre panier d'achat</li>
            <li>Analyser le trafic et les performances du site</li>
            <li>Personnaliser votre expérience utilisateur</li>
            <li>Proposer des publicités ciblées (avec votre consentement)</li>
          </ul>
          <p>Vous pouvez contrôler les cookies via les paramètres de votre navigateur. Notez que la désactivation de certains cookies peut affecter votre expérience sur notre site.</p>

          <h3 class="privacy-section-title">8. Sécurité des données</h3>
          <p>Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées pour protéger vos données contre tout accès non autorisé, modification, divulgation ou destruction. Parmi ces mesures :</p>
          <ul class="privacy-list">
            <li>Chiffrement des données sensibles</li>
            <li>Systèmes de sécurité pour prévenir les accès non autorisés</li>
            <li>Contrôle régulier de nos pratiques de collecte et de stockage</li>
            <li>Formation de notre personnel à la protection des données</li>
          </ul>

          <h3 class="privacy-section-title">9. Modifications de cette politique</h3>
          <p>Nous pouvons mettre à jour cette politique de confidentialité périodiquement. Les modifications seront publiées sur cette page avec une indication de la date de révision. Nous vous encourageons à consulter régulièrement cette politique pour rester informé de la manière dont nous protégeons vos informations.</p>

          <h3 class="privacy-section-title">10. Nous contacter</h3>
          <p>Pour toute question concernant cette politique de confidentialité ou pour exercer vos droits, contactez-nous :</p>
          <div class="contact-info">
            <p><strong>Email :</strong> privacy@inspilivres.com</p>
            <p><strong>Téléphone :</strong> +229 01 XX XX XX XX</p>
            <p><strong>Adresse postale :</strong> [Votre adresse complète]</p>
            <p><strong>Heures d'ouverture :</strong> Du lundi au vendredi, 9h-17h</p>
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

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>
</body>

</html>