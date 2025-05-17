<?php

$host = 'localhost';     
$db   = 'noteai_db';         
$user = 'root';          
$pass = '';              

// Tentative de connexion
try {
    // DSN = Data Source Name
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    // Pour voir les erreurs sous forme d’exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) {
    exit("Erreur de connexion : " . $e->getMessage());
}


// Function to sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to get database connection
function connectDB() {
    global $pdo;
    return $pdo;
}

// Function to check if user exists
function userExists($email) {
    try {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        error_log("Error checking user existence: " . $e->getMessage());
        return false;
    }
}

// Function to log database errors
function logDatabaseError($error, $query = '') {
    $errorMessage = date('Y-m-d H:i:s') . " - Error: " . $error . "\n";
    if ($query) {
        $errorMessage .= "Query: " . $query . "\n";
    }
    error_log($errorMessage, 3, "../logs/db_errors.log");
}