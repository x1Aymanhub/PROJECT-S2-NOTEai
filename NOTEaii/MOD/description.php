<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit;
}

// Vérifier si l'ID du module est fourni
if (!isset($_GET['module_id']) || !is_numeric($_GET['module_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$module_id = $_GET['module_id'];

// Vérifier que le module appartient bien à l'utilisateur
$stmt_check = $conn->prepare("
    SELECT COUNT(*) FROM user_modules 
    WHERE user_id = ? AND module_id = ?
");
$stmt_check->execute([$user_id, $module_id]);
if ($stmt_check->fetchColumn() == 0) {
    header("Location: index.php");
    exit;
}

// Récupérer les informations du module
$module = null;
$descriptions = [];
$error = null;

try {
    // Informations du module
    $stmt_module = $conn->prepare("
        SELECT id, nom, semestre, coefficient 
        FROM modules 
        WHERE id = ?
    ");
    $stmt_module->execute([$module_id]);
    $module = $stmt_module->fetch(PDO::FETCH_ASSOC);
    
    if (!$module) {
        header("Location: index.php");
        exit;
    }
    
    // Récupérer les descriptions existantes (si elles existent)
    $stmt_descriptions = $conn->prepare("
        SELECT id, description_text, created_at 
        FROM module_descriptions 
        WHERE module_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt_descriptions->execute([$module_id]);
    $descriptions = $stmt_descriptions->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = $e->getMessage();
}

// Traitement de l'ajout d'une description
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
    $description_text = trim($_POST['description']);
    
    if (!empty($description_text)) {
        try {
            $stmt_insert = $conn->prepare("
                INSERT INTO module_descriptions (module_id, description_text, created_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt_insert->execute([$module_id, $description_text]);
            
            // Redirection pour éviter les soumissions multiples
            header("Location: description.php?module_id=$module_id&success=1");
            exit;
            
        } catch(PDOException $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descriptions du Module - NOTEAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="module.css">
    <style>
        .description-item {
            height: 150px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .description-item.expanded {
            height: auto;
            overflow: visible;
        }
        .description-item:not(.expanded)::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50px;
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
        }
        .description-item .expand-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Descriptions du Module</h1>
            <div>
                <a href="../ai/ai.php?module_id=<?= $module_id ?>" class="btn me-2" style="background-color: #8B4513; color: white;">
                    <i class="fas fa-robot"></i> Générer QCM Interactif avec AI
                </a>
                <a href="index.php" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Retour aux modules
                </a>
                <a href="../php/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">La description a été ajoutée avec succès.</div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h3><?= htmlspecialchars($module['nom']) ?></h3>
                <p class="mb-0">
                    Semestre: <?= htmlspecialchars($module['semestre']) ?> | 
                    Coefficient: <?= htmlspecialchars($module['coefficient']) ?>
                </p>
            </div>
        </div>
        
        <!-- Formulaire pour ajouter une description -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Ajouter une nouvelle description</h4>
            </div>
            <div class="card-body">
                <form action="description.php?module_id=<?= $module_id ?>" method="post">
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer la description</button>
                </form>
            </div>
        </div>
        
        <!-- Liste des descriptions existantes -->
        <div class="card">
            <div class="card-header">
                <h4>Descriptions existantes</h4>
            </div>
            <div class="card-body">
                <?php if (empty($descriptions)): ?>
                    <p class="text-muted">Aucune description n'a encore été ajoutée pour ce module.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($descriptions as $description): ?>
                            <div class="list-group-item description-item" onclick="toggleDescription(this)">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-muted">
                                        Ajoutée le: <?= date('d/m/Y H:i', strtotime($description['created_at'])) ?>
                                    </small>
                                    <button class="btn btn-danger btn-sm ms-2" onclick="event.stopPropagation(); deleteDescription(<?= $description['id'] ?>)">Supprimer</button>
                                </div>
                                <div class="description-content">
                                    <p class="mb-1 mt-2"><?= nl2br(htmlspecialchars($description['description_text'])) ?></p>
                                </div>
                                <button class="btn btn-sm btn-primary expand-btn">Voir plus</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleDescription(element) {
            element.classList.toggle('expanded');
            const btn = element.querySelector('.expand-btn');
            if (element.classList.contains('expanded')) {
                btn.textContent = 'Voir moins';
            } else {
                btn.textContent = 'Voir plus';
            }
        }
        
        // Empêcher que le clic sur le bouton propage l'événement à l'élément parent
        document.querySelectorAll('.expand-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDescription(this.parentNode);
            });
        });

        function deleteDescription(id) {
            if (!confirm("Voulez-vous vraiment supprimer cette description ?")) return;
            fetch('delete_description.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'description_id=' + encodeURIComponent(id)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Description supprimée !');
                    location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch(() => alert('Erreur réseau'));
        }
    </script>
</body>
</html> 