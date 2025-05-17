// Variables globales
let selectedDescriptions = [];
let currentQCM = null;
let conversationHistory = [
    {role: "system", content: "Vous êtes un assistant éducatif spécialisé dans la création de questionnaires à choix multiples (QCM) et dans l'explication de concepts éducatifs. Votre tâche est de générer des QCM pertinents basés sur le contenu fourni, d'aider à comprendre les concepts, et de fournir des explications détaillées des réponses."}
];

// Variables pour la pagination
let currentPage = 1;
let itemsPerPage = 10;
let filteredDescriptions = [];
let allDescriptions = [];

// Fonction pour sélectionner/désélectionner une description
function toggleSelection(element) {
    const descId = element.dataset.id;
    const descText = element.querySelector('.description-preview').textContent;
    const fullText = element.getAttribute('data-full-text') || descText;
    
    // Trouver tous les éléments avec le même ID (pour les maintenir synchronisés entre les onglets)
    const allMatchingElements = document.querySelectorAll(`.description-item[data-id="${descId}"]`);
    
    if (element.classList.contains('selected')) {
        // Désélectionner
        allMatchingElements.forEach(el => el.classList.remove('selected'));
        selectedDescriptions = selectedDescriptions.filter(item => item.id !== descId);
        
        // Supprimer de la liste des sélections
        document.querySelector(`#selection-item-${descId}`)?.remove();
        
        // Mettre à jour l'onglet des sélections
        updateSelectedTab();
    } else {
        // Sélectionner
        allMatchingElements.forEach(el => el.classList.add('selected'));
        selectedDescriptions.push({
            id: descId,
            text: fullText
        });
        
        // Ajouter à la liste des sélections standard
        const selectionList = document.getElementById('selection-list');
        const listItem = document.createElement('li');
        listItem.className = 'list-group-item';
        listItem.id = `selection-item-${descId}`;
        listItem.textContent = `Description #${descId}`;
        selectionList.appendChild(listItem);
        
        // Mettre à jour l'onglet des sélections
        updateSelectedTab();
    }
}

// Fonction pour mettre à jour l'onglet des sélections
function updateSelectedTab() {
    const selectedTab = document.getElementById('selected-descriptions-list');
    
    // Effacer le contenu actuel
    selectedTab.innerHTML = '';
    
    if (selectedDescriptions.length === 0) {
        selectedTab.innerHTML = '<p class="text-muted empty-selection-message">Aucune description sélectionnée.</p>';
        return;
    }
    
    // Ajouter chaque description sélectionnée
    selectedDescriptions.forEach(item => {
        const descItem = document.createElement('div');
        descItem.className = 'description-item selected';
        descItem.dataset.id = item.id;
        descItem.setAttribute('data-full-text', item.text);
        descItem.onclick = function() { toggleSelection(this); };
        
        descItem.innerHTML = `
            <div class="d-flex justify-content-between">
                <strong>Description #${item.id}</strong>
                <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); removeSelection('${item.id}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="mb-0 description-preview">${item.text.substring(0, 100)}...</p>
        `;
        
        selectedTab.appendChild(descItem);
    });
}

// Fonction pour supprimer une sélection directement
function removeSelection(id) {
    const elements = document.querySelectorAll(`.description-item[data-id="${id}"]`);
    if (elements.length > 0) {
        toggleSelection(elements[0]);
    }
}

// Fonction pour filtrer les descriptions
function filterDescriptions() {
    const searchTerm = document.getElementById('search-descriptions').value.toLowerCase();
    
    if (searchTerm === '') {
        filteredDescriptions = [...allDescriptions];
    } else {
        filteredDescriptions = allDescriptions.filter(desc => {
            const text = desc.text.toLowerCase();
            const id = desc.id.toString();
            return text.includes(searchTerm) || id.includes(searchTerm);
        });
    }
    
    // Réinitialiser la pagination
    currentPage = 1;
    
    // Mettre à jour l'affichage
    renderDescriptions();
}

