<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'noteai_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

// Options PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Création de la connexion PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $conn = new PDO($dsn, $username, $password, $options);
} catch(PDOException $e) {
    // En cas d'erreur, on affiche un message et on arrête tout
    die('Erreur de connexion : ' . $e->getMessage());
}
?> 