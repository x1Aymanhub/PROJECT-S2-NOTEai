<?php
session_start();

// Clear user preferences cookie
if (isset($_COOKIE['user_preferences'])) {
    setcookie('user_preferences', '', time() - 3600, '/');
}

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header('Location: ../index.html');
exit();
?> 