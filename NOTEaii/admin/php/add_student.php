<?php
session_start();
require_once '../../config/database.php';

// Set cookies for admin preferences
if (!isset($_COOKIE['admin_preferences'])) {
    setcookie('admin_preferences', json_encode([
        'theme' => 'light',
        'last_action' => 'add_student',
        'timestamp' => time()
    ]), time() + (86400 * 30), "/"); // 30 days expiry
}

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Vérifier que la connexion est établie
if (!isset($conn) || $conn === null) {
    echo json_encode(['success' => false, 'message' => 'La connexion à la base de données n\'est pas établie']);
    exit();
}

// Récupérer et vérifier les données
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation des données
if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
    exit();
}

try {
    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
        exit();
    }
    
//mot de passe 
    $plain_password = $password;
    
    // Récupérer l'ID de l'admin connecté
    $admin_id = $_SESSION['user_id'] ?? null;
    
    // Insérer le nouvel étudiant
    $stmt = $conn->prepare("
        INSERT INTO users (nom, prenom, email, password, id_admin, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([$nom, $prenom, $email, $plain_password, $admin_id]);
    
    if ($result) {
        // Update last action cookie
        setcookie('admin_preferences', json_encode([
            'theme' => json_decode($_COOKIE['admin_preferences'] ?? '{"theme":"light"}', true)['theme'] ?? 'light',
            'last_action' => 'add_student',
            'timestamp' => time()
        ]), time() + (86400 * 30), "/");
        
        echo json_encode(['success' => true, 'message' => 'Étudiant ajouté avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'étudiant']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} 