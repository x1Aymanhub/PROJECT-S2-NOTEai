<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

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
        echo json_encode(['success' => true, 'message' => 'Description modifiée avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune modification effectuée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
} 