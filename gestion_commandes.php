<?php
session_start();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dev_livre";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $id_commande = intval($_POST['id_commande']);
        
        switch ($_POST['action']) {
            case 'changer_statut':
                $nouveau_statut = $_POST['statut'];
                $stmt = $conn->prepare("UPDATE commande SET statut = ? WHERE id_commande = ?");
                $stmt->bind_param("si", $nouveau_statut, $id_commande);
                $stmt->execute();
                $_SESSION['message'] = "Statut de la commande mis à jour";
                break;
                
            case 'supprimer_commande':
                // Supprimer la commande
                $stmt = $conn->prepare("DELETE FROM commande WHERE id_commande = ?");
                $stmt->bind_param("i", $id_commande);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Commande supprimée avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de la suppression de la commande";
                }
                break;
        }
    }
    header("Location: gestion_commandes.php");
    exit();
}

// Récupérer toutes les commandes avec les informations utilisateur
$query = "
    SELECT 
        c.id_commande,
        c.date_commande,
        c.statut,
        c.total,
        u.id_utilisateur,
        u.nom,
        u.prenom,
        u.email,
        u.role
    FROM commande c
    INNER JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
    ORDER BY c.date_commande DESC
";

$result = $conn->query($query);
$commandes = $result->fetch_all(MYSQLI_ASSOC);

// Récupérer les détails des livres pour chaque commande
$details_commandes = [];
foreach ($commandes as $commande) {
    $id_commande = $commande['id_commande'];
    
    // Cette requête suppose que vous avez une table 'ligne_commande' qui fait le lien entre commande et produits
    $query_details = "
        SELECT 
            p.titre,
            lc.quantite,
            lc.prix_unitaire
        FROM ligne_commande lc
        INNER JOIN produits p ON lc.id_produit = p.id
        WHERE lc.id_commande = $id_commande
    ";
    
    $result_details = $conn->query($query_details);
    $details_commandes[$id_commande] = $result_details->fetch_all(MYSQLI_ASSOC);
}

// Récupérer les messages de session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['message'], $_SESSION['error']);

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Gestion des Commandes - Admin InspiLivres</title>

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
    
    .admin-container {
      background: #f8f9fa;
      min-height: 100vh;
    }
    .sidebar {
      background: #2c3e50;
      color: white;
      min-height: 100vh;
      position: fixed;
      width: 250px;
    }
    .main-content {
      margin-left: 250px;
      padding: 20px;
    }
    .nav-link {
      color: #ecf0f1;
      padding: 15px 20px;
      border-left: 4px solid transparent;
    }
    .nav-link:hover, .nav-link.active {
      background: #34495e;
      color: var(--orange-primary);
      border-left-color: var(--orange-primary);
    }
    .stats-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }
    .stats-card:hover {
      transform: translateY(-2px);
    }
    .admin-header {
      background: white;
      padding: 15px 20px;
      border-bottom: 1px solid #dee2e6;
    }
    
    /* Badges de statut */
    .badge-statut {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }
    .statut-en-attente {
      background-color: #fff3cd;
      color: #856404;
    }
    .statut-confirme {
      background-color: #d4edda;
      color: #155724;
    }
    .statut-expedie {
      background-color: #cce5ff;
      color: #004085;
    }
    .statut-livre {
      background-color: #d1ecf1;
      color: #0c5460;
    }
    .statut-annule {
      background-color: #f8d7da;
      color: #721c24;
    }
    
    /* Cartes de commande */
    .commande-card {
      border: 1px solid #dee2e6;
      border-radius: 10px;
      margin-bottom: 20px;
      background: white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .commande-header {
      background: #f8f9fa;
      padding: 15px;
      border-bottom: 1px solid #dee2e6;
      border-radius: 10px 10px 0 0;
    }
    .commande-body {
      padding: 15px;
    }
    .livre-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #f8f9fa;
    }
    .livre-item:last-child {
      border-bottom: none;
    }
    
    /* Boutons */
    .btn-primary {
      background-color: var(--orange-primary);
      border-color: var(--orange-primary);
    }
    .btn-primary:hover {
      background-color: var(--orange-hover);
      border-color: var(--orange-hover);
    }
  </style>
</head>

