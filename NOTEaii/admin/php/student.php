<?php
session_start();
require_once '../../config/database.php';

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.html');
    exit();
}

// Récupérer tous les étudiants
try {
    // Vérifier que la connexion est établie
    if (!isset($conn) || $conn === null) {
        throw new Exception('La connexion à la base de données n\'est pas établie');
    }

    $stmt = $conn->query("
        SELECT id, nom, prenom, email, password, created_at 
        FROM users 
        ORDER BY nom, prenom
    ");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants - NOTEAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/images/logoa.png" alt="NOTEAI Logo" class="logo">
                <h3>NOTEAI</h3>
            </div>
            <ul class="nav-links">
                <li class="active">
                    <a href="student.php">
                        <i class="fas fa-users"></i>
                        <span>Étudiants</span>
                    </a>
                </li>
            </ul>
            <div class="stylish-logout">
                <a href="../../php/logout.php" class="logout-btn stylish-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchStudent" placeholder="Rechercher un étudiant...">
                </div>
                <div class="user-info">
                    <span class="welcome-text">Bonjour Mr <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span>
                </div>
            </header>

            <div class="content-wrapper">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="page-header">
                    <h1 class="page-title">Gestion des Étudiants</h1>
                    <button class="btn-add-student" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="fas fa-plus"></i> Ajouter un Étudiant
                    </button>
                </div>

                <!-- Students List -->
                <div class="students-list">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom Complet</th>
                                    <th>Email</th>
                                    <th>Mot de passe</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($students) && !empty($students)): ?>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['nom'] . ' ' . $student['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['password']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($student['created_at'])); ?></td>
                                        <td class="actions">
                                            <button class="btn-action edit-btn" 
                                                data-student-id="<?php echo $student['id']; ?>"
                                                data-nom="<?php echo htmlspecialchars($student['nom']); ?>"
                                                data-prenom="<?php echo htmlspecialchars($student['prenom']); ?>"
                                                data-email="<?php echo htmlspecialchars($student['email']); ?>"
                                                data-password="<?php echo htmlspecialchars($student['password']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action delete-btn" data-student-id="<?php echo $student['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun étudiant trouvé</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un Étudiant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStudentForm">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="text" class="form-control" id="password" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveStudent()">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'Étudiant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editStudentForm">
                        <div class="mb-3">
                            <label for="edit_nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="edit_nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="edit_prenom" name="prenom" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Nouveau mot de passe</label>
                            <input type="text" class="form-control" id="edit_password" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="editStudent(document.getElementById('editStudentForm').getAttribute('data-student-id'))">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/students.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchStudent');
        if (!searchInput) return;
        searchInput.addEventListener('input', function() {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('.students-list tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    });
    </script>