// Fonction pour aller à la page précédente
function goToPrevPage() {
    if (currentPage > 1) {
        currentPage--;
        renderDescriptions();
    }
}

// Fonction pour aller à la page suivante
function goToNextPage() {
    const totalPages = Math.ceil(filteredDescriptions.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderDescriptions();
    }
}

// Fonction pour afficher les descriptions paginées
function renderDescriptions() {
    const descList = document.getElementById('descriptions-list');
    descList.innerHTML = '';
    
    if (filteredDescriptions.length === 0) {
        descList.innerHTML = '<p class="text-muted">Aucune description correspondant à votre recherche.</p>';
        document.getElementById('pagination-info').textContent = 'Aucun résultat';
        document.getElementById('prev-page').disabled = true;
        document.getElementById('next-page').disabled = true;
        return;
    }
    
    // Calculer les indices de début et de fin pour la pagination
    const start = (currentPage - 1) * itemsPerPage;
    const end = Math.min(start + itemsPerPage, filteredDescriptions.length);
    const pageDescriptions = filteredDescriptions.slice(start, end);
    
    // Mettre à jour les informations de pagination
    document.getElementById('pagination-info').textContent = `Affichage ${start + 1}-${end} sur ${filteredDescriptions.length}`;
    
    // Activer/désactiver les boutons de pagination
    document.getElementById('prev-page').disabled = currentPage === 1;
    document.getElementById('next-page').disabled = currentPage >= Math.ceil(filteredDescriptions.length / itemsPerPage);
    
    // Afficher les descriptions de la page courante
    const searchTerm = document.getElementById('search-descriptions').value.toLowerCase();
    
    pageDescriptions.forEach(desc => {
        const descItem = document.createElement('div');
        descItem.className = 'description-item';
        if (selectedDescriptions.some(item => item.id === desc.id)) {
            descItem.classList.add('selected');
        }
        
        descItem.dataset.id = desc.id;
        descItem.setAttribute('data-full-text', desc.text);
        descItem.onclick = function() { toggleSelection(this); };
        
        let previewText = desc.text.substring(0, 100) + '...';
        
        // Mettre en surbrillance les termes de recherche
        if (searchTerm) {
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            previewText = previewText.replace(regex, '<span class="highlight-text">$1</span>');
        }
        
        descItem.innerHTML = `
            <div class="d-flex justify-content-between">
                <strong>Description #${desc.id}</strong>
                <small class="text-muted">${desc.date}</small>
            </div>
            <p class="mb-0 description-preview">${previewText}</p>
        `;
        
        descList.appendChild(descItem);
    });
}

// Après avoir généré le QCM, ajouter des écouteurs d'événements pour améliorer la réactivité
function setupOptionClickHandlers() {
    // Rendre les options entières cliquables
    document.querySelectorAll('.qcm-option').forEach(option => {
        option.addEventListener('click', function(e) {
            // Ne pas déclencher si on a cliqué directement sur l'input radio (déjà géré)
            if (e.target.type !== 'radio') {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    
                    // Ajouter/supprimer la classe selected pour l'effet visuel
                    const questionContainer = this.closest('.options-container');
                    questionContainer.querySelectorAll('.qcm-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    this.classList.add('selected');
                }
            }
        });
        
        // Gérer aussi le cas où on clique directement sur le radio
        const radio = option.querySelector('input[type="radio"]');
        if (radio) {
            radio.addEventListener('change', function() {
                const questionContainer = this.closest('.options-container');
                questionContainer.querySelectorAll('.qcm-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                if (this.checked) {
                    this.closest('.qcm-option').classList.add('selected');
                }
            });
        }
    });
}

