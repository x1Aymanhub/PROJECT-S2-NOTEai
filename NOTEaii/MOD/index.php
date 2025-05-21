<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les modules depuis la base de données (sans les descriptions)
$modules = [];
$error = null;
try {
    // Récupérer uniquement les modules de l'utilisateur
    $stmt_modules = $conn->prepare("
        SELECT m.id, m.nom, m.semestre, m.coefficient 
        FROM modules m
        INNER JOIN user_modules um ON m.id = um.module_id
        WHERE um.user_id = ?
        ORDER BY m.semestre, m.nom
    ");
    $stmt_modules->execute([$user_id]);
    $modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Modules - NOTEAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="moduless.css">
   
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des Modules</h1>
            <div>
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#moduleModal">
                    <i class="fas fa-plus"></i> Ajouter un Module
                </button>
                <a href="../php/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>

        <!-- Affichage des messages -->
        <?php if (isset($_GET['success']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?= $_GET['success'] == '1' ? 'success' : 'danger' ?>">
                <?= htmlspecialchars(urldecode($_GET['message'])) ?>
            </div>
        <?php endif; ?>

        <!-- Affichage des modules -->
        <div class="row">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (empty($modules)): ?>
                <div class="col-12">
                    <div class="alert alert-info">Aucun module n'est disponible actuellement.</div>
                </div>
            <?php else: ?>
                <?php foreach ($modules as $module): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($module['nom']) ?></h5>
                                <p class="card-text">
                                    Semestre: <?= htmlspecialchars($module['semestre']) ?><br>
                                    Coefficient: <?= htmlspecialchars($module['coefficient']) ?>
                                </p>
                                <a href="description.php?module_id=<?= $module['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-info-circle"></i> Ajouter des descriptions
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour ajouter un module -->
    <div class="modal fade" id="moduleModal" tabindex="-1" aria-labelledby="moduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="moduleModalLabel">Ajouter un nouveau module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="moduleForm" action="add_module.php" method="post">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom du module</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="semestre" class="form-label">Semestre</label>
                            <input type="number" class="form-control" id="semestre" name="semestre" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="coefficient" class="form-label">Coefficient</label>
                            <input type="number" class="form-control" id="coefficient" name="coefficient" step="0.5" min="0.5" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/module.js"></script>
</body>
</html> 