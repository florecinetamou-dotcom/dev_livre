<?php
session_start();

// Vérifier si l'utilisateur est connecté
$est_connecte = isset($_SESSION['user_id']);

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

// Récupérer l'ID utilisateur (depuis la session si connecté)
$id_utilisateur = $est_connecte ? $_SESSION['user_id'] : null;

// GESTION DE L'AJOUT AU PANIER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
  $id_produit = $_POST['id_produit'];

  // Si l'utilisateur est connecté, utiliser la base de données
  if ($est_connecte) {
    // Vérifier si le produit est déjà dans le panier
    $check_sql = "SELECT * FROM panier WHERE id_utilisateur = ? AND id_produits = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $id_utilisateur, $id_produit);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      // Mettre à jour la quantité
      $update_sql = "UPDATE panier SET quantite = quantite + 1 WHERE id_utilisateur = ? AND id_produits = ?";
      $update_stmt = $conn->prepare($update_sql);
      $update_stmt->bind_param("ii", $id_utilisateur, $id_produit);
      $update_stmt->execute();
    } else {
      // Ajouter le produit au panier
      $insert_sql = "INSERT INTO panier (id_utilisateur, id_produits, quantite) VALUES (?, ?, 1)";
      $insert_stmt = $conn->prepare($insert_sql);
      $insert_stmt->bind_param("ii", $id_utilisateur, $id_produit);
      $insert_stmt->execute();
    }
  }

  // Mettre à jour la session dans tous les cas
  if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
  }

  if (isset($_SESSION['panier'][$id_produit])) {
    $_SESSION['panier'][$id_produit]['quantite']++;
  } else {
    // Récupérer les infos du produit
    $product_sql = "SELECT titre, prix FROM produits WHERE id = ?";
    $product_stmt = $conn->prepare($product_sql);
    $product_stmt->bind_param("i", $id_produit);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();

    if ($product_result->num_rows > 0) {
      $product = $product_result->fetch_assoc();
      $_SESSION['panier'][$id_produit] = [
        'quantite' => 1,
        'titre' => $product['titre'],
        'prix' => $product['prix']
      ];
    }
  }

  // Rediriger vers le panier
  header("Location: panier.php");
  exit();
}

// Traitement des autres actions sur le panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_quantity' && isset($_POST['id_produit']) && isset($_POST['quantite'])) {
      $id_produit = $_POST['id_produit'];
      $quantite = $_POST['quantite'];

      if ($est_connecte) {
        if ($quantite <= 0) {
          // Supprimer l'article si quantité = 0
          $stmt = $conn->prepare("DELETE FROM panier WHERE id_utilisateur = ? AND id_produits = ?");
          $stmt->bind_param("ii", $id_utilisateur, $id_produit);
          unset($_SESSION['panier'][$id_produit]);
        } else {
          // Mettre à jour la quantité
          $stmt = $conn->prepare("UPDATE panier SET quantite = ? WHERE id_utilisateur = ? AND id_produits = ?");
          $stmt->bind_param("iii", $quantite, $id_utilisateur, $id_produit);
          if (isset($_SESSION['panier'][$id_produit])) {
            $_SESSION['panier'][$id_produit]['quantite'] = $quantite;
          }
        }
        $stmt->execute();
      } else {
        // Utilisateur non connecté - mise à jour de la session seulement
        if ($quantite <= 0) {
          unset($_SESSION['panier'][$id_produit]);
        } else {
          if (isset($_SESSION['panier'][$id_produit])) {
            $_SESSION['panier'][$id_produit]['quantite'] = $quantite;
          }
        }
      }
    } elseif ($_POST['action'] === 'remove_item' && isset($_POST['id_produit'])) {
      // Supprimer un article du panier
      $id_produit = $_POST['id_produit'];
      
      if ($est_connecte) {
        $stmt = $conn->prepare("DELETE FROM panier WHERE id_utilisateur = ? AND id_produits = ?");
        $stmt->bind_param("ii", $id_utilisateur, $id_produit);
        $stmt->execute();
      }
      
      unset($_SESSION['panier'][$id_produit]);
    } elseif ($_POST['action'] === 'clear_cart') {
      // Vider le panier
      if ($est_connecte) {
        $stmt = $conn->prepare("DELETE FROM panier WHERE id_utilisateur = ?");
        $stmt->bind_param("i", $id_utilisateur);
        $stmt->execute();
      }
      
      $_SESSION['panier'] = [];
    }
  }
  // Rediriger pour éviter la resoumission du formulaire
  header("Location: panier.php");
  exit();
}

