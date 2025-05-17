// Variable globale (ou à portée de script) pour suivre l'état
let isCurrentlyEditing = false;

// Fonction pour afficher les messages
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('#modulesList'));
    setTimeout(() => alertDiv.remove(), 5000);
}

// Fonction pour ajouter un champ de description dans le formulaire
function addDescriptionField(text = '') {
    const container = document.getElementById('descriptionFields');
    const newFieldGroup = document.createElement('div');
    newFieldGroup.className = 'input-group mb-2';

    const newTextarea = document.createElement('textarea');
    newTextarea.className = 'form-control';
    newTextarea.name = 'descriptions[]'; // Nom de tableau pour PHP
    newTextarea.rows = 2;
    newTextarea.value = text;

    const removeButton = document.createElement('button');
    removeButton.className = 'btn btn-outline-danger';
    removeButton.type = 'button';
    removeButton.innerHTML = '<i class="fas fa-trash"></i>';
    removeButton.onclick = function() {
        container.removeChild(newFieldGroup);
    };

    newFieldGroup.appendChild(newTextarea);
    newFieldGroup.appendChild(removeButton);
    container.appendChild(newFieldGroup);
}

// Gestionnaire d'événement pour le modal d'ajout/modification
const moduleModalElement = document.getElementById('moduleModal');
const moduleModal = new bootstrap.Modal(moduleModalElement);

// Gérer l'état du formulaire lors de l'ouverture du modal
moduleModalElement.addEventListener('show.bs.modal', function(event) {
    console.log("[show.bs.modal] Fired. isCurrentlyEditing:", isCurrentlyEditing);
    const modalTitle = moduleModalElement.querySelector('.modal-title');
    const form = document.getElementById('moduleForm');
    const descriptionFieldsContainer = document.getElementById('descriptionFields');

    if (!isCurrentlyEditing) {
        // ---- MODE AJOUT ----
        console.log("[show.bs.modal] ADD mode detected. Resetting form.");
        modalTitle.textContent = 'Ajouter un Module';
        form.reset(); 
        document.getElementById('moduleId').value = ''; 
        descriptionFieldsContainer.innerHTML = ''; // Vider les descriptions
        addDescriptionField(); // Ajouter un premier champ de description vide
    } else {
        // ---- MODE ÉDITION ----
        console.log("[show.bs.modal] EDIT mode detected. Setting title.");
        modalTitle.textContent = 'Modifier le Module';
        // Le remplissage a DÉJÀ été fait par l'écouteur de clic sur le bouton.
        // NE RIEN FAIRE D'AUTRE ICI.
    }
    // Toujours réinitialiser le flag pour la prochaine fois
    isCurrentlyEditing = false;
});

// Fonction pour sauvegarder un module (ajout ou modification)
function saveModule() {
    const form = document.getElementById('moduleForm');
    const formData = new FormData(form);
    // FormData récupère automatiquement les champs avec `name="descriptions[]"` comme un tableau

    // L'URL est maintenant toujours save_module.php car il gère l'insertion et la mise à jour
    const url = 'save_module.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message || 'Module enregistré avec succès');
            moduleModal.hide(); // Fermer le modal manuellement
            // Recharger la page pour voir les changements (simple, mais pourrait être optimisé)
            setTimeout(() => location.reload(), 500);
        } else {
            showAlert(data.error || 'Erreur lors de l\'enregistrement', 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la communication avec le serveur', 'danger');
    });
}

// Fonction pour supprimer un module
function deleteModule(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce module ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);

    fetch('delete_module.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Module supprimé avec succès');
            location.reload();
        } else {
            showAlert(data.error || 'Erreur lors de la suppression', 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors de la communication avec le serveur', 'danger');
    });
}

