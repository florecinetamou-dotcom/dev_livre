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

// Récupérer les statistiques générales
$stats = [];

// Nombre total de commandes
$result = $conn->query("SELECT COUNT(*) as total FROM commande");
$stats['total_commandes'] = $result->fetch_assoc()['total'];

// Chiffre d'affaires total
$result = $conn->query("SELECT SUM(total) as total FROM commande WHERE statut != 'annulée'");
$stats['chiffre_affaires'] = $result->fetch_assoc()['total'] ?? 0;

// Nombre de clients
$result = $conn->query("SELECT COUNT(DISTINCT id_utilisateur) as total FROM commande");
$stats['clients_uniques'] = $result->fetch_assoc()['total'];

// Commandes par statut
$result = $conn->query("SELECT statut, COUNT(*) as count FROM commande GROUP BY statut");
$stats['par_statut'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['par_statut'][$row['statut']] = $row['count'];
}

// Produits les plus vendus
$result = $conn->query("
    SELECT p.titre, SUM(lc.quantite) as total_vendu, p.prix
    FROM ligne_commande lc
    INNER JOIN produits p ON lc.id_produit = p.id
    GROUP BY lc.id_produit
    ORDER BY total_vendu DESC
    LIMIT 10
");
$stats['produits_populaires'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['produits_populaires'][] = $row;
}

// Commandes par mois (pour le graphique)
$result = $conn->query("
    SELECT 
        YEAR(date_commande) as annee, 
        MONTH(date_commande) as mois, 
        COUNT(*) as nb_commandes,
        SUM(total) as chiffre_mois
    FROM commande 
    WHERE date_commande >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(date_commande), MONTH(date_commande)
    ORDER BY annee, mois
");
$stats['par_mois'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['par_mois'][] = $row;
}

// Meilleurs clients
$result = $conn->query("
    SELECT 
        u.nom, 
        u.prenom, 
        COUNT(c.id_commande) as nb_commandes, 
        SUM(c.total) as total_depense
    FROM commande c
    INNER JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
    GROUP BY c.id_utilisateur
    ORDER BY total_depense DESC
    LIMIT 5
");
$stats['meilleurs_clients'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['meilleurs_clients'][] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Statistiques - Admin InspiLivres</title>

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
      height: 100%;
    }
    .stats-card:hover {
      transform: translateY(-2px);
    }
    .admin-header {
      background: white;
      padding: 15px 20px;
      border-bottom: 1px solid #dee2e6;
    }
    .chart-container {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .stat-number {
      font-size: 2rem;
      font-weight: bold;
      color: var(--orange-primary);
    }
    .stat-label {
      color: #6c757d;
      font-size: 0.9rem;
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
      <a class="nav-link" href="gestion_commandes.php">
        <i class="bi bi-cart me-2"></i>Commandes
      </a>
      <a class="nav-link active" href="statistiques.php">
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
        <h2 class="mb-0">Statistiques</h2>
        <span class="text-muted"><?php echo date('d/m/Y'); ?></span>
      </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-cart fs-1 text-primary"></i>
          <div class="stat-number"><?php echo $stats['total_commandes']; ?></div>
          <p class="stat-label">Commandes Total</p>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-currency-exchange fs-1 text-success"></i>
          <div class="stat-number"><?php echo number_format($stats['chiffre_affaires'], 0, ',', ' '); ?> FCFA</div>
          <p class="stat-label">Chiffre d'Affaires</p>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-people fs-1 text-info"></i>
          <div class="stat-number"><?php echo $stats['clients_uniques']; ?></div>
          <p class="stat-label">Clients Uniques</p>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-cart-check fs-1 text-warning"></i>
          <div class="stat-number"><?php echo $stats['par_statut']['livrée'] ?? 0; ?></div>
          <p class="stat-label">Commandes Livrées</p>
        </div>
      </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-4">
      <div class="col-lg-8">
        <div class="chart-container">
          <h5 class="mb-3">Commandes des 6 derniers mois</h5>
          <canvas id="commandesChart"></canvas>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="chart-container">
          <h5 class="mb-3">Répartition des statuts</h5>
          <canvas id="statutsChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Produits populaires -->
    <div class="row mb-4">
      <div class="col-lg-6">
        <div class="chart-container">
          <h5 class="mb-3">Top 10 des livres les plus vendus</h5>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Livre</th>
                  <th>Quantité vendue</th>
                  <th>Chiffre d'affaires</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($stats['produits_populaires'] as $produit): ?>
                <tr>
                  <td><?php echo htmlspecialchars($produit['titre']); ?></td>
                  <td><?php echo $produit['total_vendu']; ?></td>
                  <td><?php echo number_format($produit['total_vendu'] * $produit['prix'], 0, ',', ' '); ?> FCFA</td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="chart-container">
          <h5 class="mb-3">Top 5 des clients</h5>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Client</th>
                  <th>Commandes</th>
                  <th>Total dépensé</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($stats['meilleurs_clients'] as $client): ?>
                <tr>
                  <td><?php echo htmlspecialchars($client['prenom'] . ' ' . $client['nom']); ?></td>
                  <td><?php echo $client['nb_commandes']; ?></td>
                  <td><?php echo number_format($client['total_depense'], 0, ',', ' '); ?> FCFA</td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script>
    // Graphique des commandes par mois
    const commandesCtx = document.getElementById('commandesChart').getContext('2d');
    const commandesChart = new Chart(commandesCtx, {
      type: 'line',
      data: {
        labels: [
          <?php 
          $labels = [];
          foreach ($stats['par_mois'] as $mois) {
            $labels[] = "'" . $mois['mois'] . '/' . $mois['annee'] . "'";
          }
          echo implode(', ', $labels);
          ?>
        ],
        datasets: [{
          label: 'Nombre de commandes',
          data: [<?php echo implode(', ', array_column($stats['par_mois'], 'nb_commandes')); ?>],
          borderColor: '#e96308',
          backgroundColor: 'rgba(233, 99, 8, 0.1)',
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          }
        }
      }
    });

    // Graphique des statuts
    const statutsCtx = document.getElementById('statutsChart').getContext('2d');
    const statutsChart = new Chart(statutsCtx, {
      type: 'doughnut',
      data: {
        labels: [<?php echo "'" . implode("','", array_keys($stats['par_statut'])) . "'"; ?>],
        datasets: [{
          data: [<?php echo implode(', ', array_values($stats['par_statut'])); ?>],
          backgroundColor: [
            '#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff'
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom',
          }
        }
      }
    });
  </script>

</body>

</html>