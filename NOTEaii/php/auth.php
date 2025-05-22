<?php
session_start();
require_once 'db.php';



header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strip_tags($_POST['email']));
    $password = $_POST['password'];
    
    // Vérification des champs vides
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs']);
        exit;
    }
    
    try {
        // Vérification dans la table users
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Si l'utilisateur n'est pas trouvé dans users, chercher dans admin
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $user['role'] = 'admin'; // Définir le rôle admin explicitement
            }
        }
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
            exit;
        }
        
        // Vérification du mot de passe
        if ($password !== $user['password']) {
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
            exit;
        }
        
        // Connexion réussie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nom'] = $user['nom'] ?? '';
        $_SESSION['prenom'] = $user['prenom'] ?? '';
        $_SESSION['email'] = $user['email'];
        if (isset($user['role'])) {
            $_SESSION['role'] = $user['role'];
        } else {
            $_SESSION['role'] = 'student';
        }

        // Check if "Remember me" is set
        if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
            // Set a persistent cookie for remembering the user (e.g., for 30 days)
            $expiry_time = time() + (86400 * 30); // 30 days
            $remember_data = json_encode([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $_SESSION['role']
            ]);
            // In a real application, you would encrypt or sign this data
            setcookie('remember_user', $remember_data, $expiry_time, "/");
        }
        
        // Update cookie with login history
        $preferences = json_decode($_COOKIE['user_preferences'] ?? '{"theme":"light","login_history":[]}', true);
        $preferences['last_action'] = 'login';
        $preferences['timestamp'] = time();
        $preferences['login_history'][] = [
            'timestamp' => time(),
            'user_id' => $user['id'],
            'email' => $email,
            'role' => $_SESSION['role']
        ];
        // Keep only last 10 logins
        if (count($preferences['login_history']) > 10) {
            array_shift($preferences['login_history']);
        }
        setcookie('user_preferences', json_encode($preferences), time() + (86400 * 30), "/");
        
        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie !',
            'redirect' => (isset($user['role']) && $user['role'] === 'admin') ? 'admin/php/student.php' : 'MOD/index.php'
        ]);
        
    } catch (PDOException $e) {
        error_log("Erreur SQL : " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
// Check if user is logged in
function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    return false;
}

// Logout function
function logout() {
    // Remove session variables
    session_unset();
    session_destroy();
    
    header("Location: ../index.html");
    exit();
}

// Require authentication for protected pages
function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: ../index.html");
        exit();
    }
}

// Require admin role
function requireAdmin() {
    requireAuth();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: ../home.html");
        exit();
    }
} 
