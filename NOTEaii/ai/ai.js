// ai.js - Handles chatbot interactions
document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.getElementById('chat-box');
    const chatMessageInput = document.getElementById('chat-message-input');
    const sendMessageBtn = document.getElementById('send-message-btn');
    let selectedDescriptions = [];

    // Initialize chatbot
    function initChatbot() {
        appendMessage("🚀 AI Plus Pro - Chatbot Intelligente\nBonjour ! Je suis là pour vous aider. Posez-moi vos questions ou exprimez vos besoins, et je serai ravi de vous apporter des réponses précises et pertinentes. 💡", 'received');
    }

    // Ajouter un indicateur de chargement
    function createLoadingMessage() {
        const loadingElement = document.createElement('div');
        loadingElement.classList.add('message', 'received', 'loading-message');
        loadingElement.innerHTML = `
            <p>
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Réflexion en cours...
            </p>
        `;
        return loadingElement;
    }

    function removeLoadingMessage() {
        const loadingMsg = chatBox.querySelector('.loading-message');
        if (loadingMsg) {
            loadingMsg.remove();
        }
    }

    // Event listeners
    sendMessageBtn.addEventListener('click', sendMessage);
    chatMessageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    function sendMessage() {
        const message = chatMessageInput.value.trim();
        if (!message) return;

        // Désactiver le bouton et l'input pendant l'envoi
        sendMessageBtn.disabled = true;
        chatMessageInput.disabled = true;
        
        appendMessage(message, 'sent');
        chatMessageInput.value = '';
        
        // Ajouter l'indicateur de chargement
        const loadingMessage = createLoadingMessage();
        chatBox.appendChild(loadingMessage);
        chatBox.scrollTop = chatBox.scrollHeight;

        // Préparer les données à envoyer
        const requestData = {
            message: message,
            selectedDescriptions: selectedDescriptions || []
        };

        console.log('Envoi du message:', requestData);

        // Send message to backend (send_message.php)
        fetch('send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('Statut de la réponse:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Réponse reçue:', data);
            
            removeLoadingMessage();
            
            if (data.success) {
                appendMessage(data.reply, 'received');
            } else {
                appendMessage(data.message || 'Erreur lors de la communication avec le chatbot.', 'received error-message');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            removeLoadingMessage();
            
            let errorMessage = 'Erreur de connexion au chatbot.';
            
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                errorMessage = 'Impossible de se connecter au serveur. Vérifiez votre connexion.';
            } else if (error.message.includes('HTTP error')) {
                errorMessage = 'Erreur du serveur. Veuillez réessayer plus tard.';
            }
            
            appendMessage(errorMessage, 'received error-message');
        })
        .finally(() => {
            // Réactiver le bouton et l'input
            sendMessageBtn.disabled = false;
            chatMessageInput.disabled = false;
            chatMessageInput.focus();
        });
    }

    function appendMessage(message, type) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message', ...type.split(' '));
        
        // Échapper le HTML pour éviter les problèmes de sécurité
        const escapedMessage = message.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        
        messageElement.innerHTML = `<p>${escapedMessage}</p>`;
        chatBox.appendChild(messageElement);
        chatBox.scrollTop = chatBox.scrollHeight; // Scroll to bottom
    }

    // Initialize on load
    initChatbot();
});