<?php
session_start();
require_once '../../config/database.php';

// Set cookies for admin preferences
if (!isset($_COOKIE['admin_preferences'])) {
    setcookie('admin_preferences', json_encode([
        'theme' => 'light',
        'last_action' => 'delete_student',
        'timestamp' => time()
    ]), time() + (86400 * 30), "/"); // 30 days expiry
}

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);
$studentId = $data['id'] ?? null;

if (!$studentId) {
    echo json_encode(['success' => false, 'message' => 'ID étudiant manquant']);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $result = $stmt->execute([$studentId]);

    if ($result) {
        // Update last action cookie
        setcookie('admin_preferences', json_encode([
            'theme' => json_decode($_COOKIE['admin_preferences'] ?? '{"theme":"light"}', true)['theme'] ?? 'light',
            'last_action' => 'delete_student',
            'timestamp' => time()
        ]), time() + (86400 * 30), "/");
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 