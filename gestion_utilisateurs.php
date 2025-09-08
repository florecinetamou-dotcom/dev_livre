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
        switch ($_POST['action']) {
            case 'toggle_status':
                $id = intval($_POST['id']);
                $current_status = $conn->query("SELECT actif FROM utilisateur WHERE id_utilisateur = $id")->fetch_assoc()['actif'];
                $new_status = $current_status ? 0 : 1;
                
                $stmt = $conn->prepare("UPDATE utilisateur SET actif = ? WHERE id_utilisateur = ?");
                $stmt->bind_param("ii", $new_status, $id);
                $stmt->execute();
                break;
                
            case 'delete_user':
                $id = intval($_POST['id']);
                
                // Vérifier si l'utilisateur a des commandes
                $check_orders = $conn->query("SELECT COUNT(*) as count FROM commande WHERE id_utilisateur = $id")->fetch_assoc()['count'];
                
                if ($check_orders == 0) {
                    $stmt = $conn->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $_SESSION['message'] = "Utilisateur supprimé avec succès";
                } else {
                    $_SESSION['error'] = "Impossible de supprimer : l'utilisateur a des commandes";
                }
                break;
        }
    }
    header("Location: gestion_utilisateurs.php");
    exit();
}

// Récupérer tous les utilisateurs avec leurs statistiques
$query = "
    SELECT 
        u.id_utilisateur,
        u.nom,
        u.prenom,
        u.email,
        u.date_inscription,
        u.role,
        u.derniere_connexion,
        u.actif,
        COUNT(c.id_commande) as total_commandes,
        MAX(c.date_commande) as derniere_commande,
        (SELECT COUNT(*) FROM activite_utilisateur WHERE id_utilisateur = u.id_utilisateur) as total_activites
    FROM utilisateur u
    LEFT JOIN commande c ON u.id_utilisateur = c.id_utilisateur
    GROUP BY u.id_utilisateur
    ORDER BY u.date_inscription DESC
";
$result = $conn->query($query);
$utilisateurs = $result->fetch_all(MYSQLI_ASSOC);

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
  <title>Gestion des Utilisateurs - Admin InspiLivres</title>

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
      --orange-light: #fff0e6;
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
    .table-responsive {
      background: white;
      border-radius: 10px;
      overflow: hidden;
    }
    .admin-header {
      background: white;
      padding: 15px 20px;
      border-bottom: 1px solid #dee2e6;
    }
    
    /* Statuts */
    .statut-actif {
      background-color: #d4edda;
      color: #155724;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }
    .statut-inactif {
      background-color: #f8d7da;
      color: #721c24;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }
    
    /* Badges */
    .badge-commandes {
      background-color: var(--orange-primary);
      color: white;
    }
    .badge-activite {
      background-color: #6f42c1;
      color: white;
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

    /* Rôle utilisateur */
    .badge-role {
      background-color: #6c757d;
      color: white;
      font-size: 0.75rem;
    }
    .badge-role.admin {
      background-color: #dc3545;
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
      <a class="nav-link active" href="gestion_utilisateurs.php">
        <i class="bi bi-people me-2"></i>Utilisateurs
      </a>
      <a class="nav-link" href="gestion_commandes.php">
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
        <h2 class="mb-0">Gestion des Utilisateurs</h2>
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
          <i class="bi bi-people fs-1 text-primary"></i>
          <h3 class="mt-2"><?php echo count($utilisateurs); ?></h3>
          <p class="text-muted mb-0">Total Utilisateurs</p>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-check-circle fs-1 text-success"></i>
          <h3 class="mt-2">
            <?php 
            $actifs = array_filter($utilisateurs, function($u) { 
                return $u['actif'] == 1; 
            });
            echo count($actifs); 
            ?>
          </h3>
          <p class="text-muted mb-0">Utilisateurs Actifs</p>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-cart fs-1 text-warning"></i>
          <h3 class="mt-2">
            <?php 
            $avec_commandes = array_filter($utilisateurs, function($u) { 
                return $u['total_commandes'] > 0; 
            });
            echo count($avec_commandes); 
            ?>
          </h3>
          <p class="text-muted mb-0">Avec Commandes</p>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card text-center">
          <i class="bi bi-activity fs-1 text-info"></i>
          <h3 class="mt-2">
            <?php 
            $actifs_recemment = array_filter($utilisateurs, function($u) { 
                return $u['total_activites'] > 0; 
            });
            echo count($actifs_recemment); 
            ?>
          </h3>
          <p class="text-muted mb-0">Activités Récentes</p>
        </div>
      </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="card border-0 shadow">
      <div class="card-header bg-dark text-white">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>Liste des Utilisateurs
          </h5>
          <span class="badge bg-warning"><?php echo count($utilisateurs); ?> utilisateurs</span>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead class="table-light">
              <tr>
                <th>#ID</th>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Inscription</th>
                <th>Commandes</th>
                <th>Activité</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($utilisateurs as $user): ?>
                <tr>
                  <td><strong>#<?php echo $user['id_utilisateur']; ?></strong></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" 
                           style="width: 40px; height: 40px;">
                        <i class="bi bi-person text-muted"></i>
                      </div>
                      <div>
                        <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
                        <?php if ($user['derniere_connexion']): ?>
                          <br>
                          <small class="text-muted">
                            Dernière connexion: <?php echo date('d/m/Y H:i', strtotime($user['derniere_connexion'])); ?>
                          </small>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td><?php echo htmlspecialchars($user['email']); ?></td>
                  <td>
                    <span class="badge badge-role <?php echo $user['role'] === 'admin' ? 'admin' : ''; ?>">
                      <?php echo htmlspecialchars($user['role']); ?>
                    </span>
                  </td>
                  <td>
                    <small class="text-muted">
                      <?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?>
                    </small>
                  </td>
                  <td>
                    <span class="badge badge-commandes rounded-pill">
                      <?php echo $user['total_commandes']; ?> commande(s)
                    </span>
                    <?php if ($user['derniere_commande']): ?>
                      <br>
                      <small class="text-muted">
                        Dernière: <?php echo date('d/m/Y', strtotime($user['derniere_commande'])); ?>
                      </small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge badge-activite rounded-pill">
                      <?php echo $user['total_activites']; ?> activité(s)
                    </span>
                  </td>
                  <td>
                    <span class="<?php echo $user['actif'] ? 'statut-actif' : 'statut-inactif'; ?>">
                      <?php echo $user['actif'] ? 'Actif' : 'Inactif'; ?>
                    </span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="id" value="<?php echo $user['id_utilisateur']; ?>">
                        <button type="submit" class="btn btn-outline-<?php echo $user['actif'] ? 'warning' : 'success'; ?>" 
                                title="<?php echo $user['actif'] ? 'Désactiver' : 'Activer'; ?>">
                          <i class="bi bi-power"></i>
                        </button>
                      </form>
                      <?php if ($user['total_commandes'] == 0 && $user['role'] !== 'admin'): ?>
                        <form method="POST" style="display:inline;">
                          <input type="hidden" name="action" value="delete_user">
                          <input type="hidden" name="id" value="<?php echo $user['id_utilisateur']; ?>">
                          <button type="submit" class="btn btn-outline-danger" 
                                  onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')"
                                  title="Supprimer">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="btn btn-outline-secondary disabled" title="Impossible de supprimer">
                          <i class="bi bi-trash"></i>
                        </span>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
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