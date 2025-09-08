<?php
// Démarrage de session et vérification de connexion
session_start();

// Redirection si utilisateur non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=mon_espace');
    exit;
}

// Configuration de la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dev_livre";

// Connexion à la base de données
try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Récupération des informations utilisateur
    $user_id = $_SESSION['user_id'];
    $sql_user = "SELECT * FROM utilisateur WHERE id_utilisateur = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();

    // Récupération des commandes de l'utilisateur
    $sql_orders = "SELECT * FROM commande WHERE id_utilisateur = ? ORDER BY date_commande DESC LIMIT 5";
    $stmt_orders = $conn->prepare($sql_orders);
    $stmt_orders->bind_param("i", $user_id);
    $stmt_orders->execute();
    $orders = $stmt_orders->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_orders->close();

    $conn->close();

} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}

// Initialisation du panier si non existant
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace - InspiLivres</title>
    <meta name="description"
        content="Espace personnel sur InspiLivres - Gérez vos commandes et informations personnelles">
    <meta name="keywords" content="mon espace, compte, commandes, informations personnelles">

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
            --orange-light: rgba(233, 99, 8, 0.1);
        }


        /* Pour déboguer: afficher une bordure rouge si l'image ne charge pas */


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

        .cart-count-badge {
            background-color: white;
            color: var(--orange-primary);
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.65rem;
            margin-left: 4px;
            font-weight: bold;
        }

        /* Styles de l'espace utilisateur */

        main {
            position: relative;
            top: 70px;
            margin-bottom: 50px;
            position: relative;
            z-index: 1;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.8rem;
            margin: 0 auto 15px;
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--orange-primary), var(--orange-hover));
            color: white;
            border: none;
        }

        .welcome-card h3,
        .welcome-card p {
            color: white;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            border-left: 4px solid var(--orange-primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            font-size: 1.8rem;
            color: var(--orange-primary);
            margin-bottom: 10px;
        }

        .order-item {
            background-color: #f8f9fa;
            border-left: 3px solid var(--orange-primary);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: background-color 0.2s, transform 0.2s;
        }

        .order-item:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f1f1;
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background-color: var(--orange-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--orange-primary);
            margin-right: 15px;
            flex-shrink: 0;
        }

        .info-content small {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .info-content p {
            margin-bottom: 0;
            font-weight: 500;
        }

        .book-recommendation {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
            height: 100%;
        }

        .book-recommendation:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background-color: white;
        }

        .book-cover {
            width: 50px;
            height: 70px;
            background-color: #dee2e6;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 1.5rem;
        }

        /* Boutons améliorés */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--orange-primary);
            border-color: var(--orange-primary);
        }

        .btn-primary:hover {
            background-color: var(--orange-hover);
            border-color: var(--orange-hover);
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            color: var(--orange-primary);
            border-color: var(--orange-primary);
        }

        .btn-outline-primary:hover {
            background-color: var(--orange-primary);
            border-color: var(--orange-primary);
            color: white;
            transform: translateY(-2px);
        }

        /* Navigation sidebar */
        .sidebar-nav .btn {
            text-align: left;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }

        .sidebar-nav .btn i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        /* Fallback image */
        .fallback-image {
            display: none;
            text-align: center;
            padding: 20px;
            background: #f5f5f5;
            margin: 20px 0;
            border-radius: 5px;
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
            .page-title {
                padding: 80px 0 40px;
                margin-top: 60px;
            }

            .header-buttons {
                margin-left: 15px;
            }

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
                padding: 3px 10px !important;
                ;
            }

            .info-item {
                flex-direction: column;
                text-align: center;
            }

            .info-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
    ?>
    <!-- Header -->
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
                            <a href="mon_espace.php" class="btn-mon-espace" class="active">
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


    <!-- Main Content -->
    <main class="main">

        <!-- Fallback content if image doesn't load -->
        <div class="fallback-image" id="fallbackImage">
            <p>L'image de fond ne s'est pas chargée correctement.</p>
        </div>

        <section class="section py-5">
            <div class="container">
                <div class="row">
                    <!-- Sidebar Navigation -->
                    <div class="col-lg-3 mb-4">
                        <div class="profile-card text-center">
                            <?php
                            $initial = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
                            ?>
                            <div class="profile-avatar">
                                <?php echo $initial; ?>
                            </div>
                            <h4><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>

                            <div class="sidebar-nav">
                                <a href="mon_espace.php" class="btn btn-primary w-100">
                                    <i class="bi bi-person me-2"></i>Profil
                                </a>
                                <a href="mes_commandes.php" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-bag me-2"></i>Mes Commandes
                                </a>
                                <a href="mes_telechargements.php" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-download me-2"></i>Mes téléchargements
                                </a>
                                <a href="logout.php" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </a>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="profile-card">
                            <h5 class="mb-3">Votre activité</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Commandes</span>
                                <span class="fw-bold"><?php echo count($orders); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Membre depuis</span>
                                <span
                                    class="fw-bold"><?php echo date('m/Y', strtotime($user['date_inscription'])); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Note moyenne</span>
                                <span class="fw-bold">4.8/5</span>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="col-lg-9">
                        <!-- Welcome Card with Quick Actions -->
                        <div class="welcome-card profile-card mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3>Bonjour, <?php echo htmlspecialchars($user['prenom']); ?> !</h3>
                                    <p class="mb-0">Que souhaitez-vous faire aujourd'hui ?</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <a href="catalogue.php" class="btn btn-light">
                                        <i class="bi bi-book me-1"></i>Voir le catalogue
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="stat-card h-100">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon me-3">
                                            <i class="bi bi-bag"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?php echo count($orders); ?></h3>
                                            <p class="text-muted mb-0">Commandes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="stat-card h-100">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon me-3">
                                            <i class="bi bi-star"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0">4.8</h3>
                                            <p class="text-muted mb-0">Note moyenne</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="stat-card h-100">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon me-3">
                                            <i class="bi bi-calendar"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0">
                                                <?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?>
                                            </h3>
                                            <p class="text-muted mb-0">Membre depuis</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Dernières commandes -->
                            <div class="col-lg-7 mb-4">
                                <div class="profile-card h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="mb-0">Vos dernières commandes</h4>
                                        <a href="mes_commandes.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
                                    </div>

                                    <?php if (count($orders) > 0): ?>
                                        <div class="order-list">
                                            <?php foreach ($orders as $order): ?>
                                                <div class="order-item">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <strong>Commande #<?php echo $order['id_commande']; ?></strong>
                                                            <span
                                                                class="badge badge-status status-<?php echo strtolower($order['statut']); ?> ms-2">
                                                                <?php echo $order['statut']; ?>
                                                            </span>
                                                        </div>
                                                        <div class="text-muted small">
                                                            <?php echo date('d/m/Y', strtotime($order['date_commande'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span><?php echo number_format($order['total'], 0, ',', ' '); ?>
                                                            FCFA</span>
                                                        <a href="details_commande.php?id=<?php echo $order['id_commande']; ?>"
                                                            class="btn btn-sm btn-outline-primary">
                                                            Détails
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="bi bi-bag display-1 text-muted"></i>
                                            <h5 class="mt-3">Aucune commande</h5>
                                            <p class="text-muted">Vous n'avez pas encore passé de commande.</p>
                                            <a href="catalogue.php" class="btn btn-primary">Découvrir nos livres</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Informations personnelles -->
                            <div class="col-lg-5 mb-4">
                                <div class="profile-card h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="mb-0">Vos informations</h4>
                                        <a href="modifier_profil.php"
                                            class="btn btn-sm btn-outline-primary">Modifier</a>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div class="info-content">
                                            <small class="text-muted">Nom complet</small>
                                            <p class="mb-0">
                                                <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="bi bi-envelope"></i>
                                        </div>
                                        <div class="info-content">
                                            <small class="text-muted">Email</small>
                                            <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="bi bi-telephone"></i>
                                        </div>
                                        <div class="info-content">
                                            <small class="text-muted">Téléphone</small>
                                            <p class="mb-0">
                                                <?php echo !empty($user['telephone']) ? htmlspecialchars($user['telephone']) : 'Non renseigné'; ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="bi bi-calendar-event"></i>
                                        </div>
                                        <div class="info-content">
                                            <small class="text-muted">Membre depuis</small>
                                            <p class="mb-0">
                                                <?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Recommandations -->
                        <div class="profile-card">
                            <h4 class="mb-4">Livres que vous pourriez aimer</h4>
                            <div class="row">
                                <?php
                                try {
                                    // Utiliser la même connexion que le reste de la page
                                    $conn = new mysqli("localhost", "root", "", "dev_livre");

                                    if ($conn->connect_error) {
                                        throw new Exception("Connection failed: " . $conn->connect_error);
                                    }

                                    // Récupérer les 3 derniers produits avec leur image
                                    $sql = "SELECT id, titre, auteur, image, prix, description FROM produits ORDER BY date_ajout DESC LIMIT 3";
                                    $result = $conn->query($sql);

                                    if ($result && $result->num_rows > 0) {
                                        while ($produit = $result->fetch_assoc()) {
                                            // Utiliser le chemin d'image tel qu'il est stocké en base
                                            $imagePath = !empty($produit['image']) ? $produit['image'] : "assets/img/livre-par-defaut.jpg";
                                            ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="book-recommendation p-3 shadow rounded bg-white h-100">
                                                    <div class="d-flex align-items-center">
                                                        <!-- Image du livre -->
                                                        <div class="book-cover me-3">
                                                            <img src="<?= $imagePath ?>"
                                                                alt="Couverture de <?= htmlspecialchars($produit['titre']) ?>"
                                                                class="img-fluid rounded shadow-sm"
                                                                style="width: 65px; height: 95px; object-fit: cover;"
                                                                onerror="this.onerror=null; this.src='assets/img/livre-par-defaut.jpg'">
                                                        </div>
                                                        <!-- Infos du livre -->
                                                        <div class="book-info">
                                                            <h6 class="mb-1 book-title"><?= htmlspecialchars($produit['titre']) ?>
                                                            </h6>
                                                            <small
                                                                class="text-muted book-author"><?= htmlspecialchars($produit['auteur']) ?></small>
                                                            <div class="book-rating mt-1">
                                                                <span class="text-warning">
                                                                    <i class="bi bi-star-fill"></i>
                                                                    <i class="bi bi-star-fill"></i>
                                                                    <i class="bi bi-star-fill"></i>
                                                                    <i class="bi bi-star-fill"></i>
                                                                    <i class="bi bi-star-half"></i>
                                                                </span>
                                                            </div>
                                                            <div class="book-price mt-2">
                                                                <strong><?= number_format($produit['prix'], 0, ',', ' ') ?>
                                                                    FCFA</strong>
                                                            </div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary mt-2 view-details"
                                                                data-bs-toggle="modal" data-bs-target="#bookModal"
                                                                data-id="<?= $produit['id'] ?>"
                                                                data-titre="<?= htmlspecialchars($produit['titre']) ?>"
                                                                data-auteur="<?= htmlspecialchars($produit['auteur']) ?>"
                                                                data-image="<?= $imagePath ?>"
                                                                data-prix="<?= number_format($produit['prix'], 0, ',', ' ') ?>"
                                                                data-description="<?= htmlspecialchars($produit['description']) ?>">
                                                                Voir détails
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        echo '<div class="col-12 text-center py-4">
                    <i class="bi bi-book display-4 text-muted"></i>
                    <h5 class="mt-3">Aucun livre disponible</h5>
                    <p class="text-muted">Revenez bientôt pour découvrir nos nouveautés.</p>
                </div>';
                                    }

                                    $conn->close();

                                } catch (Exception $e) {
                                    echo '<div class="col-12"><div class="alert alert-warning">Erreur lors du chargement des recommandations: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
                                }
                                ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="catalogue.php" class="btn btn-outline-primary">Voir plus de suggestions</a>
                            </div>
                        </div>

                        <!-- Modal pour les détails du livre -->
                        <div class="modal fade" id="bookModal" tabindex="-1" aria-labelledby="bookModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="bookModalLabel">Détails du livre</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <img id="modalBookImage" src="" class="img-fluid rounded shadow"
                                                    alt="Couverture du livre">
                                            </div>
                                            <div class="col-md-8">
                                                <h3 id="modalBookTitle"></h3>
                                                <p class="text-muted" id="modalBookAuthor"></p>
                                                <div class="book-rating mb-3">
                                                    <span class="text-warning">
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-half"></i>
                                                    </span>
                                                    <span class="ms-2">4.5/5</span>
                                                </div>
                                                <div class="book-price mb-3">
                                                    <h4 class="text-primary" id="modalBookPrice"></h4>
                                                </div>
                                                <div class="book-description">
                                                    <h5>Description</h5>
                                                    <p id="modalBookDescription"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Fermer</button>
                                        <button type="button" class="btn btn-primary" id="addToCartBtn">
                                            <i class="bi bi-cart-plus"></i> Ajouter au panier
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            .book-recommendation {
                                transition: all 0.3s ease;
                                border: 1px solid rgba(0, 0, 0, 0.1);
                            }

                            .book-recommendation:hover {
                                transform: translateY(-5px);
                                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
                            }

                            .book-cover {
                                flex-shrink: 0;
                            }

                            .book-info {
                                flex-grow: 1;
                            }

                            .book-title {
                                font-weight: 600;
                                font-size: 0.95rem;
                                line-height: 1.3;
                                color: #333;
                                margin-bottom: 4px;
                            }

                            .book-author {
                                font-size: 0.85rem;
                                color: #6c757d;
                            }

                            .book-rating {
                                font-size: 0.8rem;
                            }

                            .book-price {
                                font-size: 0.9rem;
                                color: #e96308;
                            }

                            @media (max-width: 768px) {
                                .book-recommendation {
                                    padding: 15px !important;
                                }

                                .book-title {
                                    font-size: 0.9rem;
                                }
                            }
                        </style>


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
        // Fonction pour vérifier si l'image de fond est chargée
        function checkBackgroundImage() {
            const pageTitle = document.getElementById('pageTitle');
            const imageUrl = 'assets/img/pexels-element5-1370295.jpg';

            // Créer une image pour tester le chargement
            const testImage = new Image();
            testImage.onload = function () {
                document.getElementById('imageStatus').textContent = 'Image chargée';
                document.getElementById('imageStatus').style.color = 'green';
            };
            testImage.onerror = function () {
                document.getElementById('imageStatus').textContent = 'ERREUR: Image non trouvée';
                document.getElementById('imageStatus').style.color = 'red';
                document.body.classList.add('image-not-loaded');
                document.getElementById('fallbackImage').style.display = 'block';

                // Ajouter une couleur de fond de secours
                pageTitle.style.backgroundColor = '#333';
            };
            testImage.src = imageUrl;
        }

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
            checkBackgroundImage(); // Vérifier le chargement de l'image

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

        // Gestion de la modal des livres
        document.addEventListener('DOMContentLoaded', function () {
            // Gérer l'ouverture de la modal avec les données du livre
            var bookModal = document.getElementById('bookModal');
            if (bookModal) {
                bookModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;

                    // Récupérer les données du bouton
                    var id = button.getAttribute('data-id');
                    var titre = button.getAttribute('data-titre');
                    var auteur = button.getAttribute('data-auteur');
                    var image = button.getAttribute('data-image');
                    var prix = button.getAttribute('data-prix');
                    var description = button.getAttribute('data-description');

                    // Mettre à jour le contenu de la modal
                    document.getElementById('modalBookTitle').textContent = titre;
                    document.getElementById('modalBookAuthor').textContent = auteur;
                    document.getElementById('modalBookImage').src = image;
                    document.getElementById('modalBookPrice').textContent = prix + ' FCFA';
                    document.getElementById('modalBookDescription').textContent = description || 'Aucune description disponible.';

                    // Configurer le bouton "Ajouter au panier"
                    document.getElementById('addToCartBtn').onclick = function () {
                        addToCart(id, titre, prix);
                    };
                });
            }

            // Fonction pour ajouter au panier
            function addToCart(id, titre, prix) {
                // Ici, vous pouvez implémenter la logique d'ajout au panier
                // Par exemple, utiliser AJAX pour envoyer la requête au serveur
                alert('Le livre "' + titre + '" a été ajouté à votre panier !');

                // Fermer la modal après l'ajout
                var modalEl = document.getElementById('bookModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
            }
        });
    </script>

    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>
</body>

</html>