// Fonction pour générer un QCM
async function generateQCM() {
    if (selectedDescriptions.length === 0) {
        alert("Veuillez sélectionner au moins une description.");
        return;
    }
    
    // Désactiver le bouton pendant la génération
    const generateBtn = document.getElementById('generate-btn');
    generateBtn.disabled = true;
    generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Génération en cours...';
    
    // Afficher le message de chargement
    const chatArea = document.getElementById('chat-area');
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'spinner-container';
    loadingDiv.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    `;
    chatArea.appendChild(loadingDiv);
    
    // Préparer les données
    const qcmCount = document.getElementById('qcm-count').value;
    let contextText = selectedDescriptions.map(item => item.text).join('\n\n');
    
    // Ajouter le message utilisateur à l'historique
    const userMessage = `Je souhaite générer un QCM de ${qcmCount} questions basé sur les descriptions suivantes:\n\n${contextText}`;
    conversationHistory.push({role: "user", content: userMessage});
    
    // Ajouter le message utilisateur à l'interface
    const userMessageDiv = document.createElement('div');
    userMessageDiv.className = 'message user';
    userMessageDiv.innerHTML = `<p>J'ai sélectionné ${selectedDescriptions.length} description(s) et demandé un QCM de ${qcmCount} questions.</p>`;
    chatArea.appendChild(userMessageDiv);
    
    try {
        // Construire un message système plus simple pour éviter les problèmes de formatage
        const systemPrompt = "Crée un QCM interactif avec un court résumé et " + 
                            qcmCount + " questions. Chaque question doit avoir 4 options (A,B,C,D) " +
                            "et une seule réponse correcte. Inclus une explication pour chaque réponse. " +
                            "Formate ta réponse en JSON valide avec les champs 'summary' et 'questions'.";

        // Appel à l'API
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + API_KEY
            },
            body: JSON.stringify({
                model: API_MODEL,
                messages: [
                    ...conversationHistory,
                    {role: "system", content: systemPrompt}
                ],
                max_tokens: MAX_TOKENS,
                temperature: 0.3
            })
        });
        
        // Vérifier si la requête a réussi
        if (!response.ok) {
            const errorData = await response.json();
            const errorMessage = errorData.error && errorData.error.message ? errorData.error.message : 'Problème de connexion à l\'API';
            throw new Error('Erreur API: ' + errorMessage);
        }
        
        const data = await response.json();
        
        // Supprimer le spinner
        loadingDiv.remove();
        
        // Extraire et traiter le contenu de la réponse
        if (data.choices && data.choices[0] && data.choices[0].message && data.choices[0].message.content) {
            const aiMessage = data.choices[0].message.content;
            
            // Tentative d'extraction du JSON de multiples façons
            let jsonContent;
            let qcmData;
            
            try {
                // Essai 1: Chercher un bloc JSON dans le contenu avec des accolades complètes
                const jsonMatch = aiMessage.match(/```(?:json)?\s*([\s\S]*?)\s*```/) || 
                                aiMessage.match(/({[\s\S]*})/);
                
                if (jsonMatch && jsonMatch[1]) {
                    jsonContent = jsonMatch[1].trim();
                } else {
                    // Si pas de correspondance, essayer d'utiliser tout le contenu
                    jsonContent = aiMessage.trim();
                }
                
                // Nettoyage supplémentaire - retirer markdown, espaces, etc.
                jsonContent = jsonContent.replace(/^```json\s*|```$/g, '').trim();
                
                // Analyser le JSON
                qcmData = JSON.parse(jsonContent);
                
                // Vérification de la structure minimale attendue
                if (!qcmData.summary || !qcmData.questions || !Array.isArray(qcmData.questions)) {
                    throw new Error("Structure JSON incorrecte");
                }
                
                // Nettoyer et normaliser les données des questions
                qcmData.questions = qcmData.questions.map((question, index) => {
                    // Assurer que les propriétés de base existent
                    const cleanedQuestion = {
                        text: question.text || `Question ${index + 1}`,
                        options: [],
                        correctAnswer: question.correctAnswer || "A",
                        explanation: question.explanation || "Explication non disponible."
                    };
                    
                    // Normaliser les options
                    if (question.options && Array.isArray(question.options)) {
                        cleanedQuestion.options = question.options.map(opt => ({
                            id: opt.id || '',
                            text: opt.text || 'Option sans texte'
                        }));
                    } else {
                        // Créer des options par défaut si aucune n'existe
                        cleanedQuestion.options = [
                            {id: "A", text: "Option A"},
                            {id: "B", text: "Option B"},
                            {id: "C", text: "Option C"},
                            {id: "D", text: "Option D"}
                        ];
                    }
                    
                    return cleanedQuestion;
                });
                
            } catch (e) {
                console.error("Erreur lors du parsing JSON:", e, "Contenu reçu:", aiMessage);
                
                // Tenter de créer une structure minimale à partir du texte reçu
                const lines = aiMessage.split('\n');
                const summary = lines[0] || "Résumé non disponible";
                
                // Créer un QCM de secours minimal
                qcmData = {
                    summary: summary,
                    questions: [{
                        text: "Question exemple (erreur de formatage dans la réponse originale)",
                        options: [
                            {id: "A", text: "Option A"},
                            {id: "B", text: "Option B"},
                            {id: "C", text: "Option C"},
                            {id: "D", text: "Option D"}
                        ],
                        correctAnswer: "A",
                        explanation: "Les données originales n'ont pas pu être analysées correctement."
                    }]
                };
                
                // Ajouter un message d'erreur visible
                const errorDiv = document.createElement('div');
                errorDiv.className = 'message ai';
                errorDiv.innerHTML = `
                    <p class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Erreur de formatage dans la réponse de l'IA. Un QCM minimal a été généré.
                    </p>
                    <p>Détail technique: ${e.message}</p>
                `;
                chatArea.appendChild(errorDiv);
            }
            
            // Stocker le QCM courant
            currentQCM = qcmData;
            
            // Créer la réponse de l'IA avec le QCM
            const aiMessageDiv = document.createElement('div');
            aiMessageDiv.className = 'message ai';
            
            // Construire le HTML pour le QCM
            let qcmHtml = `
                <h4 class="qcm-title">Résumé des concepts clés</h4>
                <p class="qcm-summary">${qcmData.summary}</p>
                <div class="qcm-container">
                    <h4 class="qcm-title">QCM Généré</h4>
                    <div id="qcm-questions">
            `;
            
            // Ajouter chaque question avec ses options
            qcmData.questions.forEach((question, qIndex) => {
                qcmHtml += `
                    <div class="qcm-question card" id="question-${qIndex}">
                        <div class="card-header">
                            <h5 class="mb-0">Question ${qIndex + 1}</h5>
                        </div>
                        <div class="card-body">
                            <p class="question-text"><strong>${question.text}</strong></p>
                            <div class="options-container">
                `;
                
                // Ajouter chaque option
                if (question.options && Array.isArray(question.options) && question.options.length > 0) {
                    question.options.forEach(option => {
                        qcmHtml += `
                            <div class="qcm-option" onclick="selectOption(this, ${qIndex}, '${option.id}')">
                                <input class="form-check-input" type="radio" name="q${qIndex}" id="q${qIndex}${option.id}" value="${option.id}" data-question="${qIndex}">
                                <label class="form-check-label" for="q${qIndex}${option.id}">
                                    <span class="option-letter">${option.id}</span> ${option.text}
                                </label>
                            </div>
                        `;
                    });
                } else {
                    qcmHtml += '<div class="alert alert-warning">Options non disponibles pour cette question</div>';
                }
                
                qcmHtml += `
                            </div>
                            <div class="feedback-container hidden" id="feedback-${qIndex}"></div>
                            <button class="btn btn-outline-info mt-3 explain-btn" onclick="explainQuestion(${qIndex})">
                                <i class="fas fa-question-circle"></i> Demander une explication
                            </button>
                        </div>
                    </div>
                `;
            });
            
            // Ajouter les boutons de contrôle
            qcmHtml += `
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <button class="btn btn-success check-answers-btn" onclick="checkAnswers()">
                            <i class="fas fa-check-circle"></i> Vérifier les réponses
                        </button>
                        <button class="btn btn-primary new-qcm-btn" onclick="generateQCM()">
                            <i class="fas fa-sync-alt"></i> Générer un nouveau QCM
                        </button>
                    </div>
                </div>
            `;
            
            // Ajouter le HTML à l'élément de message
            aiMessageDiv.innerHTML = qcmHtml;
            chatArea.appendChild(aiMessageDiv);
            
            // Ajouter la réponse à l'historique de conversation
            conversationHistory.push({role: "assistant", content: aiMessage});
            
            // Faire défiler vers le bas
            chatArea.scrollTop = chatArea.scrollHeight;
        } else {
            throw new Error("La réponse de l'API ne contient pas de message");
        }
    } catch (error) {
        console.error("Erreur lors de la génération du QCM:", error);
        
        // Supprimer le spinner
        loadingDiv.remove();
        
        // Afficher le message d'erreur de façon plus détaillée
        const errorDiv = document.createElement('div');
        errorDiv.className = 'message ai';
        errorDiv.innerHTML = `
            <p class="text-danger">
                <i class="fas fa-exclamation-circle"></i> Une erreur s'est produite lors de la génération du QCM:
            </p>
            <p>${error.message}</p>
            <p>Conseils:</p>
            <ul>
                <li>Vérifiez votre connexion internet</li>
                <li>Essayez avec une description plus courte</li>
                <li>Réduisez le nombre de questions demandées</li>
            </ul>
        `;
        chatArea.appendChild(errorDiv);
    } finally {
        // Réactiver le bouton
        generateBtn.disabled = false;
        generateBtn.innerHTML = '<i class="fas fa-robot"></i> Générer le QCM';
    }
}

