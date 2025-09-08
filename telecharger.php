<?php
// telecharger.php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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

// Récupérer le token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token manquant.");
}

// Vérifier le token et les droits de téléchargement
$stmt = $conn->prepare("
    SELECT t.*, p.fichier, p.taille_fichier, p.titre, c.statut 
    FROM telechargements t
    JOIN produits p ON t.id_produit = p.id
    JOIN commande c ON t.id_commande = c.id_commande
    WHERE t.token = ? AND t.id_utilisateur = ? AND t.date_expiration > NOW() AND c.statut = 'paye'
");
$stmt->bind_param("si", $token, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$telechargement = $result->fetch_assoc();

if (!$telechargement) {
    die("Lien de téléchargement invalide, expiré ou non payé.");
}

// Vérifier que le fichier existe
if (!file_exists($telechargement['fichier'])) {
    die("Fichier non trouvé.");
}

// Incrémenter le compteur de téléchargements
$conn->query("UPDATE telechargements SET nombre_telechargements = nombre_telechargements + 1 WHERE id_telechargement = " . $telechargement['id_telechargement']);

// Télécharger le fichier
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($telechargement['titre']) . '.' . pathinfo($telechargement['fichier'], PATHINFO_EXTENSION) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $telechargement['taille_fichier']);
readfile($telechargement['fichier']);
exit;