// Récupérer les articles du panier
if ($est_connecte) {
  // Utilisateur connecté - récupérer depuis la base de données
  $sql = "SELECT p.id_panier, p.id_produits, p.quantite, pr.titre, pr.auteur, pr.prix, pr.image 
          FROM panier p 
          JOIN produits pr ON p.id_produits = pr.id 
          WHERE p.id_utilisateur = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id_utilisateur);
  $stmt->execute();
  $result = $stmt->get_result();

  $articles = [];
  $total_panier = 0;

  while ($row = $result->fetch_assoc()) {
    $total_article = $row['prix'] * $row['quantite'];
    $row['total_article'] = $total_article;
    $articles[] = $row;
    $total_panier += $total_article;
    
    // Mettre à jour la session pour cohérence
    $_SESSION['panier'][$row['id_produits']] = [
      'quantite' => $row['quantite'],
      'titre' => $row['titre'],
      'prix' => $row['prix']
    ];
  }
} else {
  // Utilisateur non connecté - récupérer depuis la session
  $articles = [];
  $total_panier = 0;

  if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $id_produit => $item) {
      // Récupérer les détails du produit depuis la base de données
      $product_sql = "SELECT titre, auteur, prix, image FROM produits WHERE id = ?";
      $product_stmt = $conn->prepare($product_sql);
      $product_stmt->bind_param("i", $id_produit);
      $product_stmt->execute();
      $product_result = $product_stmt->get_result();
      
      if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        $total_article = $product['prix'] * $item['quantite'];
        
        $articles[] = [
          'id_produits' => $id_produit,
          'quantite' => $item['quantite'],
          'titre' => $product['titre'],
          'auteur' => $product['auteur'],
          'prix' => $product['prix'],
          'image' => $product['image'],
          'total_article' => $total_article
        ];
        
        $total_panier += $total_article;
      }
    }
  }
}

$conn->close();