<body class="admin-container">

  <div class="sidebar">
    <div class="text-center py-4">
      <h4 class="mb-0" style="color:#e96308">InspiLivres</h4>
      <small style="color:#fff">Panel Admin</small>
    </div>
    
    <nav class="nav flex-column mt-4">
      <a class="nav-link" href="gestion_livres.php">
        <i class="bi bi-book me-2"></i>Gestion des Livres
      </a>
      <a class="nav-link" href="gestion_utilisateurs.php">
        <i class="bi bi-people me-2"></i>Utilisateurs
      </a>
      <a class="nav-link active" href="gestion_commandes.php">
        <i class="bi bi-cart me-2"></i>Commandes
      </a>
      <a class="nav-link" href="statistiques.php">
        <i class="bi bi-bar-chart me-2"></i>Statistiques
      </a>
      <a class="nav-link" href="accueil.php">
        <i class="bi bi-house me-2"></i>Retour au site
      </a>
      <a class="nav-link" href="logout.php">
        <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
      </a>
    </nav>
  </div>

  <div class="main-content">
    <!-- Header -->
    <div class="admin-header mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Gestion des Commandes</h2>
        <span class="text-muted"><?php echo date('d/m/Y'); ?></span>
      </div>
    </div>

    <!-- Notifications -->
    <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      <i class="bi bi-check-circle me-2"></i><?php echo $message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-cart fs-1 text-primary"></i>
          <h3 class="mt-2"><?php echo count($commandes); ?></h3>
          <p class="text-muted mb-0">Total Commandes</p>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-currency-exchange fs-1 text-success"></i>
          <h3 class="mt-2">
            <?php 
            $total_ventes = array_sum(array_column($commandes, 'total'));
            echo number_format($total_ventes, 0, ',', ' ') . ' FCFA'; 
            ?>
          </h3>
          <p class="text-muted mb-0">Chiffre d'Affaires</p>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-check-circle fs-1 text-warning"></i>
          <h3 class="mt-2">
            <?php 
            $commandes_confirmees = array_filter($commandes, function($c) { 
                return $c['statut'] === 'confirmée'; 
            });
            echo count($commandes_confirmees); 
            ?>
          </h3>
          <p class="text-muted mb-0">Commandes Confirmées</p>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-people fs-1 text-info"></i>
          <h3 class="mt-2">
            <?php 
            $clients_uniques = array_unique(array_column($commandes, 'id_utilisateur'));
            echo count($clients_uniques); 
            ?>
          </h3>
          <p class="text-muted mb-0">Clients Uniques</p>
        </div>
      </div>
    </div>

    <!-- Liste des commandes -->
    <div class="card border-0 shadow">
      <div class="card-header bg-dark text-white">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>Liste des Commandes
          </h5>
          <span class="badge bg-warning"><?php echo count($commandes); ?> commandes</span>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($commandes)): ?>
          <div class="text-center py-5">
            <i class="bi bi-cart-x fs-1 text-muted"></i>
            <h5 class="mt-3">Aucune commande</h5>
            <p class="text-muted">Aucune commande n'a été passée pour le moment.</p>
          </div>
        <?php else: ?>
          <?php foreach ($commandes as $commande): 
            $details = $details_commandes[$commande['id_commande']] ?? [];
            $nb_livres = count($details);
          ?>
            <div class="commande-card">
              <div class="commande-header">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong>Commande #<?php echo $commande['id_commande']; ?></strong>
                    <span class="badge badge-statut statut-<?php echo strtolower($commande['statut']); ?> ms-2">
                      <?php echo $commande['statut']; ?>
                    </span>
                  </div>
                  <div class="text-muted">
                    <?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?>
                  </div>
                </div>
              </div>
              
              <div class="commande-body">
                <!-- Informations client -->
                <div class="row mb-3">
                  <div class="col-md-6">
                    <h6>Client</h6>
                    <p class="mb-1">
                      <strong><?php echo htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']); ?></strong>
                      <?php if ($commande['role'] === 'admin'): ?>
                        <span class="badge bg-danger ms-1">Admin</span>
                      <?php endif; ?>
                    </p>
                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($commande['email']); ?></p>
                    <small class="text-muted">ID: <?php echo $commande['id_utilisateur']; ?></small>
                  </div>
                  <div class="col-md-6">
                    <h6>Détails de la commande</h6>
                    <div class="d-flex justify-content-between">
                      <span>Total:</span>
                      <strong><?php echo number_format($commande['total'], 0, ',', ' '); ?> FCFA</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                      <span>Nombre de livres:</span>
                      <strong><?php echo $nb_livres; ?></strong>
                    </div>
                  </div>
                </div>
                
                <!-- Liste des livres -->
                <?php if ($nb_livres > 0): ?>
                  <h6>Livres commandés</h6>
                  <?php foreach ($details as $livre): ?>
                    <div class="livre-item">
                      <div>
                        <strong><?php echo htmlspecialchars($livre['titre']); ?></strong>
                        <br>
                        <small class="text-muted">
                          Quantité: <?php echo $livre['quantite']; ?> × 
                          <?php echo number_format($livre['prix_unitaire'], 0, ',', ' '); ?> FCFA
                        </small>
                      </div>
                      <div>
                        <strong><?php echo number_format($livre['quantite'] * $livre['prix_unitaire'], 0, ',', ' '); ?> FCFA</strong>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Aucun détail de livre trouvé pour cette commande
                  </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                  <div>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="action" value="changer_statut">
                      <input type="hidden" name="id_commande" value="<?php echo $commande['id_commande']; ?>">
                      <select name="statut" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                        <option value="en attente" <?php echo $commande['statut'] === 'en attente' ? 'selected' : ''; ?>>En attente</option>
                        <option value="confirmée" <?php echo $commande['statut'] === 'confirmée' ? 'selected' : ''; ?>>Confirmée</option>
                        <option value="expédiée" <?php echo $commande['statut'] === 'expédiée' ? 'selected' : ''; ?>>Expédiée</option>
                        <option value="livrée" <?php echo $commande['statut'] === 'livrée' ? 'selected' : ''; ?>>Livrée</option>
                        <option value="annulée" <?php echo $commande['statut'] === 'annulée' ? 'selected' : ''; ?>>Annulée</option>
                      </select>
                    </form>
                  </div>
                  <div>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="action" value="supprimer_commande">
                      <input type="hidden" name="id_commande" value="<?php echo $commande['id_commande']; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" 
                              onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?')">
                        <i class="bi bi-trash"></i> Supprimer
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script>
    // Auto-dismiss alerts
    setTimeout(function() {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(alert => {
        new bootstrap.Alert(alert).close();
      });
    }, 5000);
  </script>

</body>

</html>