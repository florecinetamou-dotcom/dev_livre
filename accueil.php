<?php
// accueil.php

// Démarrage de session avec options
session_set_cookie_params([
  'lifetime' => 86400,
  'path' => '/',
  'domain' => '',
  'secure' => false,
  'httponly' => true,
  'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
  $_SESSION['panier'] = [];
}

// Connexion à la base de données pour récupérer les derniers livres
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dev_livre";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  // On continue quand même sans les livres si erreur de connexion
  $derniers_livres = [];
  $error_message = "Erreur de connexion à la base de données";
} else {
  // Récupérer les 6 derniers livres
  $sql_derniers_livres = "SELECT * FROM produits ORDER BY date_ajout DESC LIMIT 6";
  $result_derniers_livres = $conn->query($sql_derniers_livres);

  if ($result_derniers_livres) {
    $derniers_livres = $result_derniers_livres->fetch_all(MYSQLI_ASSOC);
  } else {
    $derniers_livres = [];
    $error_message = "Erreur lors de la récupération des livres";
  }
  $conn->close();
}

// Compter les articles du panier pour l'affichage
$nombre_articles = count($_SESSION['panier']);

// Debug (à commenter ou retirer en production)
// echo "<!-- Session: " . print_r($_SESSION, true) . " -->";
// echo "<!-- Nombre d'articles: $nombre_articles -->";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Index - InspiLivres</title>
  <meta name="description" content="Librairie en ligne de livres de développement personnel">
  <meta name="keywords" content="livres, développement personnel, inspiration, lecture, croissance personnelle">


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


</head>