// Calculer le nombre total d'articles pour le badge
$nombre_articles = 0;
foreach ($articles as $article) {
  $nombre_articles += $article['quantite'];
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Panier - InspiLivres</title>
  <meta name="description" content="Votre panier d'achat de livres de développement personnel">
  <meta name="keywords" content="livres, développement personnel, panier, achats">

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
    .cart-page {
      background-color: #f8f9fa;
      padding-bottom: 50px;
    }

    .cart-item {
      background: white;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 15px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .cart-item-image-container {
      width: 100px;
      height: 140px;
      overflow: hidden;
      border-radius: 5px;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cart-item-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .image-placeholder {
      font-size: 2rem;
      color: #adb5bd;
    }

    .quantity-controls {
      display: flex;
      align-items: center;
    }

    .quantity-btn {
      width: 35px;
      height: 35px;
      border: 1px solid #ddd;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border-radius: 5px;
      transition: all 0.3s ease;
    }

    .quantity-btn:hover {
      background: #e96308;
      color: white;
      border-color: #e96308;
    }

    .quantity-input {
      width: 60px;
      height: 35px;
      text-align: center;
      margin: 0 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
    }

    .cart-summary {
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
      position: sticky;
      top: 20px;
    }

    .empty-cart {
      text-align: center;
      padding: 60px 0;
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .empty-cart i {
      font-size: 4rem;
      color: #e96308;
      margin-bottom: 20px;
    }

    .cart-count-badge {
      background-color: #e96308;
      color: white;
      border-radius: 50%;
      padding: 3px 8px;
      font-size: 0.7rem;
      margin-left: 5px;
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

    .price-highlight {
      color: #e96308;
      font-weight: bold;
      font-size: 1.1rem;
    }

    .remove-btn {
      color: #dc3545;
      transition: color 0.3s ease;
    }

    .remove-btn:hover {
      color: #c82333;
    }

    .cart-title {
      color: #2c3e50;
      font-weight: 700;
      margin-bottom: 30px;
    }

    .btn-continue-shopping {
      background-color: #2c3e50;
      border-color: #2c3e50;
      color: white;
    }

    .btn-continue-shopping:hover {
      background-color: #1a252f;
      border-color: #1a252f;
      color: white;
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

<body class="cart-page">

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
              <a class="btn-panier" href="panier.php" class="active">
                <i class="bi bi-cart"></i> Panier
                <span class="cart-count-badge">
                  <?php
                  // Afficher le nombre total d'articles calculé précédemment
                  echo $nombre_articles;
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
          <h1>Votre Panier</h1>
          <nav class="breadcrumbs">
            <ol>
              <li><a href="accueil.php">Accueil</a></li>
              <li class="current">Panier</li>
            </ol>
          </nav>
        </div>
      </div><!-- End Page Title -->

      <!-- Cart Section -->
      <section id="cart" class="cart section">
        <div class="container" data-aos="fade-up" data-aos-delay="100">

          <?php if (count($articles) > 0): ?>
            <div class="row">
              <div class="col-lg-8">
                <h2 class="cart-title">Vos articles (<?php echo $nombre_articles; ?>)</h2>

                <?php foreach ($articles as $article): ?>
                  <div class="cart-item" data-aos="fade-up">
                    <div class="row align-items-center">
                      <div class="col-md-2">
                        <div class="cart-item-image-container">
                          <?php if (!empty($article['image'])): ?>
                            <img src="<?php echo htmlspecialchars($article['image']); ?>"
                              alt="<?php echo htmlspecialchars($article['titre']); ?>" class="cart-item-image"
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
                      </div>
                      <div class="col-md-4">
                        <h5><?php echo htmlspecialchars($article['titre']); ?></h5>
                        <p class="text-muted">par <?php echo htmlspecialchars($article['auteur']); ?></p>
                      </div>
                      <div class="col-md-2">
                        <p class="price-highlight"><?php echo number_format($article['prix'], 0, ',', ' '); ?> FCFA</p>
                      </div>
                      <div class="col-md-2">
                        <div class="quantity-controls">
                          <button type="button" class="quantity-btn minus"
                            onclick="updateQuantity(<?php echo $article['id_produits']; ?>, <?php echo $article['quantite'] - 1; ?>)">-</button>
                          <input type="number" name="quantite" value="<?php echo $article['quantite']; ?>" min="1"
                            class="quantity-input" id="qty-<?php echo $article['id_produits']; ?>"
                            onchange="updateQuantity(<?php echo $article['id_produits']; ?>, this.value)">
                          <button type="button" class="quantity-btn plus"
                            onclick="updateQuantity(<?php echo $article['id_produits']; ?>, <?php echo $article['quantite'] + 1; ?>)">+</button>
                        </div>
                      </div>
                      <div class="col-md-2 text-end">
                        <p class="price-highlight"><?php echo number_format($article['total_article'], 0, ',', ' '); ?> FCFA
                        </p>
                        <form method="post" class="d-inline">
                          <input type="hidden" name="action" value="remove_item">
                          <input type="hidden" name="id_produit" value="<?php echo $article['id_produits']; ?>">
                          <button type="submit" class="btn btn-link remove-btn p-0" title="Supprimer">
                            <i class="bi bi-trash"></i> Supprimer
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>

                <div class="d-flex gap-2 mt-4">
                  <form method="post">
                    <input type="hidden" name="action" value="clear_cart">
                    <button type="submit" class="btn btn-outline-secondary">
                      <i class="bi bi-trash"></i> Vider le panier
                    </button>
                  </form>
                  <a href="catalogue.php" class="btn btn-continue-shopping">
                    <i class="bi bi-arrow-left"></i> Continuer mes achats
                  </a>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="cart-summary" data-aos="fade-left" data-aos-delay="200">
                  <h4 class="mb-4">Récapitulatif de la commande</h4>

                  <div class="summary-details">
                    <div class="d-flex justify-content-between mb-3">
                      <span>Sous-total (<?php echo $nombre_articles; ?>
                        article<?php echo $nombre_articles > 1 ? 's' : ''; ?>)</span>
                      <span class="price-highlight"><?php echo number_format($total_panier, 0, ',', ' '); ?> FCFA</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                      <span>Frais de livraison</span>
                      <span class="text-success">Gratuit</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                      <strong>Total</strong>
                      <strong class="price-highlight"
                        style="font-size: 1.3rem;"><?php echo number_format($total_panier, 0, ',', ' '); ?> FCFA</strong>
                    </div>
                  </div>

                  <a href="paiement.php" class="btn btn-warning w-100 py-3 mb-3">
                    <i class="bi bi-lock-fill"></i> Procéder au paiement
                  </a>

                  <div class="payment-methods text-center">
                    <p class="small text-muted mb-2">Paiement sécurisé</p>
                    <div class="d-flex justify-content-center gap-3">
                      <i class="bi bi-credit-card fs-4"></i>
                      <i class="bi bi-paypal fs-4"></i>
                      <i class="bi bi-bank fs-4"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="empty-cart" data-aos="fade-up">
              <i class="bi bi-cart-x"></i>
              <h3>Votre panier est vide</h3>
              <p class="text-muted mb-4">Découvrez notre sélection de livres de développement personnel</p>
              <a href="catalogue.php" class="btn btn-warning"> Explorer le catalogue </a>
            </div>
          <?php endif; ?>

        </div>
      </section><!-- /Cart Section -->

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
        background-color: #28292865;
        color: white;
      }

      .newsletter-warning {
        background-color: #ffb007ff;
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

    <script>
      function updateQuantity(productId, newQuantity) {
        if (newQuantity < 1) newQuantity = 1;

        // Mettre à jour l'interface
        document.getElementById('qty-' + productId).value = newQuantity;

        // Soumettre le formulaire
        const formData = new FormData();
        formData.append('action', 'update_quantity');
        formData.append('id_produit', productId);
        formData.append('quantite', newQuantity);

        fetch('panier.php', {
          method: 'POST',
          body: formData
        }).then(response => {
          // Recharger la page pour voir les changements
          window.location.reload();
        });
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
    
    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>

  </body>

</html>