<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// Si succès ou erreur via GET
$confirmation = '';
if (isset($_GET['success'])) {
    $confirmation = "✅ Merci ! Votre message a été envoyé avec succès.";
} elseif (isset($_GET['error'])) {
    $confirmation = "❌ Erreur lors de l'envoi : " . htmlspecialchars($_GET['error']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['subscribe'])) {
    $nom = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $sujet = htmlspecialchars($_POST['subject'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    if (!empty($nom) && !empty($email) && !empty($sujet) && !empty($message)) {
        $mail = new PHPMailer(true);

        // --- AJOUT POUR STOCKER LE MESSAGE DANS LA BASE ---
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=dev_livre;charset=utf8", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("INSERT INTO messages (nom, email, sujet, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $sujet, $message]);
    } catch (PDOException $e) {
        // Si erreur, on continue quand même l'envoi du mail
    }

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'florecinetamou@gmail.com';
            $mail->Password = 'exbl blmq nqlm kike';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('florecinetamou@gmail.com', 'InspiLivres');
            $mail->addAddress('tamouflorecine@gmail.com');
            $mail->addReplyTo($email, $nom);

            $mail->isHTML(true);
            $mail->Subject = "Formulaire contact : $sujet";
            $mail->Body = "<h3>Nouveau message depuis le site</h3>
                           <p><b>Nom :</b> $nom</p>
                           <p><b>Email :</b> $email</p>
                           <p><b>Sujet :</b> $sujet</p>
                           <p><b>Message :</b><br>" . nl2br($message) . "</p>";
            $mail->AltBody = "Nom: $nom\nEmail: $email\nSujet: $sujet\nMessage:\n$message";

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->send();

            // Redirection après succès
            header("Location: contact.php?success=1");
            exit;

        } catch (Exception $e) {
            header("Location: contact.php?error=" . urlencode($mail->ErrorInfo));
            exit;
        }
    } else {
        header("Location: contact.php?error=" . urlencode("Veuillez remplir tous les champs."));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Contact - InspiLivres</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

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
    .page-title {
      position: relative;
      background-image: url("assets/img/pexels-element5-1370295.jpg");
      background-size: cover;
      background-position: center;
    }

    .page-title::before {
      content: "";
      position: absolute;
      inset: 0;
      /* couvre toute la div */
      background-color: rgba(10, 10, 10, 0.868);
      /* noir avec 50% d’opacité */
      z-index: 1;
    }

    .page-title .container {
      position: relative;
      z-index: 2;
      /* texte au-dessus du calque */
    }

    .cart-count-badge {
      background-color: #e96308;
      color: white;
      border-radius: 50%;
      padding: 3px 8px;
      font-size: 0.7rem;
      margin-left: 5px;
    }

    .header .container-fluid {
      display: flex;
      align-items: center;
      justify-content: space-between;
      /* ← CLÉ : répartit l'espace entre les éléments */
    }

    .header-buttons {
      display: flex;
      align-items: center;
      margin-left: auto;
      /* ← CLÉ : pousse ce groupe vers l'extrême droite */
    }

    .btn-mon-espace {
      margin-right: 15px;
      /* ← Espacement accru entre "Mon espace" et le panier */
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
    .header-buttons {
      display: flex;
      align-items: center;
    }

    /* Conteneur du bouton et des réseaux sociaux */
.form-submit {
    display: flex;
    flex-direction: column; /* bouton au-dessus, icônes en dessous */
    align-items: flex-start; /* aligne à gauche */
    gap: 15px; /* espace entre bouton et icônes */
    margin-top: 20px;
}

/* Style du bouton Envoyer */
.form-submit button {
    padding: 12px 25px;
    background-color: #e96308; /* couleur principale */
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-submit button:hover {
    background-color: #cf5200; /* un peu plus foncé au survol */
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Icônes sociales */
.form-submit .social-links {
    display: flex;
    gap: 10px;
}

.form-submit .social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    background-color: #f0f0f0;
    border-radius: 50%;
    color: #333;
    font-size: 18px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.form-submit .social-links a:hover {
    background-color: #e96308;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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

<body class="contact-page">

  <?php
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }

  if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
  }
  ?>

<!-- Message de confirmation -->
          <?php if (!empty($confirmation)): ?>
            <div id="confirmation-message" style="
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(10, 10, 10, 0.94);
        color: #e96308;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.68);
        font-weight: bold;
        border:1px solid rgba(0, 0, 0, 0.68) ;
        z-index: 1000;
        opacity: 1;
        transition: opacity 0.5s ease;
    " class="<?php echo (strpos($confirmation, 'Merci') !== false) ? 'alert alert-success' : 'alert alert-danger'; ?>"
              role="alert">
              <?= $confirmation ?>
            </div>
            <script>
              setTimeout(function () {
                document.getElementById('confirmation-message').style.opacity = '0';
              }, 4000); // le message disparaît après 4 secondes
            </script>
          <?php endif; ?>


  <header id="header" class="header d-flex align-items-center fixed-top">
      <div class="container-fluid container-xl position-relative d-flex align-items-center">

        <a href="accueil.php" class="logo d-flex align-items-center me-auto">
          <h1 class="sitename" style="color: #e96308;">InspiLivres</h1>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="accueil.php" >Accueil</a></li>
            <li><a href="catalogue.php">Catalogue</a></li>
            <li><a href="about.php">À propos</a></li>
            <li><a href="contact.php" class="active">Contact</a></li>

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
    <div class="page-title dark-background" data-aos="fade">
      <div class="container position-relative">
        <h1>Contact</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="accueil.php">Accueil</a></li>
            <li class="current">Contact</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <div class="container-fluid" data-aos="fade-up" data-aos-delay="100">
        <div class="contact-main-wrapper d-flex  justify-content-between">

          <!-- CONTACT INFOS -->
          <div class="contact-content col-lg-5" data-aos="fade-up" data-aos-delay="300">
            <div class="contact-cards-container d-flex flex-column gap-3">

              <div class="contact-card mb-5">
                <div class="icon-box">
                  <i class="bi bi-geo-alt"></i>
                </div>
                <div class="contact-text">
                  <h4>Adresse</h4>
                  <p>Cotonou, Bénin</p>
                </div>
              </div>

              <div class="contact-card mb-5">
                <div class="icon-box">
                  <i class="bi bi-envelope"></i>
                </div>
                <div class="contact-text">
                  <h4>Email</h4>
                  <p>contact@mabiblio.com</p>
                </div>
              </div>

              <div class="contact-card mb-5">
                <div class="icon-box">
                  <i class="bi bi-telephone"></i>
                </div>
                <div class="contact-text">
                  <h4>Téléphone</h4>
                  <p>+229 90 12 34 56</p>
                </div>
              </div>

              <div class="contact-card mb-4">
                <div class="icon-box">
                  <i class="bi bi-clock"></i>
                </div>
                <div class="contact-text">
                  <h4>Heures d’ouverture</h4>
                  <p>Lundi - Vendredi: 9h - 18h</p>
                </div>
              </div>

            </div>
          </div>

          

          <!-- FORMULAIRE -->
          <div class="contact-form-container col-lg-7" data-aos="fade-up" data-aos-delay="400"
            style="max-height:550px;">
            <h3>Contactez-nous</h3>
            <p style="font-size:20px;">Une question ou une suggestion ? Envoyez-nous un message et nous vous répondrons
              rapidement.</p>



            <form action="contact.php" method="post">
              <div class="row">
                <div class="col-md-6 form-group">
                  <input type="text" name="name" class="form-control" id="name" placeholder="Votre nom"
                    value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
                </div>
                <div class="col-md-6 form-group mt-3 mt-md-0">
                  <input type="email" class="form-control" name="email" id="email" placeholder="Votre email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
              </div>
              <div class="form-group mt-3">
                <input type="text" class="form-control" name="subject" id="subject" placeholder="Objet"
                  value="<?php echo isset($_POST['sujet']) ? htmlspecialchars($_POST['sujet']) : ''; ?>" required>
              </div>
              <div class="form-group mt-3">
                <textarea class="form-control" name="message" rows="5" placeholder="Votre message" required>
                  <?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
              </div>



              <div class="form-submit">
                <button type="submit">Envoyer</button>
                <div class="social-links mt-3">
                  <a href="#"><i class="bi bi-facebook"></i></a>
                  <a href="#"><i class="bi bi-instagram"></i></a>
                  <a href="#"><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
            </form>
          </div>

        </div>
      </div>
    </section>
    <!-- contact section end  -->

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