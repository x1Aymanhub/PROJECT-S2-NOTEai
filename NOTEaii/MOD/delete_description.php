<?php
session_start();
require_once '../php/db.php';

// Set cookies for user preferences
if (!isset($_COOKIE['module_preferences'])) {
    setcookie('module_preferences', json_encode([
        'theme' => 'light',
        'last_action' => 'delete_description',
        'timestamp' => time(),
        'deleted_descriptions' => []
    ]), time() + (86400 * 30), "/"); // 30 days expiry
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if (!isset($_POST['description_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$user_id = $_SESSION['user_id'];
$description_id = intval($_POST['description_id']);

try {
    $conn = connectDB();
    // Correction : suppression sans user_id
    $sql = "DELETE FROM module_descriptions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$description_id]);
    if ($stmt->rowCount() > 0) {
        // Update cookie with deletion history
        $preferences = json_decode($_COOKIE['module_preferences'] ?? '{"theme":"light","deleted_descriptions":[]}', true);
        $preferences['last_action'] = 'delete_description';
        $preferences['timestamp'] = time();
        $preferences['deleted_descriptions'][] = [
            'timestamp' => time(),
            'description_id' => $description_id
        ];
        // Keep only last 50 deletions
        if (count($preferences['deleted_descriptions']) > 50) {
            array_shift($preferences['deleted_descriptions']);
        }
        setcookie('module_preferences', json_encode($preferences), time() + (86400 * 30), "/");
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Suppression impossible']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
} 