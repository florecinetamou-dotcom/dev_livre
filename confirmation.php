<?php
session_start();

if (!isset($_SESSION['numero_commande'])) {
    header("Location: accueil.php");
    exit();
}

$numero_commande = $_SESSION['numero_commande'];
unset($_SESSION['numero_commande']);
// Vider le panier après confirmation
unset($_SESSION['panier']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de commande - InspiLivres</title>
    <!-- Inclure ici vos styles -->
</head>
<body>
    <div class="container">
        <div class="confirmation-message text-center py-5">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            <h2 class="mt-3">Commande confirmée!</h2>
            <p class="lead">Votre commande a été enregistrée avec succès.</p>
            <p>Numéro de commande: <strong><?php echo $numero_commande; ?></strong></p>
            <p>Vous recevrez un email de confirmation sous peu.</p>
            <a href="accueil.php" class="btn btn-primary mt-3">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>