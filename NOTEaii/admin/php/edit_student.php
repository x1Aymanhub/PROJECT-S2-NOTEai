<?php
session_start();
require_once '../../config/database.php';

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID étudiant manquant']);
    exit();
}

try {
    $updateFields = [];
    $params = [];

    // Vérifier chaque champ possible
    if (isset($data['nom']) && !empty($data['nom'])) {
        $updateFields[] = "nom = ?";
        $params[] = $data['nom'];
    }
    if (isset($data['prenom']) && !empty($data['prenom'])) {
        $updateFields[] = "prenom = ?";
        $params[] = $data['prenom'];
    }
    if (isset($data['email']) && !empty($data['email'])) {
        $updateFields[] = "email = ?";
        $params[] = $data['email'];
    }
    if (isset($data['password']) && !empty($data['password'])) {
        $updateFields[] = "password = ?";
        $params[] = $data['password'];
    }

    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'message' => 'Aucune donnée à mettre à jour']);
        exit();
    }

    // Ajouter l'ID à la fin des paramètres
    $params[] = $data['id'];

    $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 