// Fonction pour afficher les détails du module
function showModuleDetails(module) {
    document.getElementById('detailsModuleTitle').textContent = module.nom;
    document.getElementById('detailsModuleCode').textContent = module.code;
    
    const descriptionsList = document.getElementById('detailsModuleDescriptions');
    descriptionsList.innerHTML = ''; // Vider la liste précédente
    if (module.descriptions && module.descriptions.length > 0) {
        module.descriptions.forEach(desc => {
            const listItem = document.createElement('li');
            listItem.className = 'mb-2 p-2 border rounded bg-light'; // Style simple
            listItem.textContent = desc;
            descriptionsList.appendChild(listItem);
        });
    } else {
        descriptionsList.innerHTML = '<li><i>Aucune description disponible</i></li>';
    }

    document.getElementById('detailsModuleSemestre').textContent = 'Semestre ' + module.semestre;
    document.getElementById('detailsModuleCoefficient').textContent = 'Coefficient: ' + module.coefficient;
    
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('moduleDetailsModal'));
    modal.show();
}

// Initialisation des modales Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    console.log("[DOMContentLoaded] Event fired. Setting up listeners..."); // Log initial

    // Initialiser toutes les modales
    var modals = document.querySelectorAll('.modal');
    modals.forEach(function(modalElement) {
        new bootstrap.Modal(modalElement, {
            keyboard: true, // Permet la fermeture avec la touche Échap
            backdrop: true  // Permet la fermeture en cliquant en dehors
        });
    });

    // Ajouter le style pour rendre la carte cliquable
    document.querySelectorAll('.module-card').forEach(card => {
        card.style.cursor = 'pointer';
    });

    // Attacher l'événement au bouton "Ajouter une description"
    const addDescButton = document.getElementById('addDescriptionButton');
    if (addDescButton) {
        addDescButton.addEventListener('click', function() {
            addDescriptionField(); // Appeler la fonction existante
        });
    } else {
        console.error("Le bouton #addDescriptionButton n'a pas été trouvé !");
    }

    // Attacher l'écouteur aux boutons Modifier
    const editButtons = document.querySelectorAll('.js-edit-module-btn');
    console.log(`[DOMContentLoaded] Found ${editButtons.length} edit buttons.`); // Log nombre de boutons

    editButtons.forEach((button, index) => {
        button.addEventListener('click', function(event) {
            console.log(`[Edit Button ${index} Click] Click detected.`); // Log clic spécifique
            event.stopPropagation(); // Essayer d'arrêter la propagation IMMÉDIATEMENT
            console.log(`[Edit Button ${index} Click] Propagation stopped.`); // Confirmer arrêt
            
            isCurrentlyEditing = true; 
            console.log(`[Edit Button ${index} Click] isCurrentlyEditing set to true.`);

            const moduleDataString = this.getAttribute('data-module-data');
            if (!moduleDataString) {
                console.error(`[Edit Button ${index} Click] Erreur: Attribut data-module-data manquant ou vide.`);
                isCurrentlyEditing = false; 
                return;
            }

            try {
                const moduleData = JSON.parse(moduleDataString);
                console.log("[Edit Button Click] Module data parsed:", moduleData);

                const form = document.getElementById('moduleForm');
                const descriptionFieldsContainer = document.getElementById('descriptionFields');

                // Remplir les champs de base
                document.getElementById('moduleId').value = moduleData.id;
                document.getElementById('code').value = moduleData.code;
                document.getElementById('nom').value = moduleData.nom;
                document.getElementById('semestre').value = moduleData.semestre;
                document.getElementById('coefficient').value = moduleData.coefficient;
                console.log("[Edit Button Click] Basic fields filled.");

                // Vider les anciens champs de description
                descriptionFieldsContainer.innerHTML = '';
                console.log("[Edit Button Click] Description container cleared.");

                // Ajouter les champs de description existants
                if (moduleData.descriptions && moduleData.descriptions.length > 0) {
                    moduleData.descriptions.forEach(desc => addDescriptionField(desc));
                } else {
                    addDescriptionField(); // Ajouter au moins un champ vide
                }
                console.log("[Edit Button Click] Description fields populated.");

                // Le modal s'ouvrira automatiquement grâce à data-bs-target
                console.log(`[Edit Button ${index} Click] Form population successful.`);

            } catch (e) {
                console.error(`[Edit Button ${index} Click] Erreur lors du parsing/remplissage:`, e);
                showAlert('Erreur lors du chargement des données du module.', 'danger');
                isCurrentlyEditing = false;
            }
        });
        console.log(`[DOMContentLoaded] Click listener attached to edit button ${index}.`); // Log attachement listener
    });

    console.log("[DOMContentLoaded] Setup complete."); // Fin setup
});