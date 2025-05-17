<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Log des données reçues
    file_put_contents('debug_log.txt', print_r($_POST, true));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération et nettoyage des données
        $nom = trim(strip_tags($_POST['nom']));
        $prenom = trim(strip_tags($_POST['prenom']));
        $email = trim(strip_tags($_POST['email']));
        $password = $_POST['password'];
        
        
        // Vérification des champs vides
        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            throw new Exception('Tous les champs sont obligatoires');
        }

        // Validation de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Format email invalide']);
            exit;
        }
        
        // Log des données reçues
        file_put_contents('debug_log.txt', print_r($_POST, true));

        // Vérification si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        if ($result['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }
        
        // Insertion de l'utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nom, $prenom, $email, $password])) {
            echo json_encode([
                'success' => true,
                'message' => 'Inscription réussie ! Vous pouvez maintenant vous connecter.',
                'redirect' => 'index.html'
            ]);
        } else {
            throw new Exception('Erreur lors de l\'insertion');
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Méthode non autorisée'
        ]);
    }
} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>