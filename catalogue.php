<?php
session_start();
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dev_livre";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Récupérer les filtres
$prix_min = $_GET['prix_min'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';
$search = $_GET['search'] ?? '';
$tri = $_GET['tri'] ?? 'nouveautes';

// Construire la requête avec filtres
$sql = "SELECT * FROM produits WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
  $sql .= " AND (titre LIKE ? OR auteur LIKE ? OR description LIKE ?)";
  $search_term = "%$search%";
  $params = array_merge($params, [$search_term, $search_term, $search_term]);
  $types .= "sss";
}

if (!empty($prix_min)) {
  $sql .= " AND prix >= ?";
  $params[] = $prix_min;
  $types .= "d";
}

if (!empty($prix_max)) {
  $sql .= " AND prix <= ?";
  $params[] = $prix_max;
  $types .= "d";
}

// Ajouter le tri
switch ($tri) {
  case 'prix_croissant':
    $sql .= " ORDER BY prix ASC";
    break;
  case 'prix_decroissant':
    $sql .= " ORDER BY prix DESC";
    break;
  case 'titre':
    $sql .= " ORDER BY titre ASC";
    break;
  case 'auteur':
    $sql .= " ORDER BY auteur ASC";
    break;
  default:
    $sql .= " ORDER BY date_ajout DESC";
    break;
}

