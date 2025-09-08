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

// Configuration upload
$upload_dir = "uploads/livres/";
$upload_dir_fichiers = "uploads/livres/fichiers/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
if (!is_dir($upload_dir_fichiers)) {
    mkdir($upload_dir_fichiers, 0777, true);
}

// Fonction utilitaire pour la taille des fichiers
function formatTailleFichier($bytes) {
    if ($bytes == 0) return "0 B";
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_book':
                $titre = $_POST['titre'];
                $auteur = $_POST['auteur'];
                $description = $_POST['description'];
                $prix = $_POST['prix'];
                $est_numerique = isset($_POST['est_numerique']) ? 1 : 0;
                
                // Gestion de l'upload d'image
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
                    $image_target = $upload_dir . $image_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $image_target)) {
                        $image_path = $image_target;
                    }
                }
                
                // Gestion de l'upload de fichier numérique
                $fichier_path = '';
                $taille_fichier = 0;
                if ($est_numerique && isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
                    $fichier_name = uniqid() . '_' . basename($_FILES['fichier']['name']);
                    $fichier_target = $upload_dir_fichiers . $fichier_name;
                    
                    if (move_uploaded_file($_FILES['fichier']['tmp_name'], $fichier_target)) {
                        $fichier_path = $fichier_target;
                        $taille_fichier = $_FILES['fichier']['size'];
                    }
                }
                
                $stmt = $conn->prepare("INSERT INTO produits (titre, auteur, description, prix, image, est_numerique, fichier, taille_fichier, date_ajout) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssdsisi", $titre, $auteur, $description, $prix, $image_path, $est_numerique, $fichier_path, $taille_fichier);
                $stmt->execute();
                break;
                
            case 'edit_book':
                $id = $_POST['id'];
                $titre = $_POST['titre'];
                $auteur = $_POST['auteur'];
                $description = $_POST['description'];
                $prix = $_POST['prix'];
                $est_numerique = isset($_POST['est_numerique']) ? 1 : 0;
                
                // Gestion de l'upload d'image
                $image_path = $_POST['image_actuelle'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
                    $image_target = $upload_dir . $image_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $image_target)) {
                        // Supprimer l'ancienne image si elle existe
                        if (!empty($_POST['image_actuelle']) && file_exists($_POST['image_actuelle'])) {
                            unlink($_POST['image_actuelle']);
                        }
                        $image_path = $image_target;
                    }
                }
                
                // Gestion de l'upload de fichier numérique
                $fichier_path = $_POST['fichier_actuel'] ?? '';
                $taille_fichier = $_POST['taille_fichier_actuelle'] ?? 0;
                
                if ($est_numerique && isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
                    $fichier_name = uniqid() . '_' . basename($_FILES['fichier']['name']);
                    $fichier_target = $upload_dir_fichiers . $fichier_name;
                    
                    if (move_uploaded_file($_FILES['fichier']['tmp_name'], $fichier_target)) {
                        // Supprimer l'ancien fichier si il existe
                        if (!empty($_POST['fichier_actuel']) && file_exists($_POST['fichier_actuel'])) {
                            unlink($_POST['fichier_actuel']);
                        }
                        $fichier_path = $fichier_target;
                        $taille_fichier = $_FILES['fichier']['size'];
                    }
                } elseif (!$est_numerique && !empty($_POST['fichier_actuel'])) {
                    // Si on passe de numérique à physique, supprimer le fichier
                    if (file_exists($_POST['fichier_actuel'])) {
                        unlink($_POST['fichier_actuel']);
                    }
                    $fichier_path = '';
                    $taille_fichier = 0;
                }
                
                $stmt = $conn->prepare("UPDATE produits SET titre = ?, auteur = ?, description = ?, prix = ?, image = ?, est_numerique = ?, fichier = ?, taille_fichier = ? WHERE id = ?");
                $stmt->bind_param("sssdsisii", $titre, $auteur, $description, $prix, $image_path, $est_numerique, $fichier_path, $taille_fichier, $id);
                $stmt->execute();
                break;
                
            case 'delete_book':
                $id = $_POST['id'];
                
                // Récupérer les informations du livre pour suppression des fichiers
                $stmt_select = $conn->prepare("SELECT image, fichier FROM produits WHERE id = ?");
                $stmt_select->bind_param("i", $id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();
                $livre = $result->fetch_assoc();
                
                // Supprimer l'image
                if (!empty($livre['image']) && file_exists($livre['image'])) {
                    unlink($livre['image']);
                }
                
                // Supprimer le fichier numérique
                if (!empty($livre['fichier']) && file_exists($livre['fichier'])) {
                    unlink($livre['fichier']);
                }
                
                $stmt = $conn->prepare("DELETE FROM produits WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
        }
    }
    header("Location: gestion_livres.php");
    exit();
}

