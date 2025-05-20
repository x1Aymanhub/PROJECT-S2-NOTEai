<?php
session_start();
header('Content-Type: application/json');
require_once '../php/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$module_id = isset($_GET['module_id']) ? intval($_GET['module_id']) : null;

try {
    $conn = connectDB();
    if ($module_id) {
        $sql = 'SELECT d.id, m.nom AS title, d.description_text AS content, d.created_at AS date
                FROM module_descriptions d
                JOIN modules m ON d.module_id = m.id
                WHERE d.module_id = ? AND EXISTS (
                    SELECT 1 FROM user_modules um WHERE um.user_id = ? AND um.module_id = d.module_id
                )
                ORDER BY d.created_at DESC';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$module_id, $user_id]);
    } else {
        $sql = 'SELECT d.id, m.nom AS title, d.description_text AS content, d.created_at AS date
                FROM module_descriptions d
                JOIN modules m ON d.module_id = m.id
                JOIN user_modules um ON um.module_id = m.id
                WHERE um.user_id = ?
                ORDER BY d.created_at DESC';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
    }
    $descriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($descriptions);
} catch (PDOException $e) {
    echo json_encode([]);
} 