// Préparer et exécuter la requête
$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$produits = [];
while ($row = $result->fetch_assoc()) {
  $produits[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Catalogue - InspiLivres</title>
  <meta name="description" content="Découvrez notre catalogue de livres de développement personnel">
  <meta name="keywords" content="livres, développement personnel, catalogue, inspiration, croissance personnelle">

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
    .catalogue-page .product-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      height: 100%;
    }

    .catalogue-page .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .product-image-container {
      height: 280px;
      overflow: hidden;
      position: relative;
      background-color: #f8f9fa;
    }

    .product-image {
      width: 100%;
      height: 100%;
      object-fit: contain;
      transition: transform 0.3s ease;
    }

    .product-card:hover .product-image {
      transform: scale(1.05);
    }

    .price-tag {
      background: #e96308;
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-weight: bold;
    }

    .filter-sidebar {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
    }

    .filter-group {
      margin-bottom: 20px;
    }

    .search-box {
      position: relative;
    }

    .search-btn {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      border: none;
      background: none;
    }

    .sort-select {
      border: 1px solid #dee2e6;
      border-radius: 5px;
      padding: 8px 15px;
    }

    .product-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      z-index: 1;
    }

    .quick-view-btn {
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .product-card:hover .quick-view-btn {
      opacity: 1;
    }

    .empty-catalogue {
      text-align: center;
      padding: 60px 0;
    }

    .empty-catalogue i {
      font-size: 4rem;
      color: #6c757d;
      margin-bottom: 20px;
    }

    .btn-warning {
      background-color: #e96308;
      border-color: #e96308;
      color: white;
    }

    .btn-warning:hover {
      background-color: #d45a07;
      border-color: #d45a07;
      color: white;
    }

    .image-placeholder {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .image-placeholder i {
      font-size: 3rem;
      color: #adb5bd;
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

<body class="catalogue-page">

    <style>
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
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="catalogue.php" class="active">Catalogue</a></li>
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

    <!-- Page Title -->
    <div class="page-title dark-background" data-aos="fade"
      style="background-image: url(assets/img/pexels-element5-1370295.jpg);">
      <div class="container position-relative">
        <h1>Notre Catalogue</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="accueil.php">Accueil</a></li>
            <li class="current">Catalogue</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Catalogue Section -->
    <section id="catalogue" class="catalogue section">
      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">
          <!-- Sidebar Filtres -->
          <div class="col-lg-3">
            <div class="filter-sidebar" data-aos="fade-right">
              <h4>Filtres</h4>

              <!-- Recherche -->
              <div class="filter-group">
                <label class="form-label">Recherche</label>
                <form method="GET" class="search-box">
                  <input type="text" name="search" class="form-control" placeholder="Titre, auteur..."
                    value="<?php echo htmlspecialchars($search); ?>">
                  <button type="submit" class="search-btn">
                    <i class="bi bi-search"></i>
                  </button>
                </form>
              </div>

              <!-- Prix -->
              <div class="filter-group">
                <label class="form-label">Prix (FCFA)</label>
                <form method="GET">
                  <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                  <div class="row g-2">
                    <div class="col-6">
                      <input type="number" name="prix_min" class="form-control" placeholder="Min"
                        value="<?php echo htmlspecialchars($prix_min); ?>" min="0">
                    </div>
                    <div class="col-6">
                      <input type="number" name="prix_max" class="form-control" placeholder="Max"
                        value="<?php echo htmlspecialchars($prix_max); ?>" min="0">
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary btn-sm mt-2 w-100">Appliquer</button>
                </form>
              </div>

              <!-- Réinitialiser -->
              <div class="filter-group">
                <a href="catalogue.php" class="btn btn-outline-secondary w-100">Réinitialiser les filtres</a>
              </div>
            </div>
          </div>

          <!-- Produits -->
          <div class="col-lg-9">
            <!-- En-tête avec tri et résultats -->
            <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-left">
              <div>
                <h4><?php echo count($produits); ?> livre(s) trouvé(s)</h4>
              </div>
              <div>
                <select class="sort-select" onchange="window.location.href = updateUrlParam('tri', this.value)">
                  <option value="nouveautes" <?php echo $tri === 'nouveautes' ? 'selected' : ''; ?>>Nouveautés</option>
                  <option value="prix_croissant" <?php echo $tri === 'prix_croissant' ? 'selected' : ''; ?>>Prix croissant
                  </option>
                  <option value="prix_decroissant" <?php echo $tri === 'prix_decroissant' ? 'selected' : ''; ?>>Prix
                    décroissant</option>
                  <option value="titre" <?php echo $tri === 'titre' ? 'selected' : ''; ?>>Titre A-Z</option>
                  <option value="auteur" <?php echo $tri === 'auteur' ? 'selected' : ''; ?>>Auteur A-Z</option>
                </select>
              </div>
            </div>

            <!-- Grille de produits -->
            <div class="row g-4">
              <?php if (count($produits) > 0): ?>
                <?php foreach ($produits as $index => $produit): ?>
                  <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="product-card card h-100">
                      <div class="position-relative">
                        <div class="product-image-container">
                          <?php if (!empty($produit['image'])): ?>
                            <img src="<?php echo htmlspecialchars($produit['image']); ?>" class="product-image"
                              alt="<?php echo htmlspecialchars($produit['titre']); ?>"
                              onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="image-placeholder" style="display: none;">
                              <i class="bi bi-book"></i>
                            </div>
                          <?php else: ?>
                            <div class="image-placeholder">
                              <i class="bi bi-book"></i>
                            </div>
                          <?php endif; ?>
                        </div>
                        <span class="product-badge price-tag"><?php echo number_format($produit['prix'], 0, ',', ' '); ?>
                          FCFA</span>
                        <button class="quick-view-btn btn btn-primary btn-sm position-absolute bottom-0 end-0 m-2"
                          data-bs-toggle="modal" data-bs-target="#productModal<?php echo $produit['id']; ?>">
                          <i class="bi bi-eye"></i>
                        </button>
                      </div>
                      <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($produit['titre']); ?></h5>
                        <p class="card-text text-muted">par <?php echo htmlspecialchars($produit['auteur']); ?></p>
                      </div>
                      <div class="card-footer bg-transparent">
                        <form method="POST" action="panier.php">
                          <input type="hidden" name="action" value="add_to_cart">
                          <input type="hidden" name="id_produit" value="<?php echo $produit['id']; ?>">
                          <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-cart-plus"></i> Ajouter au panier
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>

                  <!-- Modal Quick View -->
                  <div class="modal fade" id="productModal<?php echo $produit['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title"><?php echo htmlspecialchars($produit['titre']); ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <div class="row">
                            <div class="col-md-6">
                              <?php if (!empty($produit['image'])): ?>
                                <img src="<?php echo htmlspecialchars($produit['image']); ?>" class="img-fluid rounded"
                                  alt="<?php echo htmlspecialchars($produit['titre']); ?>"
                                  onerror="this.style.display='none';">
                              <?php else: ?>
                                <div class="image-placeholder h-100 rounded">
                                  <i class="bi bi-book" style="font-size: 4rem;"></i>
                                </div>
                              <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                              <h4><?php echo htmlspecialchars($produit['titre']); ?></h4>
                              <p class="text-muted">par <?php echo htmlspecialchars($produit['auteur']); ?></p>
                              <h3 class="text-primary"><?php echo number_format($produit['prix'], 0, ',', ' '); ?> FCFA</h3>
                              <p><?php echo htmlspecialchars($produit['description']); ?></p>
                              <form method="POST" action="panier.php" class="mt-3">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="id_produit" value="<?php echo $produit['id']; ?>">
                                <button type="submit" class="btn btn-warning w-100">
                                  <i class="bi bi-cart-plus"></i> Ajouter au panier
                                </button>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="col-12">
                  <div class="empty-catalogue" data-aos="fade-up">
                    <i class="bi bi-book"></i>
                    <h4>Aucun livre trouvé</h4>
                    <p>Essayez de modifier vos critères de recherche ou réinitialisez les filtres</p>
                    <a href="catalogue.php" class="btn btn-primary">Voir tout le catalogue</a>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <!-- Pagination (optionnelle) -->
            <?php if (count($produits) > 0): ?>
              <nav class="mt-5" data-aos="fade-up">
                <ul class="pagination justify-content-center">
                  <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Précédent</a>
                  </li>
                  <li class="page-item active"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item">
                    <a class="page-link" href="#">Suivant</a>
                  </li>
                </ul>
              </nav>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </section><!-- /Catalogue Section -->

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
              <button class="btn btn-outline w-100" type="submit" name="subscribe" style="border-color: #e96308; color:#fff; background-color: #e96308;">
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
            <p class="mb-0">© 2025 <span class="sitename" style="color:#e96308;">InspiLivres</span>. Tous droits réservés.</p>
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
<div id="newsletter-message" style="display: none; position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 9999; opacity: 0; transition: opacity 0.5s ease; font-weight: 500; max-width: 300px;"></div>

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
  .cart-count-badge {
      background-color: #e96308;
      color: white;
      border-radius: 50%;
      padding: 3px 8px;
      font-size: 0.7rem;
      margin-left: 5px;
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
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    function updateUrlParam(key, value) {
      const url = new URL(window.location.href);
      url.searchParams.set(key, value);
      return url.toString();
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function () {
      // Animation au scroll
      AOS.init({
        duration: 1000,
        easing: 'ease-in-out',
        once: true,
        mirror: false
      });

      // Gestion des modales
      const productModals = document.querySelectorAll('.modal');
      productModals.forEach(modal => {
        new bootstrap.Modal(modal);
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
  document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', function(e) {
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