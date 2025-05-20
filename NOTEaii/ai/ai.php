<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$module_id = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0;

// Vérifier si un module_id valide est fourni
if ($module_id <= 0) {
    header("Location: ../MOD/index.php");
    exit;
}

// Vérifier que le module appartient à l'utilisateur
$stmt_check = $conn->prepare("
    SELECT COUNT(*) FROM user_modules 
    WHERE user_id = ? AND module_id = ?
");
$stmt_check->execute([$user_id, $module_id]);
if ($stmt_check->fetchColumn() == 0) {
    header("Location: ../MOD/index.php");
    exit;
}

// Récupérer les informations du module
$stmt_module = $conn->prepare("
    SELECT nom, semestre, coefficient FROM modules WHERE id = ?
");
$stmt_module->execute([$module_id]);
$module = $stmt_module->fetch(PDO::FETCH_ASSOC);

// Récupérer les descriptions du module
$stmt_descriptions = $conn->prepare("
    SELECT id, description_text, created_at 
    FROM module_descriptions 
    WHERE module_id = ? 
    ORDER BY created_at DESC
");
$stmt_descriptions->execute([$module_id]);
$descriptions = $stmt_descriptions->fetchAll(PDO::FETCH_ASSOC);

// Configuration de l'API
$API_KEY = "AIzaSyCccJymc412DUyuf7tXEDr-0LSIUgLJJaQ";
$API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=GEMINI_API_KEY";

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Génération de QCM avec IA - NOTEAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../MOD/module.css">
    <link rel="stylesheet" href="ai.css">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Génération de QCM par IA - <?= htmlspecialchars($module['nom']) ?></h1>
            <div>
                <a href="../MOD/description.php?module_id=<?= $module_id ?>" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Retour au module
                </a>
                <a href="../php/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>

        <div class="row chat-container">
            <!-- Sidebar avec les descriptions -->
            <div class="col-md-4 chat-sidebar">
                <h4>Descriptions disponibles</h4>
                <p class="text-muted mb-3">Sélectionnez les descriptions pour générer un QCM</p>
                
                <!-- Barre de recherche -->
                <div class="input-group mb-3">
                    <span class="input-group-text" id="search-addon"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="search-descriptions" placeholder="Rechercher..." aria-label="Rechercher" aria-describedby="search-addon">
                </div>
                
                <!-- Système d'onglets pour catégoriser les descriptions -->
                <ul class="nav nav-tabs mb-3" id="descriptionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-descriptions" type="button" role="tab" aria-selected="true">Toutes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="selected-tab" data-bs-toggle="tab" data-bs-target="#selected-descriptions" type="button" role="tab" aria-selected="false">Sélectionnées</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent-descriptions" type="button" role="tab" aria-selected="false">Récentes</button>
                    </li>
                </ul>
                
                <!-- Contenu des onglets -->
                <div class="tab-content" id="descriptionTabsContent">
                    <!-- Toutes les descriptions -->
                    <div class="tab-pane fade show active" id="all-descriptions" role="tabpanel" aria-labelledby="all-tab">
                        <div class="descriptions-pagination mb-2">
                            <span id="pagination-info">Affichage 1-10 sur <?= count($descriptions) ?></span>
                            <div class="btn-group btn-group-sm">
                                <button id="prev-page" class="btn btn-outline-secondary" disabled><i class="fas fa-chevron-left"></i></button>
                                <button id="next-page" class="btn btn-outline-secondary" <?= count($descriptions) <= 10 ? 'disabled' : '' ?>><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        
                        <div id="descriptions-list">
                            <?php if (empty($descriptions)): ?>
                                <p class="text-muted">Aucune description n'est disponible pour ce module.</p>
                            <?php else: ?>
                                <?php foreach ($descriptions as $description): ?>
                                    <div class="description-item" data-id="<?= $description['id'] ?>" data-full-text="<?= htmlspecialchars($description['description_text']) ?>" onclick="toggleSelection(this)">
                                        <div class="d-flex justify-content-between">
                                            <strong>Description #<?= $description['id'] ?></strong>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($description['created_at'])) ?></small>
                                        </div>
                                        <p class="mb-0 description-preview"><?= substr(htmlspecialchars($description['description_text']), 0, 100) ?>...</p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Descriptions sélectionnées -->
                    <div class="tab-pane fade" id="selected-descriptions" role="tabpanel" aria-labelledby="selected-tab">
                        <div id="selected-descriptions-list">
                            <p class="text-muted empty-selection-message">Aucune description sélectionnée.</p>
                        </div>
                    </div>
                    
                    <!-- Descriptions récentes -->
                    <div class="tab-pane fade" id="recent-descriptions" role="tabpanel" aria-labelledby="recent-tab">
                        <div id="recent-descriptions-list">
                            <?php if (empty($descriptions)): ?>
                                <p class="text-muted">Aucune description récente.</p>
                            <?php else: ?>
                                <?php 
                                $recentDescriptions = array_slice($descriptions, 0, 5); 
                                foreach ($recentDescriptions as $description): 
                                ?>
                                    <div class="description-item" data-id="<?= $description['id'] ?>" data-full-text="<?= htmlspecialchars($description['description_text']) ?>" onclick="toggleSelection(this)">
                                        <div class="d-flex justify-content-between">
                                            <strong>Description #<?= $description['id'] ?></strong>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($description['created_at'])) ?></small>
                                        </div>
                                        <p class="mb-0 description-preview"><?= substr(htmlspecialchars($description['description_text']), 0, 100) ?>...</p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <label for="qcm-count" class="form-label">Nombre de questions</label>
                    <input type="number" class="form-control" id="qcm-count" min="1" max="10" value="5">
                </div>
                
                <button id="generate-btn" class="btn btn-primary w-100" onclick="generateQCM()">
                    <i class="fas fa-robot"></i> Générer le QCM
                </button>
                
                <div id="current-selections" class="mt-3">
                    <h5>Sélections actuelles</h5>
                    <ul id="selection-list" class="list-group">
                        <!-- Les descriptions sélectionnées apparaîtront ici -->
                    </ul>
                </div>
            </div>
            
            <!-- Zone principale de chat et QCM -->
            <div class="col-md-8 chat-main">
                <div class="chat-messages" id="chat-area">
                    <div class="message ai">
                        <p>Bonjour ! Je suis votre assistant IA pour générer des QCM basés sur vos descriptions de module. Veuillez sélectionner les descriptions que vous souhaitez utiliser, puis cliquez sur "Générer le QCM".</p>
                    </div>
                    
                    <!-- Les messages et QCMs apparaîtront ici -->
                </div>
                
                <div class="chat-input">
                    <div class="input-group">
                        <input type="text" id="user-input" class="form-control" placeholder="Posez une question sur le QCM ou demandez des précisions...">
                        <button class="btn btn-primary" id="send-btn" onclick="sendQuestion()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="config.js"></script>
    <script src="ai.js"></script>
</body>
</html>
