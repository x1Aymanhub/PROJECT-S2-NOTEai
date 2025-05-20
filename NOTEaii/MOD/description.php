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
    
    // Trier les descriptions par date croissante (ancienne -> récente)
    if (!empty($descriptions)) {
        usort($descriptions, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
    }
    
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
    <link rel="stylesheet" href="modules.css">
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
        
        <!-- Drawer d'ajout de module -->
        <div id="addModuleDrawer" class="top-drawer-modal" style="display:none;">
            <div class="drawer-header d-flex justify-content-between align-items-center px-4 pt-3">
                <h3 class="mb-0">Ajouter un Module</h3>
                <button class="btn-close" onclick="closeDrawer()"></button>
            </div>
            <form class="px-4 pb-4" action="description.php?module_id=<?= $module_id ?>" method="post">
                <div class="mb-3">
                    <label for="module_nom" class="form-label">Nom du module</label>
                    <input type="text" class="form-control" id="module_nom" name="module_nom" required>
                </div>
                <div class="mb-3">
                    <label for="semestre" class="form-label">Semestre</label>
                    <input type="text" class="form-control" id="semestre" name="semestre" required>
                </div>
                <div class="mb-3">
                    <label for="coefficient" class="form-label">Coefficient</label>
                    <input type="number" class="form-control" id="coefficient" name="coefficient" required>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" onclick="closeDrawer()">Annuler</button>
                    <button type="submit" class="btn" style="background-color: #8B4513; color: white;">Enregistrer</button>
                </div>
            </form>
        </div>
        <script>
        function openDrawer() {
            document.getElementById('addModuleDrawer').style.display = 'block';
            setTimeout(() => {
                document.getElementById('addModuleDrawer').classList.add('show');
            }, 10);
        }
        function closeDrawer() {
            document.getElementById('addModuleDrawer').classList.remove('show');
            setTimeout(() => {
                document.getElementById('addModuleDrawer').style.display = 'none';
            }, 400);
        }
        </script>
        
        <!-- Barre d'ajout de description -->
        <div class="card mb-4 shadow-sm w-100" style="border-radius: 16px;">
            <div class="card-body d-flex flex-column flex-md-row align-items-md-center gap-3">
                <form id="desc-form" class="flex-grow-1 d-flex flex-column flex-md-row align-items-md-center gap-3 w-100" action="description.php?module_id=<?= $module_id ?>" method="post">
                    <textarea class="form-control" name="description" id="description-textarea" rows="2" placeholder="Ajouter une nouvelle description..." required style="resize: none; border-radius: 10px; min-height: 60px; font-size: 1.1rem; padding: 18px 20px;"></textarea>
                    <input type="hidden" name="edit_id" id="edit-id" value="">
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 12px; min-width: 160px; height: 60px; font-size: 1.15rem; display: flex; align-items: center; justify-content: center;">Ajouter</button>
                </form>
            </div>
        </div>
        <script>
        // Auto-ajustement de la textarea
        const textarea = document.getElementById('description-textarea');
        const editIdInput = document.getElementById('edit-id');
        const submitBtn = document.getElementById('desc-submit-btn');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 180) + 'px';
        });
        // Gestion édition
        function editDescription(btn) {
            document.getElementById('edit-description-textarea').value = btn.getAttribute('data-description');
            document.getElementById('edit-description-id').value = btn.getAttribute('data-id');
            var modal = new bootstrap.Modal(document.getElementById('editDescriptionModal'));
            modal.show();
        }
        document.getElementById('desc-form').addEventListener('reset', function() {
            editIdInput.value = '';
            submitBtn.textContent = 'Ajouter la description ' + (document.querySelectorAll('.description-card').length + 1);
        });
        // Initialiser le bouton au chargement
        window.addEventListener('DOMContentLoaded', function() {
            submitBtn.textContent = 'Ajouter la description ' + (document.querySelectorAll('.description-card').length + 1);
        });
        document.getElementById('desc-form').addEventListener('submit', function(e) {
            if (editIdInput.value) {
                e.preventDefault();
                submitBtn.disabled = true;
                fetch('modifydescription.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + encodeURIComponent(editIdInput.value) + '&description=' + encodeURIComponent(textarea.value)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur : ' + data.message);
                    }
                })
                .catch(() => alert('Erreur réseau'))
                .finally(() => { submitBtn.disabled = false; });
            }
        });
        </script>
        
        <!-- Liste des descriptions existantes -->
        <div class="card">
            <div class="card-header">
                <h4>Descriptions existantes</h4>
            </div>
            <div class="card-body">
                <?php if (empty($descriptions)): ?>
                    <p class="text-muted">Aucune description n'a encore été ajoutée pour ce module.</p>
                <?php else: ?>
                    <div class="list-group d-flex flex-column align-items-center" style="max-height: 600px; overflow-y: auto; scrollbar-width: thin;">
                        <?php foreach ($descriptions as $i => $description): ?>
                            <div class="description-card position-relative mb-4 animate-fade-in" style="max-width: 700px; width: 100%; background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px 24px 24px 24px;">
                                <div class="position-absolute top-0 end-0 mt-3 me-3 d-flex gap-2">
                                    <button class="btn btn-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" title="Modifier" style="width:36px; height:36px;" onclick="editDescription(this)" data-id="<?= $description['id'] ?>" data-description="<?= htmlspecialchars($description['description_text'], ENT_QUOTES) ?>">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" title="Supprimer" style="width:36px; height:36px;" onclick="confirmDelete(<?= $description['id'] ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button class="btn btn-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" title="Voir plus" style="width:36px; height:36px;" onclick="showDescriptionModal(this)" data-description="<?= htmlspecialchars(json_encode($description['description_text'])) ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="text-center mb-2">
                                    <strong>Description <?= $i+1 ?></strong><br>
                                    <small class="text-muted">Ajoutée le : <?= date('d/m/Y H:i', strtotime($description['created_at'])) ?></small>
                                </div>
                                <div class="text-center">
                                    <p class="mb-0 short-description" style="font-size:1.1rem; color:#333; letter-spacing:0.01em; line-height:1.6; max-height: 60px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= nl2br(htmlspecialchars($description['description_text'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modale Bootstrap pour Voir plus -->
    <div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="descriptionModalLabel">Description complète</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="descriptionModalBody"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modale Bootstrap pour modifier une description -->
    <div class="modal fade" id="editDescriptionModal" tabindex="-1" aria-labelledby="editDescriptionModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editDescriptionModalLabel">Modifier la description</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <textarea class="form-control" id="edit-description-textarea" rows="10" style="resize: vertical; min-height: 250px;"></textarea>
            <input type="hidden" id="edit-description-id">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-primary" id="save-edit-description">Enregistrer</button>
          </div>
        </div>
      </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm('Voulez-vous vraiment supprimer cette description ?')) {
                fetch('delete_description.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'description_id=' + encodeURIComponent(id)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur : ' + data.message);
                    }
                })
                .catch(() => alert('Erreur réseau'));
            }
        }
        function showDescriptionModal(btn) {
            var desc = btn.getAttribute('data-description');
            desc = JSON.parse(desc);
            document.getElementById('descriptionModalBody').innerText = desc;
            var modal = new bootstrap.Modal(document.getElementById('descriptionModal'));
            modal.show();
        }
        document.getElementById('save-edit-description').onclick = function() {
            const id = document.getElementById('edit-description-id').value;
            const text = document.getElementById('edit-description-textarea').value;
            this.disabled = true;
            fetch('modifydescription.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(id) + '&description=' + encodeURIComponent(text)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch(() => alert('Erreur réseau'))
            .finally(() => { this.disabled = false; });
        };
    </script>
    <style>
        .animate-fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.7s cubic-bezier(.23,1.01,.32,1) forwards;
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .description-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
            transform: translateY(-2px) scale(1.01);
            transition: box-shadow 0.3s, transform 0.3s;
        }
    </style>
</body>
</html> 