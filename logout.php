<?php
session_start();

// Détruire toutes les variables de session
$_SESSION = [];

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil ou login
header("Location: accueil.php");
exit();
?>
