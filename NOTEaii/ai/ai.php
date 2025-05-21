<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTEai - Générateur de QCM avec IA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="ai.css" rel="stylesheet">
    <style>
        .sidebar-description {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 20px;
        }
        .desc-item.selected {
            background: #d4edda;
            border: 2px solid #28a745;
        }
        .desc-item {
            cursor: pointer;
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.2s;
        }
        .desc-item:hover {
            background: #f8f9fa;
        }
        .desc-date {
            float: right;
            color: #888;
            font-size: 0.9em;
        }
        .desc-title {
            font-weight: bold;
        }
        .desc-content {
            font-size: 0.95em;
            color: #555;
        }
        .desc-current {
            background: #f1f3f4;
            border-radius: 5px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .tab-btn.active {
            font-weight: bold;
            color: #007bff;
            border-bottom: 2px solid #007bff;
        }
        .tab-btn {
            background: none;
            border: none;
            margin-right: 10px;
            font-size: 1em;
            padding: 0 0 5px 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="../MOD/description.php?module_id=" class="btn btn-secondary me-2" id="btn-back-desc">
                <i class="fas fa-arrow-left"></i> Revenir aux descriptions
            </a>
            <a href="../php/logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
        <div class="row">
            <!-- Sidebar Descriptions -->
            <div class="col-md-4 col-lg-3">
                <div class="sidebar-description">
                    <h5>Descriptions disponibles</h5>
                    <div id="module-title-sidebar" class="mb-2" style="font-size:1.2em; font-weight:700; color:#ff8800;"></div>
                    <div class="mb-2" style="font-size:0.95em; color:#666;">Sélectionnez les descriptions pour générer un QCM</div>
                    <input type="text" id="desc-search" class="form-control mb-2" placeholder="Rechercher...">
                    <div id="desc-list-container" style="max-height:300px; overflow-y:auto; scrollbar-width:thin;">
                        <div id="desc-list"></div>
                    </div>
                    <hr>
                    <div style="font-weight:500;">Sélections actuelles</div>
                    <div id="desc-current-list" class="mb-2"></div>
                </div>
            </div>
            <!-- Main Content -->
            <div class="col-md-8 col-lg-9">
                <div class="row">
                    <div class="col-md-7">
                        <div class="chat-container">
                            <div class="chat-main">
                                <div class="chat-messages" id="chat-messages">
                                    <div class="qcm-container">
                                        <h3 class="qcm-title">Générateur de QCM avec IA</h3>
                                        <div class="qcm-summary">
                                            Sélectionnez une description à gauche et cliquez sur "Générer le QCM".
                                        </div>
                                        <!-- QCM Generation Form -->
                                        <form id="qcm-form" class="mb-4">
                                            <div class="mb-3">
                                                <label for="question-count" class="form-label">Nombre de questions</label>
                                                <input type="number" class="form-control" id="question-count" min="1" max="20" value="5" required>
                                            </div>
                                            <button type="submit" class="btn btn-generate-qcm">
                                                <i class="fas fa-robot me-2"></i>Générer le QCM
                                            </button>
                                        </form>
                                        <!-- QCM Display Area -->
                                        <div id="qcm-display" class="d-none">
                                            <div id="questions-container"></div>
                                            <div class="text-center mt-4">
                                                <button id="check-answers" class="btn btn-success check-answers-btn">
                                                    <i class="fas fa-check me-2"></i>Vérifier les réponses
                                                </button>
                                                <button id="new-qcm" class="btn btn-primary new-qcm-btn ms-2">
                                                    <i class="fas fa-sync me-2"></i>Nouveau QCM
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Loading Spinner -->
                                        <div id="loading-spinner" class="spinner-container d-none">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Chargement...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <!-- Chatbot Area -->
                        <div class="chatbot-container">
                            <h3>Chatbot Interactif</h3>
                            <div class="chat-box" id="chat-box">
                                <!-- Messages will be loaded here -->
                                <div class="message received">
                                    <p>Bonjour ! Posez-moi une question sur les descriptions sélectionnées.</p>
                                </div>
                            </div>
                            <div class="chat-input">
                                <input type="text" id="chat-message-input" placeholder="Votre message...">
                                <button id="send-message-btn" class="btn btn-primary">Envoyer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let descriptions = [];
        let selectedDescriptions = [];
        let currentTab = 'all';

        function getModuleIdFromUrl() {
            const params = new URLSearchParams(window.location.search);
            return params.get('module_id');
        }

        function fetchDescriptions() {
            const moduleId = getModuleIdFromUrl();
            let url = 'get_descriptions.php';
            if (moduleId) url += '?module_id=' + encodeURIComponent(moduleId);
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    descriptions = data;
                    // Afficher le nom du module si présent
                    const moduleTitleDiv = document.getElementById('module-title-sidebar');
                    if (descriptions.length > 0) {
                        moduleTitleDiv.textContent = descriptions[0].title;
                    } else {
                        moduleTitleDiv.textContent = '';
                    }
                    renderDescriptions();
                    renderCurrentSelection();
                });
        }
        document.addEventListener('DOMContentLoaded', function() {
            fetchDescriptions();
            // Met à jour le lien "Revenir aux descriptions" avec le bon module_id
            const moduleId = getModuleIdFromUrl();
            if (moduleId) {
                document.getElementById('btn-back-desc').href = `../MOD/description.php?module_id=${moduleId}`;
            }
        });

        function renderDescriptions() {
            let list = descriptions;
            if (currentTab === 'selected') {
                list = descriptions.filter(d => selectedDescriptions.includes(d.id));
            } else if (currentTab === 'recent') {
                list = descriptions.slice(0, 2); // exemple : 2 plus récentes
            }
            const search = document.getElementById('desc-search').value.toLowerCase();
            if (search) {
                list = list.filter(d => d.title.toLowerCase().includes(search) || d.content.toLowerCase().includes(search));
            }
            const descList = document.getElementById('desc-list');
            descList.innerHTML = list.length ? '' : '<div class="empty-selection-message">Aucune description trouvée</div>';
            list.forEach((desc, idx) => {
                const index = descriptions.findIndex(d => d.id === desc.id);
                const label = `Description ${index + 1}`;
                const div = document.createElement('div');
                div.className = 'desc-item' + (selectedDescriptions.includes(desc.id) ? ' selected' : '');
                div.innerHTML = `<span class='desc-title'>${label}</span> <span class='desc-date'>${desc.date}</span><br><span class='desc-content'>${desc.content.substring(0, 20)}...</span>`;
                div.onclick = () => toggleSelect(desc.id);
                descList.appendChild(div);
            });
        }
        function toggleSelect(id) {
            if (selectedDescriptions.includes(id)) {
                selectedDescriptions = selectedDescriptions.filter(did => did !== id);
            } else {
                selectedDescriptions.push(id);
            }
            renderDescriptions();
            renderCurrentSelection();
            updateQcmButtonLabel();
        }
        function renderCurrentSelection() {
            const current = document.getElementById('desc-current-list');
            current.innerHTML = selectedDescriptions.length
                ? descriptions.filter(d => selectedDescriptions.includes(d.id)).map(d => {
                    const index = descriptions.findIndex(desc => desc.id === d.id);
                    return `<div class='desc-current'>Description ${index + 1}</div>`;
                }).join('')
                : '<div class="empty-selection-message">Aucune sélection</div>';
        }
        const tabAll = document.getElementById('tab-all');
        const tabSelected = document.getElementById('tab-selected');
        const tabRecent = document.getElementById('tab-recent');
        if (tabAll) tabAll.onclick = function() { currentTab = 'all'; setActiveTab(this); renderDescriptions(); };
        if (tabSelected) tabSelected.onclick = function() { currentTab = 'selected'; setActiveTab(this); renderDescriptions(); };
        if (tabRecent) tabRecent.onclick = function() { currentTab = 'recent'; setActiveTab(this); renderDescriptions(); };
        document.getElementById('desc-search').oninput = renderDescriptions;
        function setActiveTab(btn) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        // QCM form logic
        const qcmForm = document.getElementById('qcm-form');
        const qcmDisplay = document.getElementById('qcm-display');
        const loadingSpinner = document.getElementById('loading-spinner');
        const questionsContainer = document.getElementById('questions-container');
        const checkAnswersBtn = document.getElementById('check-answers');
        const newQcmBtn = document.getElementById('new-qcm');

        qcmForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Submit QCM intercepted');
            if (!selectedDescriptions.length) {
                alert('Veuillez sélectionner au moins une description à gauche.');
                return;
            }
            const questionCount = document.getElementById('question-count').value;
            const selectedDescs = descriptions.filter(d => selectedDescriptions.includes(d.id));
            const moduleContent = selectedDescs.map(d => d.title + ' - ' + d.content).join('\n\n');
            loadingSpinner.classList.remove('d-none');
            qcmForm.classList.add('d-none');
            try {
                const response = await fetch('generate_qcm.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        module: moduleContent,
                        difficulty: 'moyen',
                        questionCount: questionCount
                    })
                });
                const data = await response.json();
                console.log('Réponse QCM:', data);
                if (data.success) {
                    displayQCM(data.questions);
                } else {
                    alert('Erreur lors de la génération du QCM: ' + data.message);
                    qcmForm.classList.remove('d-none');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de la génération du QCM');
                qcmForm.classList.remove('d-none');
            } finally {
                loadingSpinner.classList.add('d-none');
            }
        });
        function displayQCM(questions) {
            questionsContainer.innerHTML = '';
            questions.forEach((question, index) => {
                const questionHtml = `
                    <div class="qcm-question card mb-4">
                        <div class="card-header">
                            Question ${index + 1}
                        </div>
                        <div class="card-body">
                            <div class="question-text">${question.text}</div>
                            <div class="options-container">
                                ${question.options.map((option, optIndex) => `
                                    <div class="qcm-option" data-correct="${option.isCorrect}">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q${index}" id="q${index}o${optIndex}">
                                            <label class="form-check-label" for="q${index}o${optIndex}">
                                                <span class="option-letter">${String.fromCharCode(65 + optIndex)}</span>
                                                ${option.text}
                                            </label>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `;
                questionsContainer.innerHTML += questionHtml;
            });
            qcmDisplay.classList.remove('d-none');
            enableOptionClick();
        }
        checkAnswersBtn.addEventListener('click', function() {
            const questions = document.querySelectorAll('.qcm-question');
            let score = 0;
            let total = questions.length;
            let wrong = 0;
            questions.forEach((question, index) => {
                const selectedOption = question.querySelector('input[type="radio"]:checked');
                if (selectedOption) {
                    const option = selectedOption.closest('.qcm-option');
                    const isCorrect = option.dataset.correct === 'true';
                    if (isCorrect) {
                        score++;
                        option.classList.add('option-correct');
                    } else {
                        wrong++;
                        option.classList.add('option-incorrect');
                        // Highlight correct answer
                        question.querySelector('.qcm-option[data-correct="true"]').classList.add('option-correct');
                    }
                } else {
                    wrong++;
                    // Si aucune réponse sélectionnée, on met en surbrillance la bonne réponse
                    question.querySelector('.qcm-option[data-correct="true"]').classList.add('option-correct');
                }
            });
            // Affichage du score avec bonnes réponses en vert et mauvaises en rouge
            const scoreHtml = `
                <div class="score-message">
                    <div class="score-container">
                        <h4>Résultat</h4>
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" style="width: ${(score/total)*100}%">
                                ${score}/${total} (${Math.round((score/total)*100)}%)
                            </div>
                        </div>
                        <div>
                            <span style="color: #28a745; font-weight: bold;">Bonnes réponses : ${score}</span>
                            &nbsp;|&nbsp;
                            <span style="color: #dc3545; font-weight: bold;">Mauvaises réponses : ${wrong}</span>
                        </div>
                    </div>
                </div>
            `;
            questionsContainer.insertAdjacentHTML('beforeend', scoreHtml);
            checkAnswersBtn.disabled = true;
        });
        function enableOptionClick() {
            document.querySelectorAll('.qcm-option').forEach(optionDiv => {
                optionDiv.onclick = function(e) {
                    const radio = optionDiv.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                };
            });
        }
        newQcmBtn.addEventListener('click', function() {
            // On relance la génération du QCM avec les mêmes paramètres
            qcmDisplay.classList.add('d-none');
            loadingSpinner.classList.remove('d-none');
            checkAnswersBtn.disabled = false;
            // Récupérer les paramètres actuels
            const questionCount = document.getElementById('question-count').value;
            const selectedDescs = descriptions.filter(d => selectedDescriptions.includes(d.id));
            const moduleContent = selectedDescs.map(d => d.title + ' - ' + d.content).join('\n\n');
            fetch('generate_qcm.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    module: moduleContent,
                    difficulty: 'moyen',
                    questionCount: questionCount
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayQCM(data.questions);
                } else {
                    alert('Erreur lors de la génération du QCM: ' + data.message);
                    qcmForm.classList.remove('d-none');
                }
            })
            .catch(error => {
                alert('Une erreur est survenue lors de la génération du QCM');
                qcmForm.classList.remove('d-none');
            })
            .finally(() => {
                loadingSpinner.classList.add('d-none');
            });
        });
        // Dans le formulaire QCM, modifie le texte du bouton selon la sélection
        function updateQcmButtonLabel() {
            const btn = document.querySelector('#qcm-form button[type="submit"]');
            if (!btn) return;
            if (selectedDescriptions.length === 1) {
                const index = descriptions.findIndex(d => d.id === selectedDescriptions[0]);
                btn.innerHTML = `<i class="fas fa-robot me-2"></i>Générer le QCM pour Description ${index + 1}`;
            } else {
                btn.innerHTML = `<i class="fas fa-robot me-2"></i>Générer le QCM`;
            }
        }

        // Chatbot Logic
        const chatBox = document.getElementById('chat-box');
        const chatMessageInput = document.getElementById('chat-message-input');
        const sendMessageBtn = document.getElementById('send-message-btn');

        sendMessageBtn.addEventListener('click', sendMessage);
        chatMessageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function sendMessage() {
            const message = chatMessageInput.value.trim();
            if (!message) return;

            appendMessage(message, 'sent');
            chatMessageInput.value = '';
            // TODO: Add loading indicator

            // Send message to backend (send_message.php)
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message, selectedDescriptions: selectedDescriptions })
            })
            .then(response => response.json())
            .then(data => {
                // TODO: Remove loading indicator
                if (data.success) {
                    appendMessage(data.reply, 'received');
                } else {
                    appendMessage('Error: ' + (data.message || 'Could not get reply.'), 'received');
                }
            })
            .catch(error => {
                // TODO: Remove loading indicator
                console.error('Error:', error);
                appendMessage('Error: Could not connect to chatbot.', 'received');
            });
        }

        function appendMessage(message, type) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', type);
            messageElement.innerHTML = `<p>${message}</p>`;
            chatBox.appendChild(messageElement);
            chatBox.scrollTop = chatBox.scrollHeight; // Scroll to bottom
        }
    </script>
</body>
</html> 