// Récupérer tous les livres
$result = $conn->query("SELECT * FROM produits ORDER BY date_ajout DESC");
$livres = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Gestion des Livres - Admin InspiLivres</title>

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
    }
    .book-card {
      transition: transform 0.2s;
    }
    .book-card:hover {
      transform: translateY(-2px);
    }
    .action-buttons .btn {
      margin-right: 5px;
    }
    .modal-content {
      border-radius: 15px;
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
    
    /* Boutons orange */
    .btn-primary {
      background-color: var(--orange-primary);
      border-color: var(--orange-primary);
    }
    .btn-primary:hover {
      background-color: var(--orange-hover);
      border-color: var(--orange-hover);
    }
    .btn-outline-primary {
      color: var(--orange-primary);
      border-color: var(--orange-primary);
    }
    .btn-outline-primary:hover {
      background-color: var(--orange-primary);
      border-color: var(--orange-primary);
      color: white;
    }
    .btn-warning {
      background-color: var(--orange-primary);
      border-color: var(--orange-primary);
      color: white;
    }
    .btn-warning:hover {
      background-color: var(--orange-hover);
      border-color: var(--orange-hover);
      color: white;
    }
    
    /* Badges orange */
    .badge.bg-success {
      background-color: var(--orange-primary) !important;
    }
    
    /* Icônes orange */
    .stats-card .text-primary {
      color: var(--orange-primary) !important;
    }
    .stats-card .text-success {
      color: var(--orange-primary) !important;
    }
    
    /* Prix en FCFA */
    .price-fcfa {
      font-weight: bold;
      color: var(--orange-primary);
    }
    
    /* Preview image */
    .image-preview {
      width: 100%;
      height: 200px;
      border: 2px dashed #ddd;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      margin-bottom: 15px;
    }
    .image-preview img {
      max-width: 100%;
      max-height: 100%;
      object-fit: cover;
    }
    .image-preview-text {
      color: #6c757d;
      text-align: center;
    }
    
    /* Badge type livre */
    .badge-numerique {
      background-color: #0d6efd;
    }
    .badge-physique {
      background-color: #6c757d;
    }
  </style>
</head>

<body class="admin-container">

  <div class="sidebar">
    <div class="text-center py-4">
      <h4 class="mb-0" style="color:#e96308">InspiLivres</h4>
      <small  style="color:#fff">Panel Admin</small>
    </div>
    
    <nav class="nav flex-column mt-4">
      <a class="nav-link active" href="gestion_livres.php">
        <i class="bi bi-book me-2"></i>Gestion des Livres
      </a>
      <a class="nav-link" href="gestion_utilisateurs.php">
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
        <h2 class="mb-0">Gestion des Livres</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
          <i class="bi bi-plus-circle me-2"></i>Ajouter un livre
        </button>
      </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="stats-card text-center">
          <i class="bi bi-book fs-1 text-primary"></i>
          <h3 class="mt-2"><?php echo count($livres); ?></h3>
          <p class="text-muted mb-0">Livres total</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stats-card text-center">
          <i class="bi bi-currency-exchange fs-1 text-success"></i>
          <h3 class="mt-2 price-fcfa">
            <?php 
            $total_value = array_sum(array_column($livres, 'prix'));
            echo number_format($total_value, 0, ',', ' ');
            ?> FCFA
          </h3>
          <p class="text-muted mb-0">Valeur totale</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stats-card text-center">
          <i class="bi bi-file-earmark-text fs-1 text-info"></i>
          <h3 class="mt-2">
            <?php 
            $livres_numeriques = array_filter($livres, function($livre) {
                return isset($livre['est_numerique']) && $livre['est_numerique'] == 1;
            });
            echo count($livres_numeriques);
            ?>
          </h3>
          <p class="text-muted mb-0">Livres numériques</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stats-card text-center">
          <i class="bi bi-clock-history fs-1 text-warning"></i>
          <h3 class="mt-2"><?php echo date('d/m/Y'); ?></h3>
          <p class="text-muted mb-0">Mise à jour</p>
        </div>
      </div>
    </div>

    <!-- Tableau des livres -->
    <div class="card">
      <div class="card-header bg-white">
        <h5 class="mb-0">Liste des livres</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Type</th>
                <th>Prix</th>
                <th>Date d'ajout</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($livres as $livre): ?>
              <tr>
                <td><?php echo $livre['id']; ?></td>
                <td>
                  <?php if (!empty($livre['image']) && file_exists($livre['image'])): ?>
                    <img src="<?php echo htmlspecialchars($livre['image']); ?>" 
                         alt="<?php echo htmlspecialchars($livre['titre']); ?>" 
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                  <?php else: ?>
                    <div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                      <i class="bi bi-image text-muted"></i>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?php echo htmlspecialchars($livre['titre']); ?></strong>
                  <br>
                  <small class="text-muted"><?php echo substr(htmlspecialchars($livre['description']), 0, 50); ?>...</small>
                </td>
                <td><?php echo htmlspecialchars($livre['auteur']); ?></td>
                <td>
                  <?php if (isset($livre['est_numerique']) && $livre['est_numerique'] == 1): ?>
                    <span class="badge badge-numerique">Numérique</span>
                    <?php if (!empty($livre['fichier'])): ?>
                      <br>
                      <small class="text-muted"><?php echo formatTailleFichier($livre['taille_fichier']); ?></small>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="badge badge-physique">Physique</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-success">
                    <?php echo number_format($livre['prix'], 0, ',', ' '); ?> FCFA
                  </span>
                </td>
                <td><?php echo date('d/m/Y', strtotime($livre['date_ajout'])); ?></td>
                <td>
                  <div class="action-buttons">
                    <button class="btn btn-sm btn-warning" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editBookModal"
                            data-id="<?php echo $livre['id']; ?>"
                            data-titre="<?php echo htmlspecialchars($livre['titre']); ?>"
                            data-auteur="<?php echo htmlspecialchars($livre['auteur']); ?>"
                            data-description="<?php echo htmlspecialchars($livre['description']); ?>"
                            data-prix="<?php echo $livre['prix']; ?>"
                            data-image="<?php echo htmlspecialchars($livre['image']); ?>"
                            data-est_numerique="<?php echo isset($livre['est_numerique']) ? $livre['est_numerique'] : 0; ?>"
                            data-fichier="<?php echo htmlspecialchars($livre['fichier'] ?? ''); ?>"
                            data-taille_fichier="<?php echo $livre['taille_fichier'] ?? 0; ?>">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="action" value="delete_book">
                      <input type="hidden" name="id" value="<?php echo $livre['id']; ?>">
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre ?')">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
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

  <!-- Modal Ajouter Livre -->
  <div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ajouter un nouveau livre</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_book">
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Titre *</label>
                  <input type="text" class="form-control" name="titre" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Auteur *</label>
                  <input type="text" class="form-control" name="auteur" required>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Description *</label>
              <textarea class="form-control" name="description" rows="4" required></textarea>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Prix (FCFA) *</label>
                  <input type="number" step="100" class="form-control" name="prix" required 
                         placeholder="Ex: 5000">
                  <small class="text-muted">Prix en Franc CFA</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Image du livre *</label>
                  <input type="file" class="form-control" name="image" accept="image/*" required 
                         onchange="previewImage(this, 'addPreview')">
                  <small class="text-muted">Formats acceptés: JPG, PNG, WEBP</small>
                  
                  <div class="image-preview mt-2" id="addPreview">
                    <div class="image-preview-text">
                      <i class="bi bi-image fs-1"></i>
                      <p>Aperçu de l'image</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="add_est_numerique" name="est_numerique" value="1">
                    <label class="form-check-label" for="add_est_numerique">Livre numérique</label>
                  </div>
                  <small class="text-muted">Cochez cette case si le livre est un eBook téléchargeable</small>
                </div>
              </div>
            </div>
            
            <div id="add-fichier-numerique" style="display: none;">
              <div class="mb-3">
                <label class="form-label">Fichier du livre numérique *</label>
                <input type="file" class="form-control" name="fichier" accept=".pdf,.epub,.mobi">
                <small class="text-muted">Formats acceptés: PDF, EPUB, MOBI (Max: 50MB)</small>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Ajouter le livre</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Modifier Livre -->
  <div class="modal fade" id="editBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Modifier le livre</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit_book">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="image_actuelle" id="edit_image_actuelle">
            <input type="hidden" name="fichier_actuel" id="edit_fichier_actuel">
            <input type="hidden" name="taille_fichier_actuelle" id="edit_taille_fichier_actuelle">
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Titre *</label>
                  <input type="text" class="form-control" name="titre" id="edit_titre" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Auteur *</label>
                  <input type="text" class="form-control" name="auteur" id="edit_auteur" required>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Description *</label>
              <textarea class="form-control" name="description" id="edit_description" rows="4" required></textarea>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Prix (FCFA) *</label>
                  <input type="number" step="100" class="form-control" name="prix" id="edit_prix" required>
                  <small class="text-muted">Prix en Franc CFA</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Image du livre</label>
                  <input type="file" class="form-control" name="image" accept="image/*" 
                         onchange="previewImage(this, 'editPreview')">
                  <small class="text-muted">Laissez vide pour conserver l'image actuelle</small>
                  
                  <div class="image-preview mt-2" id="editPreview">
                    <div class="image-preview-text">
                      <i class="bi bi-image fs-1"></i>
                      <p>Aperçu de l'image</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="edit_est_numerique" name="est_numerique" value="1">
                    <label class="form-check-label" for="edit_est_numerique">Livre numérique</label>
                  </div>
                  <small class="text-muted">Cochez cette case si le livre est un eBook téléchargeable</small>
                </div>
              </div>
            </div>
            
            <div id="edit-fichier-numerique" style="display: none;">
              <div class="mb-3">
                <label class="form-label">Fichier du livre numérique</label>
                <input type="file" class="form-control" name="fichier" accept=".pdf,.epub,.mobi">
                <small class="text-muted">Formats acceptés: PDF, EPUB, MOBI (Max: 50MB)</small>
                
                <div id="edit-fichier-actuel" class="mt-2"></div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Modifier le livre</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script>
    // Gestion du modal d'édition
    const editBookModal = document.getElementById('editBookModal');
    editBookModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const titre = button.getAttribute('data-titre');
      const auteur = button.getAttribute('data-auteur');
      const description = button.getAttribute('data-description');
      const prix = button.getAttribute('data-prix');
      const image = button.getAttribute('data-image');
      const est_numerique = button.getAttribute('data-est_numerique');
      const fichier = button.getAttribute('data-fichier');
      const taille_fichier = button.getAttribute('data-taille_fichier');
      
      document.getElementById('edit_id').value = id;
      document.getElementById('edit_titre').value = titre;
      document.getElementById('edit_auteur').value = auteur;
      document.getElementById('edit_description').value = description;
      document.getElementById('edit_prix').value = prix;
      document.getElementById('edit_image_actuelle').value = image;
      document.getElementById('edit_fichier_actuel').value = fichier;
      document.getElementById('edit_taille_fichier_actuelle').value = taille_fichier;
      
      // Afficher l'image actuelle dans le preview
      const preview = document.getElementById('editPreview');
      if (image) {
        preview.innerHTML = `<img src="${image}" alt="Image actuelle" class="img-fluid">`;
      } else {
        preview.innerHTML = `
          <div class="image-preview-text">
            <i class="bi bi-image fs-1"></i>
            <p>Aucune image</p>
          </div>
        `;
      }
      
      // Gestion du livre numérique
      const estNumeriqueCheckbox = document.getElementById('edit_est_numerique');
      const fichierNumeriqueDiv = document.getElementById('edit-fichier-numerique');
      const fichierActuelDiv = document.getElementById('edit-fichier-actuel');
      
      if (est_numerique == 1) {
        estNumeriqueCheckbox.checked = true;
        fichierNumeriqueDiv.style.display = 'block';
        
        if (fichier) {
          const fileName = fichier.split('/').pop();
          fichierActuelDiv.innerHTML = `
            <small>Fichier actuel: ${fileName} (${formatFileSize(taille_fichier)})</small>
          `;
        } else {
          fichierActuelDiv.innerHTML = '<small>Aucun fichier actuellement</small>';
        }
      } else {
        estNumeriqueCheckbox.checked = false;
        fichierNumeriqueDiv.style.display = 'none';
      }
    });

    // Fonction de preview d'image
    function previewImage(input, previewId) {
      const preview = document.getElementById(previewId);
      const file = input.files[0];
      
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid">`;
        }
        reader.readAsDataURL(file);
      } else {
        preview.innerHTML = `
          <div class="image-preview-text">
            <i class="bi bi-image fs-1"></i>
            <p>Aperçu de l'image</p>
          </div>
        `;
      }
    }
    
    // Fonction pour formater la taille du fichier
    function formatFileSize(bytes) {
      if (bytes == 0) return "0 B";
      const units = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(1024));
      return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + units[i];
    }

    // Messages de confirmation
    document.addEventListener('DOMContentLoaded', function() {
      const forms = document.querySelectorAll('form[method="POST"]');
      forms.forEach(form => {
        form.addEventListener('submit', function(e) {
          if (this.querySelector('input[name="action"]').value === 'delete_book') {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce livre ? Cette action est irréversible.')) {
              e.preventDefault();
            }
          }
        });
      });
      
      // Gestion de l'affichage du champ fichier numérique pour l'ajout
      const addEstNumerique = document.getElementById('add_est_numerique');
      const addFichierNumerique = document.getElementById('add-fichier-numerique');
      
      addEstNumerique.addEventListener('change', function() {
        addFichierNumerique.style.display = this.checked ? 'block' : 'none';
      });
      
      // Gestion de l'affichage du champ fichier numérique pour l'édition
      const editEstNumerique = document.getElementById('edit_est_numerique');
      const editFichierNumerique = document.getElementById('edit-fichier-numerique');
      
      editEstNumerique.addEventListener('change', function() {
        editFichierNumerique.style.display = this.checked ? 'block' : 'none';
      });
    });
  </script>

</body>

</html>