// Fonction pour sélectionner une option
function selectOption(element, questionIndex, optionId) {
    // Trouver tous les éléments d'options pour cette question
    const questionContainer = element.closest('.options-container');
    const radioInput = element.querySelector('input[type="radio"]');
    
    // Déselectionner toutes les options
    questionContainer.querySelectorAll('.qcm-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    // Sélectionner l'option cliquée
    element.classList.add('selected');
    radioInput.checked = true;
}

// Fonction pour vérifier les réponses
function checkAnswers() {
    if (!currentQCM) return;
    
    let correctCount = 0;
    const total = currentQCM.questions.length;
    
    currentQCM.questions.forEach((question, index) => {
        // Vérifier si la question a des options valides
        if (!question.options || !Array.isArray(question.options) || question.options.length === 0) {
            console.error("La question", index + 1, "n'a pas d'options valides:", question);
            return;
        }

        const selectedOption = document.querySelector(`input[name="q${index}"]:checked`);
        const questionDiv = document.getElementById(`question-${index}`);
        const feedbackDiv = document.getElementById(`feedback-${index}`);
        
        // Réinitialiser les classes
        questionDiv.querySelectorAll('.qcm-option').forEach(opt => {
            opt.classList.remove('option-correct', 'option-incorrect');
        });
        
        // S'assurer que la réponse correcte existe
        const correctAnswer = question.correctAnswer || question.options[0].id;
        
        if (selectedOption) {
            const userAnswer = selectedOption.value;
            
            // Vérifier si l'élément de la réponse correcte existe
            const correctElement = document.querySelector(`#q${index}${correctAnswer}`);
            if (correctElement) {
                correctElement.closest('.qcm-option').classList.add('option-correct');
            }
            
            if (userAnswer === correctAnswer) {
                correctCount++;
                // Mettre à jour l'apparence du feedback
                feedbackDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i> 
                        <strong>Correct!</strong> ${question.explanation}
                    </div>
                `;
                // Ajouter une animation de succès
                questionDiv.classList.add('correct-answer-animation');
            } else {
                // Mettre en évidence la réponse incorrecte de l'utilisateur
                selectedOption.closest('.qcm-option').classList.add('option-incorrect');
                feedbackDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Incorrect.</strong> La réponse correcte est l'option ${correctAnswer}. 
                        <hr>
                        <p>${question.explanation}</p>
                    </div>
                `;
            }
        } else {
            // Mettre en évidence la réponse correcte
            const correctElement = document.querySelector(`#q${index}${correctAnswer}`);
            if (correctElement) {
                correctElement.closest('.qcm-option').classList.add('option-correct');
            }
            feedbackDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Attention!</strong> Vous n'avez pas répondu. La réponse correcte est l'option ${correctAnswer}.
                </div>
            `;
        }
        
        feedbackDiv.classList.remove('hidden');
    });
    
    // Afficher le score avec une animation
    const chatArea = document.getElementById('chat-area');
    const scoreDiv = document.createElement('div');
    scoreDiv.className = 'message ai score-message';
    
    // Calculer le pourcentage pour la notation
    const percentage = Math.round(correctCount/total*100);
    let gradeText = "Besoin d'amélioration";
    let gradeClass = "text-danger";
    
    if (percentage >= 80) {
        gradeText = "Excellent!";
        gradeClass = "text-success";
    } else if (percentage >= 60) {
        gradeText = "Bien";
        gradeClass = "text-info";
    } else if (percentage >= 40) {
        gradeText = "Moyen";
        gradeClass = "text-warning";
    }
    
    scoreDiv.innerHTML = `
        <div class="score-container">
            <h4>Résultat</h4>
            <div class="progress mb-3">
                <div class="progress-bar progress-bar-striped" role="progressbar" 
                    style="width: 0%;" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">${percentage}%</div>
            </div>
            <p>Vous avez obtenu <strong>${correctCount}/${total}</strong> <span class="${gradeClass}">(${gradeText})</span></p>
            <div class="text-center mt-3">
                <button class="btn btn-primary" onclick="generateQCM()">Essayer un nouveau QCM</button>
            </div>
        </div>
    `;
    chatArea.appendChild(scoreDiv);
    
    // Animer la barre de progression
    setTimeout(() => {
        const progressBar = scoreDiv.querySelector('.progress-bar');
        progressBar.style.width = `${percentage}%`;
    }, 100);
    
    // Faire défiler vers le bas
    chatArea.scrollTop = chatArea.scrollHeight;
}

// Fonction pour demander une explication sur une question
async function explainQuestion(questionIndex) {
    if (!currentQCM) return;
    
    const question = currentQCM.questions[questionIndex];
    const chatArea = document.getElementById('chat-area');
    
    // Ajouter le message utilisateur
    const userMessageDiv = document.createElement('div');
    userMessageDiv.className = 'message user';
    userMessageDiv.innerHTML = `<p>Pouvez-vous m'expliquer davantage la question ${questionIndex + 1} ?</p>`;
    chatArea.appendChild(userMessageDiv);
    
    // Ajouter à l'historique
    conversationHistory.push({
        role: "user", 
        content: `Pouvez-vous m'expliquer davantage la question suivante: "${question.text}" et pourquoi la réponse est "${question.correctAnswer}" ?`
    });
    
    // Afficher le spinner
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'spinner-container';
    loadingDiv.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    `;
    chatArea.appendChild(loadingDiv);
    
    try {
        // Appel à l'API
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + API_KEY
            },
            body: JSON.stringify({
                model: API_MODEL,
                messages: conversationHistory,
                max_tokens: MAX_TOKENS
            })
        });
        
        const data = await response.json();
        
        // Supprimer le spinner
        loadingDiv.remove();
        
        if (data.choices && data.choices[0] && data.choices[0].message) {
            const aiMessage = data.choices[0].message.content;
            
            // Afficher l'explication
            const aiMessageDiv = document.createElement('div');
            aiMessageDiv.className = 'message ai';
            aiMessageDiv.innerHTML = `<p>${aiMessage}</p>`;
            chatArea.appendChild(aiMessageDiv);
            
            // Ajouter à l'historique
            conversationHistory.push({role: "assistant", content: aiMessage});
            
            // Reconfigurer les gestionnaires d'événements
            setupOptionClickHandlers();
        } else {
            throw new Error("Format de réponse invalide");
        }
    } catch (error) {
        console.error("Erreur lors de la demande d'explication:", error);
        
        // Supprimer le spinner
        loadingDiv.remove();
        
        // Afficher le message d'erreur
        const errorDiv = document.createElement('div');
        errorDiv.className = 'message ai';
        errorDiv.innerHTML = `<p class="text-danger">Une erreur s'est produite: ${error.message}</p>`;
        chatArea.appendChild(errorDiv);
    }
    
    // Faire défiler vers le bas
    chatArea.scrollTop = chatArea.scrollHeight;
}

// Fonction pour envoyer une question à l'IA
async function sendQuestion() {
    const userInput = document.getElementById('user-input');
    const question = userInput.value.trim();
    
    if (!question) return;
    
    const chatArea = document.getElementById('chat-area');
    
    // Afficher la question
    const userMessageDiv = document.createElement('div');
    userMessageDiv.className = 'message user';
    userMessageDiv.innerHTML = `<p>${question}</p>`;
    chatArea.appendChild(userMessageDiv);
    
    // Vider l'input
    userInput.value = '';
    
    // Ajouter à l'historique
    conversationHistory.push({role: "user", content: question});
    
    // Afficher le spinner
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'spinner-container';
    loadingDiv.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    `;
    chatArea.appendChild(loadingDiv);
    
    try {
        // Appel à l'API
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + API_KEY
            },
            body: JSON.stringify({
                model: API_MODEL,
                messages: conversationHistory,
                max_tokens: MAX_TOKENS
            })
        });
        
        const data = await response.json();
        
        // Supprimer le spinner
        loadingDiv.remove();
        
        if (data.choices && data.choices[0] && data.choices[0].message) {
            const aiMessage = data.choices[0].message.content;
            
            // Afficher la réponse
            const aiMessageDiv = document.createElement('div');
            aiMessageDiv.className = 'message ai';
            aiMessageDiv.innerHTML = `<p>${aiMessage}</p>`;
            chatArea.appendChild(aiMessageDiv);
            
            // Ajouter à l'historique
            conversationHistory.push({role: "assistant", content: aiMessage});
        } else {
            throw new Error("Format de réponse invalide");
        }
    } catch (error) {
        console.error("Erreur lors de l'envoi de la question:", error);
        
        // Supprimer le spinner
        loadingDiv.remove();
        
        // Afficher le message d'erreur
        const errorDiv = document.createElement('div');
        errorDiv.className = 'message ai';
        errorDiv.innerHTML = `<p class="text-danger">Une erreur s'est produite: ${error.message}</p>`;
        chatArea.appendChild(errorDiv);
    }
    
    // Faire défiler vers le bas
    chatArea.scrollTop = chatArea.scrollHeight;
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Collecter toutes les descriptions disponibles
    const descriptionElements = document.querySelectorAll('#descriptions-list .description-item');
    
    allDescriptions = Array.from(descriptionElements).map(el => {
        return {
            id: el.dataset.id,
            text: el.getAttribute('data-full-text') || el.querySelector('.description-preview').textContent,
            date: el.querySelector('.text-muted').textContent,
            element: el
        };
    });
    
    // Initialiser les descriptions filtrées
    filteredDescriptions = [...allDescriptions];
    
    // Configurer l'écouteur d'événement pour le champ de recherche
    document.getElementById('search-descriptions').addEventListener('input', filterDescriptions);
    
    // Configurer les boutons de pagination
    document.getElementById('prev-page').addEventListener('click', goToPrevPage);
    document.getElementById('next-page').addEventListener('click', goToNextPage);
    
    // Initialiser l'affichage
    renderDescriptions();
    
    // Événement pour envoyer une question avec la touche Entrée
    document.getElementById('user-input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendQuestion();
        }
    });
}); 