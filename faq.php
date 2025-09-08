<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAQ - InspiLivres</title>
  <meta name="description" content="Foire aux questions sur InspiLivres, nos livres et services">
  <meta name="keywords" content="FAQ, livres, aide, support, questions">

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
      margin-left: auto; /* Pousse les boutons vers la droite */
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
    .faq-section {
      padding: 60px 0;
    }
    .faq-header {
      margin-bottom: 40px;
      text-align: center;
    }
    .accordion-button {
      background-color: #f8f9fa;
      color: #e96308;
      font-weight: 500;
    }
    .accordion-button:not(.collapsed) {
      background-color: #e96308;
      color: white;
    }
    .accordion-body {
      background-color: #fff8f0;
    }
  </style>
</head>

<body class="contact-page">
<?php
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }

  if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
  }
  ?>
<header id="header" class="header d-flex align-items-center fixed-top">
      <div class="container-fluid container-xl position-relative d-flex align-items-center">

        <a href="accueil.php" class="logo d-flex align-items-center me-auto">
          <h1 class="sitename" style="color: #e96308;">InspiLivres</h1>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="accueil.php" class="active">Accueil</a></li>
            <li><a href="catalogue.php">Catalogue</a></li>
            <li><a href="about.php">À propos</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="faq.php" class="active">FAQ</a></li>

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
        <h1>FAQ</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="accueil.php">Accueil</a></li>
            <li class="current">FAQ</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- FAQ Section -->
    <section class="faq-section" data-aos="fade-up">
      <div class="container">
        <div class="faq-header">
          <h2>Foire aux Questions</h2>
          <p>Vous avez des questions ? Nous avons les réponses !</p>
        </div>

        <div class="accordion" id="faqAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                aria-expanded="true" aria-controls="collapseOne">
                Comment puis-je commander un livre sur InspiLivres ?
              </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
              data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                Pour commander un livre, naviguez dans notre catalogue, choisissez le livre désiré et cliquez sur "Ajouter au panier". Ensuite, procédez au paiement depuis votre panier.
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                Quels moyens de paiement acceptez-vous ?
              </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
              data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                Nous acceptons les paiements par carte bancaire, mobile money et PayPal pour une transaction sécurisée.
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                Puis-je suivre ma commande ?
              </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
              data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                Oui, après avoir passé votre commande, connectez-vous à votre espace utilisateur pour suivre l'état de livraison de vos livres.
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                Comment puis-je m'abonner à la newsletter ?
              </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
              data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                Sur n'importe quelle page, utilisez le formulaire de newsletter en bas du site pour vous inscrire et recevoir nos nouveautés et offres exclusives.
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <h2 class="accordion-header" id="headingFive">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                Que faire si j'ai un problème avec ma commande ?
              </button>
            </h2>
            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
              data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                Vous pouvez contacter notre support via la page Contact, nous répondrons rapidement à toutes vos demandes.
              </div>
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
