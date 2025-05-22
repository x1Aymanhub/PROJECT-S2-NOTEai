<?php
session_start();
require_once '../config/database.php';

// Set cookies for user preferences
if (!isset($_COOKIE['module_preferences'])) {
    setcookie('module_preferences', json_encode([
        'theme' => 'light',
        'last_action' => 'add_module',
        'timestamp' => time(),
        'module_history' => []
    ]), time() + (86400 * 30), "/"); // 30 days expiry
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $nom = trim($_POST['nom'] ?? '');
    $semestre = filter_var($_POST['semestre'] ?? 0, FILTER_VALIDATE_INT);
    $coefficient = filter_var($_POST['coefficient'] ?? 0, FILTER_VALIDATE_FLOAT);
    
    // Validation des données
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom du module est requis.";
    }
    
    if ($semestre <= 0) {
        $errors[] = "Le semestre doit être un nombre positif.";
    }
    
    if ($coefficient <= 0) {
        $errors[] = "Le coefficient doit être un nombre positif.";
    }
    
    // Si pas d'erreurs, insérer le module dans la base de données
    if (empty($errors)) {
        try {
            // Démarrer une transaction
            $conn->beginTransaction();
            
            // Insérer le module
            $stmt_module = $conn->prepare("
                INSERT INTO modules (nom, semestre, coefficient)
                VALUES (?, ?, ?)
            ");
            $stmt_module->execute([$nom, $semestre, $coefficient]);
            
            // Récupérer l'ID du module inséré
            $module_id = $conn->lastInsertId();
            
            // Associer le module à l'utilisateur
            $stmt_user_module = $conn->prepare("
                INSERT INTO user_modules (user_id, module_id)
                VALUES (?, ?)
            ");
            $stmt_user_module->execute([$user_id, $module_id]);
            
            // Valider la transaction
            $conn->commit();
            
            // Update cookie with module history
            $preferences = json_decode($_COOKIE['module_preferences'] ?? '{"theme":"light","module_history":[]}', true);
            $preferences['last_action'] = 'add_module';
            $preferences['timestamp'] = time();
            $preferences['module_history'][] = [
                'timestamp' => time(),
                'module_id' => $module_id,
                'module_name' => $nom,
                'semestre' => $semestre,
                'coefficient' => $coefficient
            ];
            // Keep only last 20 module creations
            if (count($preferences['module_history']) > 20) {
                array_shift($preferences['module_history']);
            }
            setcookie('module_preferences', json_encode($preferences), time() + (86400 * 30), "/");
            
            $response['success'] = true;
            $response['message'] = "Le module a été ajouté avec succès.";
            
        } catch(PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $conn->rollBack();
            $response['message'] = "Erreur lors de l'ajout du module: " . $e->getMessage();
        }
    } else {
        $response['message'] = implode("<br>", $errors);
    }
}

// Rediriger vers la page des modules avec un message
header("Location: index.php?success=" . ($response['success'] ? '1' : '0') . "&message=" . urlencode($response['message']));
exit;
?> 