<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Utilisateur non connecté'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn->beginTransaction(); // Démarrer une transaction

try {
    error_log("[save_module.php] Received POST data: " . print_r($_POST, true)); // LOG 1

    // Vérifier si les données sont reçues
    if (empty($_POST)) {
        throw new Exception('Aucune donnée reçue');
    }

    // Récupérer et nettoyer les données du module
    $id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : null;
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $semestre = isset($_POST['semestre']) ? intval($_POST['semestre']) : 0;
    $coefficient = isset($_POST['coefficient']) ? floatval($_POST['coefficient']) : 1.0;
    
    // Récupérer les descriptions (devrait être un tableau envoyé par le formulaire)
    // Nom du champ attendu : descriptions[]
    $descriptions = isset($_POST['descriptions']) && is_array($_POST['descriptions']) ? $_POST['descriptions'] : [];
    // Nettoyer chaque description
    $descriptions = array_map('trim', $descriptions);
    // Filtrer les descriptions vides
    $descriptions = array_filter($descriptions, function($desc) { return !empty($desc); });
    error_log("[save_module.php] Filtered descriptions: " . print_r($descriptions, true)); // LOG 2

    // Validation des données
    if (empty($code)) throw new Exception('Le code du module est requis');
    if (empty($nom)) throw new Exception('Le nom du module est requis');
    if ($semestre < 1 || $semestre > 6) throw new Exception('Le semestre doit être entre 1 et 6');
    if ($coefficient < 0.5 || $coefficient > 5) throw new Exception('Le coefficient doit être entre 0.5 et 5');

    // Vérifier si le code existe déjà (sauf pour la mise à jour du module actuel)
    $stmt_check_code = $conn->prepare("SELECT id FROM modules WHERE code = ? AND (? IS NULL OR id != ?)");
    $stmt_check_code->execute([$code, $id, $id]);
    if ($stmt_check_code->fetch()) {
        throw new Exception('Un module avec ce code existe déjà');
    }

    $current_module_id = $id;

    if ($id) {
        // ---- MISE À JOUR ----
        error_log("[save_module.php] Updating module ID: " . $id); // LOG 3a
        $stmt_update = $conn->prepare("UPDATE modules SET code = ?, nom = ?, semestre = ?, coefficient = ? WHERE id = ?");
        $success = $stmt_update->execute([$code, $nom, $semestre, $coefficient, $id]);
        $message = 'Module mis à jour avec succès';

        if (!$success) {
            throw new Exception('Erreur lors de la mise à jour du module');
        }
        
        // Gérer les descriptions (Supprimer les anciennes, insérer les nouvelles)
        $stmt_delete_desc = $conn->prepare("DELETE FROM module_descriptions WHERE module_id = ?");
        $stmt_delete_desc->execute([$id]);
    } else {
        // ---- INSERTION ----
        $stmt_insert = $conn->prepare("INSERT INTO modules (code, nom, semestre, coefficient) VALUES (?, ?, ?, ?)");
        $success = $stmt_insert->execute([$code, $nom, $semestre, $coefficient]);
        $message = 'Module ajouté avec succès';
        
        if (!$success) {
            throw new Exception('Erreur lors de l\'insertion du module');
        }
        $current_module_id = $conn->lastInsertId(); // Récupérer l'ID du nouveau module
        
        // Associer le module à l'utilisateur
        $stmt_user_module = $conn->prepare("INSERT INTO user_modules (user_id, module_id) VALUES (?, ?)");
        $success_association = $stmt_user_module->execute([$user_id, $current_module_id]);
        
        if (!$success_association) {
            throw new Exception('Erreur lors de l\'association du module à l\'utilisateur');
        }

        error_log("[save_module.php] Inserted new module. ID: " . $current_module_id); // LOG 3b
        if (!$current_module_id) {
             error_log("[save_module.php] ERROR: lastInsertId() returned invalid ID.");
             throw new Exception('Impossible de récupérer l\'ID du nouveau module après insertion.');
        }
    }

    // Insérer les nouvelles descriptions (pour l'ajout et la mise à jour)
    if ($current_module_id && !empty($descriptions)) {
        error_log("[save_module.php] Inserting descriptions for module ID: " . $current_module_id); // LOG 4
        $stmt_insert_desc = $conn->prepare("INSERT INTO module_descriptions (module_id, description_text) VALUES (?, ?)");
        foreach ($descriptions as $desc_text) {
            if (!empty($desc_text)) { // Double vérification
                error_log("[save_module.php] Attempting to insert description: " . substr($desc_text, 0, 50) . "..."); // LOG 5
                $insert_desc_success = $stmt_insert_desc->execute([$current_module_id, $desc_text]);
                if (!$insert_desc_success) {
                    error_log("[save_module.php] ERROR inserting description for module ID {$current_module_id}. PDO Error: " . print_r($stmt_insert_desc->errorInfo(), true)); // LOG 6
                    throw new Exception("Erreur lors de l'insertion d'une description. Vérifiez les logs.");
                }
            }
        }
    }

    $conn->commit(); // Valider la transaction
    error_log("[save_module.php] Transaction committed successfully for module ID: " . $current_module_id);

    echo json_encode([
        'success' => true,
        'message' => $message,
        'moduleId' => $current_module_id
    ]);

} catch(Exception $e) {
    $conn->rollBack(); // Annuler la transaction en cas d'erreur
    // Log plus détaillé de l'erreur
    error_log("!!! Erreur dans save_module.php : " . $e->getMessage() . " - Module ID (si connu): " . ($current_module_id ?? 'N/A') . " - Trace: " . $e->getTraceAsString()); 
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage() // Renvoyer le message d'erreur exact
    ]);
}
?> 