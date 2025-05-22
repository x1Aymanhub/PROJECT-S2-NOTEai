<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

// Set cookies for user preferences
if (!isset($_COOKIE['module_preferences'])) {
    setcookie('module_preferences', json_encode([
        'theme' => 'light',
        'last_action' => 'modify_description',
        'timestamp' => time(),
        'modified_descriptions' => []
    ]), time() + (86400 * 30), "/"); // 30 days expiry
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if (!isset($_POST['id']) || !isset($_POST['description'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$id = intval($_POST['id']);
$description = trim($_POST['description']);

try {
    $stmt = $conn->prepare('UPDATE module_descriptions SET description_text = ? WHERE id = ?');
    $stmt->execute([$description, $id]);
    if ($stmt->rowCount() > 0) {
        // Update cookie with modification history
        $preferences = json_decode($_COOKIE['module_preferences'] ?? '{"theme":"light","modified_descriptions":[]}', true);
        $preferences['last_action'] = 'modify_description';
        $preferences['timestamp'] = time();
        $preferences['modified_descriptions'][] = [
            'timestamp' => time(),
            'description_id' => $id,
            'old_content' => $description // Store the modified content
        ];
        // Keep only last 50 modifications
        if (count($preferences['modified_descriptions']) > 50) {
            array_shift($preferences['modified_descriptions']);
        }
        setcookie('module_preferences', json_encode($preferences), time() + (86400 * 30), "/");
        
        echo json_encode(['success' => true, 'message' => 'Description modifiée avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune modification effectuée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
} 