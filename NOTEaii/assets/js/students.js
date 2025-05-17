// Fonction pour sauvegarder un nouvel étudiant
function saveStudent() {
    const formData = new FormData(document.getElementById('addStudentForm'));
    
    fetch('../../admin/php/add_student.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Étudiant ajouté avec succès!');
            // Fermer le modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addStudentModal'));
            if (modal) {
                modal.hide();
            }
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors de l\'ajout de l\'étudiant');
    });
}

// Fonction pour modifier un étudiant
function editStudent(studentId) {
    // Récupérer les données du formulaire de modification
    const form = document.getElementById('editStudentForm');
    const formData = {
        id: studentId,
        nom: form.querySelector('#edit_nom').value,
        prenom: form.querySelector('#edit_prenom').value,
        email: form.querySelector('#edit_email').value,
        password: form.querySelector('#edit_password').value
    };

    fetch('../../admin/php/edit_student.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Étudiant modifié avec succès!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editStudentModal'));
            if (modal) {
                modal.hide();
            }
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors de la modification');
    });
}

// Gestionnaire d'événements pour les boutons de modification et suppression
document.addEventListener('DOMContentLoaded', function() {
    // Gestionnaire pour les boutons de modification
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            // Ouvrir le modal de modification
            const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
            editModal.show();
            
            // Stocker l'ID de l'étudiant pour la modification
            document.getElementById('editStudentForm').setAttribute('data-student-id', studentId);

            // Pré-remplir le formulaire avec les données de l'étudiant
            const form = document.getElementById('editStudentForm');
            document.getElementById('edit_nom').value = this.getAttribute('data-nom');
            document.getElementById('edit_prenom').value = this.getAttribute('data-prenom');
            document.getElementById('edit_email').value = this.getAttribute('data-email');
            document.getElementById('edit_password').value = this.getAttribute('data-password');
        });
    });

    // Gestionnaire pour les boutons de suppression
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')) {
                const studentId = this.getAttribute('data-student-id');
                
                fetch('../../admin/php/delete_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: studentId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Étudiant supprimé avec succès!');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la suppression');
                });
            }
        });
    });
}); 