<?php
session_start();
require_once '../php/db.php';

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
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Suppression impossible']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
} 