<body class="index-page">


  <style>
    /* Styles personnalisés pour le header */


    .header .navbar-brand {
      color: #e96308 !important;
      font-weight: bold;
      font-size: 1.5rem;
    }

    .header .nav-link {
      color: white !important;
      font-weight: 500;
    }

    .header .nav-link:hover,
    .header .nav-link.active {
      color: #e96308 !important;
    }



    .cart-count-badge {
      background-color: #e96308;
      color: white;
      border-radius: 50%;
      padding: 3px 8px;
      font-size: 0.7rem;
      margin: 0 5px;
    }

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
      background-color: transparent;
      color: white !important;
      border-color: white;
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

    /* Pour les écrans mobiles */
    @media (max-width: 1200px) {
      .btn-mon-espace span {
        display: none;
      }

      .btn-mon-espace {
        width: 10rem;
        margin-bottom: 1rem;
        padding: 3px 10px !important;
        margin-right: 10px;
      }

      .btn-panier {
        width: 10rem;
        /* padding:3px 10px !important;; */
      }

      .btn-panier span {
        display: none;
      }

      .btn-primary {
        margin-bottom: 1rem;
      }

      .navbar-collapse {
        background: rgba(0, 0, 0, 0.9);
        padding: 1rem;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
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

      <!-- Hero Section -->
      <section id="hero" class="hero section dark-background">

        <div class="hero-video-container">
          <video autoplay muted loop>
            <source src="assets/video/4861112-uhd_4096_2160_25fps (1).mp4" type="video/mp4">
            Your browser does not support the video tag.
          </video>
          <div class="hero-overlay"></div>
        </div>

        <div class="container" data-aos="fade-up" data-aos-delay="100">

          <div class="row justify-content-center text-center">
            <div class="col-lg-8">
              <div class="hero-content">
                <h1 data-aos="fade-up" data-aos-delay="200">Éveillez votre esprit avec nos livres</h1>
                <p data-aos="fade-up" data-aos-delay="300">Explorez notre sélection de livres de développement personnel
                  et transformez votre quotidien grâce à la lecture.</p>

                <div class="hero-actions" data-aos="fade-up" data-aos-delay="400">
                  <a href="catalogue.php" class="btn btn-primary"><i class="bi bi-book"></i> Découvrir le catalogue</a>
                  <a href="panier.php" class="btn btn-secondary"><i class="bi bi-cart"></i> Voir le panier</a>
                </div>

                <div class="hero-stats" data-aos="fade-up" data-aos-delay="500">
                  <div class="row">
                    <div class="col-lg-3 col-md-6">
                      <div class="stat-item">
                        <span class="stat-number" data-purecounter-start="0" data-purecounter-end="120"
                          data-purecounter-duration="2">120</span>
                        <span class="stat-label">Livres Disponibles</span>
                      </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                      <div class="stat-item">
                        <span class="stat-number" data-purecounter-start="0" data-purecounter-end="10"
                          data-purecounter-duration="2">10</span>
                        <span class="stat-label">Années d'expérience</span>
                      </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                      <div class="stat-item">
                        <span class="stat-number" data-purecounter-start="0" data-purecounter-end="95"
                          data-purecounter-duration="2">95</span>
                        <span class="stat-label">% Clients satisfaits</span>
                      </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                      <div class="stat-item">
                        <span class="stat-number" data-purecounter-start="0" data-purecounter-end="20"
                          data-purecounter-duration="2">20</span>
                        <span class="stat-label">Auteurs partenaires</span>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

        </div>

      </section><!-- /Hero Section -->


      <!-- About Section -->
      <section id="about" class="about section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

          <div class="row align-items-center">

            <!-- Texte À propos -->
            <div class="col-lg-6 order-2 order-lg-1" data-aos="fade-right" data-aos-delay="200">
              <div class="content">
                <h2 class="section-heading mb-4"><strong>À propos</strong></h2>
                <p class="lead-text mb-4">InspiLivres sélectionne pour vous les meilleurs ouvrages de développement
                  personnel afin de vous aider à grandir, apprendre et atteindre vos objectifs.</p>
                <p class="description-text mb-5">Nous croyons que chaque lecture est une opportunité de transformation.
                  Que vous soyez débutant ou lecteur assidu, notre catalogue propose des livres qui éveillent l'esprit,
                  renforcent la motivation et nourrissent la créativité.</p>

                <!-- Statistiques -->
                <div class="stats-grid">
                  <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-number">120+</div>
                    <div class="stat-label">Livres disponibles</div>
                  </div>
                  <div class="stat-item" data-aos="fade-up" data-aos-delay="350">
                    <div class="stat-number">15+</div>
                    <div class="stat-label">Années d'expérience</div>
                  </div>
                  <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Clients satisfaits</div>
                  </div>
                </div>

              </div>
            </div>

            <!-- Image -->
            <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-left" data-aos-delay="200">
              <div class="image-section">
                <div class="main-image">
                  <img src="assets/img/book-1835799_1280.jpg" alt="Sélection de livres" class="img-fluid">
                </div>
              </div>
            </div>

          </div>

        </div>

      </section><!-- /About Section -->


      <!-- Livres Récemment Publiés -->
      <section id="recent-books" class="projects section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2>Livres récemment publiés</h2>
          <p>Découvrez les nouveautés littéraires sélectionnées pour vous.</p>
        </div>
        <!-- End Section Title -->

        <div class="container-fluid" data-aos="fade-up" data-aos-delay="100">

          <div class="row Livres gy-4">
            <?php if (count($derniers_livres) > 0): ?>
              <?php foreach ($derniers_livres as $index => $livre): ?>
                <!-- Livre Dynamique -->
                <div class="col-lg-2 col-md-4" data-aos="fade-up" data-aos-delay="<?php echo ($index % 6) * 100; ?>">
                  <div class="project-card">
                    <div class="project-image">
                      <?php if (!empty($livre['image']) && file_exists($livre['image'])): ?>
                        <img src="<?php echo htmlspecialchars($livre['image']); ?>"
                          alt="<?php echo htmlspecialchars($livre['titre']); ?>" class="img-fluid">
                      <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center h-100">
                          <i class="bi bi-book fs-1 text-muted"></i>
                        </div>
                      <?php endif; ?>
                      <div class="project-overlay">
                        <div class="project-actions">
                          <a href="catalogue.php" class="btn-project">Voir détails</a>
                        </div>
                      </div>
                    </div>
                    <div class="project-info">
                      <h4 class="project-title" style="font-size:1rem; margin-top:-20px;"><?php echo htmlspecialchars($livre['titre']); ?></h4>
                      <p class="project-description">par <?php echo htmlspecialchars($livre['auteur']); ?></p>
                      <!-- <div class="project-meta">
                        <span class="price"><i class="bi bi-tag"></i>
                          <?php echo number_format($livre['prix'], 0, ',', ' '); ?> FCFA
                        </span>
                      </div> -->
                    </div>
                  </div>
                </div>
                <!-- End Livre Item -->
              <?php endforeach; ?>
            <?php else: ?>
              <!-- Message si aucun livre -->
              <div class="col-12 text-center py-5">
                <i class="bi bi-book fs-1 text-muted mb-3"></i>
                <h4>Aucun livre disponible pour le moment</h4>
                <p>Revenez bientôt pour découvrir nos nouveautés</p>
                <a href="catalogue.php" class="btn btn-primary mt-3">Découvrir le catalogue</a>
              </div>
            <?php endif; ?>
          </div>

        </div>

      </section>
      <!-- /Livres Récemment Publiés -->

      <!-- Testimonials Section -->
      <section id="testimonials" class="testimonials section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2>Témoignages</h2>
          <p>Ils partagent leur expérience avec <strong>InspiLivres</strong></p>
        </div><!-- End Section Title -->

        <div class="container">

          <div class="testimonial-masonry">

            <!-- Témoignage 1 -->
            <div class="testimonial-item" data-aos="fade-up">
              <div class="testimonial-content">
                <div class="quote-pattern"><i class="bi bi-quote"></i></div>
                <p>Depuis que je lis les livres proposés par InspiLivres, j’ai découvert une nouvelle motivation dans ma
                  vie personnelle et professionnelle.</p>
                <div class="client-details">
                  <h3>Rachel Dupont</h3>
                </div>
              </div>
            </div>

            <!-- Témoignage 2 -->
            <div class="testimonial-item highlight" data-aos="fade-up" data-aos-delay="100">
              <div class="testimonial-content">
                <div class="quote-pattern"><i class="bi bi-quote"></i></div>
                <p>Chaque ouvrage est une vraie source d’inspiration. J’ai retrouvé le goût de la lecture et cela m’aide
                  à
                  progresser chaque jour.</p>
                <div class="client-details">
                  <h3>Daniel Martin</h3>
                </div>
              </div>
            </div>

            <!-- Témoignage 3 -->
            <div class="testimonial-item" data-aos="fade-up" data-aos-delay="200">
              <div class="testimonial-content">
                <div class="quote-pattern"><i class="bi bi-quote"></i></div>
                <p>Grâce à leurs sélections, j’ai appris à mieux gérer mon temps et à rester motivée face aux défis
                  quotidiens.</p>
                <div class="client-details">
                  <h3>Emma Leroy</h3>
                </div>
              </div>
            </div>

            <!-- Témoignage 4 -->
            <div class="testimonial-item" data-aos="fade-up" data-aos-delay="300">
              <div class="testimonial-content">
                <div class="quote-pattern"><i class="bi bi-quote"></i></div>
                <p>Les livres recommandés sont d’une grande qualité. J’ai trouvé des réponses concrètes à mes
                  questionnements.</p>
                <div class="client-details">
                  <h3>Christophe Morel</h3>
                </div>
              </div>
            </div>

            <!-- Témoignage 5 -->
            <div class="testimonial-item highlight" data-aos="fade-up" data-aos-delay="400">
              <div class="testimonial-content">
                <div class="quote-pattern"><i class="bi bi-quote"></i></div>
                <p>Lire un peu chaque jour m’a permis de retrouver confiance en moi et de changer ma façon de voir les
                  choses.</p>
                <div class="client-details">
                  <h3>Olivia Carpentier</h3>
                </div>
              </div>
            </div>

            <!-- Témoignage 6 -->
            <div class="testimonial-item" data-aos="fade-up" data-aos-delay="500">
              <div class="testimonial-content">
                <div class="quote-pattern"><i class="bi bi-quote"></i></div>
                <p>Une expérience unique : les livres proposés allient profondeur et simplicité, parfaits pour enrichir
                  son quotidien.</p>
                <div class="client-details">
                  <h3>Nathan Bernard</h3>
                </div>
              </div>
            </div>

          </div>

        </div>

      </section><!-- /Testimonials Section -->



      <!-- Call To Action Section -->
      <section id="call-to-action" class="call-to-action section light-background">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

          <!-- En-tête CTA -->
          <div class="row justify-content-center">
            <div class="col-lg-10">
              <div class="cta-header text-center" data-aos="fade-up" data-aos-delay="200">
                <h2>Prêt à transformer votre vie par la lecture ?</h2>
                <p>Découvrez une sélection soignée de livres de développement personnel pour nourrir votre esprit,
                  booster
                  votre motivation et passer à l'action, un chapitre à la fois.</p>
              </div>
            </div>
          </div>

          <!-- Contenu principal CTA -->
          <div class="cta-main" data-aos="fade-up" data-aos-delay="300">
            <div class="row align-items-center g-5">

              <!-- Statistiques / Preuves sociales -->
              <div class="col-lg-6">
                <div class="achievements-grid">

                  <div class="achievement-item" data-aos="zoom-in" data-aos-delay="400">
                    <div class="achievement-icon">
                      <i class="bi bi-book"></i>
                    </div>
                    <div class="achievement-info">
                      <h3>200+</h3>
                      <span>Livres inspirants</span>
                    </div>
                  </div>

                  <div class="achievement-item" data-aos="zoom-in" data-aos-delay="450">
                    <div class="achievement-icon">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="achievement-info">
                      <h3>20+</h3>
                      <span>Auteurs partenaires</span>
                    </div>
                  </div>

                  <div class="achievement-item" data-aos="zoom-in" data-aos-delay="500">
                    <div class="achievement-icon">
                      <i class="bi bi-star-fill"></i>
                    </div>
                    <div class="achievement-info">
                      <h3>95%</h3>
                      <span>Lecteurs satisfaits</span>
                    </div>
                  </div>

                  <div class="achievement-item" data-aos="zoom-in" data-aos-delay="550">
                    <div class="achievement-icon">
                      <i class="bi bi-journal-text"></i>
                    </div>
                    <div class="achievement-info">
                      <h3>50+</h3>
                      <span>Extraits gratuits</span>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Panneau d'action -->
              <div class="col-lg-6">
                <div class="action-panel" data-aos="fade-left" data-aos-delay="350">

                  <div class="panel-content">
                    <h3>Commencez aujourd’hui</h3>
                    <p>Parcourez le catalogue, ajoutez vos coups de cœur au panier et lisez un extrait pour trouver le
                      livre qui vous fera passer à l’étape suivante.</p>

                    <div class="action-buttons">
                      <a href="catalogue.html" class="btn-primary">
                        <span>Découvrir le catalogue</span>
                        <i class="bi bi-arrow-right"></i>
                      </a>
                      <a href="extraits.html" class="btn-secondary">
                        <span>Lire un extrait gratuit</span>
                        <i class="bi bi-bookmark"></i>
                      </a>
                    </div>
                  </div>

                  <!-- Contact rapide -->
                  <div class="contact-quick">
                    <div class="contact-row">
                      <i class="bi bi-whatsapp"></i>
                      <div class="contact-details">
                        <span class="contact-label">WhatsApp</span>
                        <span class="contact-value">+229 00 00 00 00</span>
                      </div>
                    </div>

                    <div class="contact-row">
                      <i class="bi bi-envelope-fill"></i>
                      <div class="contact-details">
                        <span class="contact-label">Email</span>
                        <span class="contact-value">contact@inspirivres.com</span>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

            </div>
          </div>

        </div>

      </section>
      <!-- /Call To Action Section -->

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