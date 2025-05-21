<?php
header('Content-Type: application/json');

// Inclure la configuration de la base de données
require_once '../../config/database.php';

// Inclure la configuration de l'API (Note: pour des raisons de sécurité, la clé API ne devrait idéalement pas être dans un fichier JS accessible publiquement. Considérez de la stocker dans un fichier PHP non accessible via le web.)
// Nous allons définir les variables PHP ici pour correspondre à config.js
// Assurez-vous que config.js définit bien des variables PHP si vous incluez tel quel, sinon ajustez.
// Pour cet exemple, je vais redéfinir les valeurs en dur basées sur votre config.js pour simplifier,
// mais en production, chargez-les depuis un endroit sécurisé.
$API_KEY = "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"; // Remplacez par votre clé réelle de manière sécurisée
$API_URL = "https://openrouter.ai/api/v1/chat/completions";
$API_MODEL = "meta-llama/llama-3.3-8b-instruct:free";
$MAX_TOKENS = 100000;

// Récupérer les données POST
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$selectedDescriptionIds = $input['selectedDescriptions'] ?? [];

if (empty($userMessage)) {
    echo json_encode(['success' => false, 'message' => 'Aucun message reçu.']);
    exit;
}

$context = "";
if (!empty($selectedDescriptionIds)) {
    // Récupérer le contenu des descriptions sélectionnées
    $placeholders = implode(', ', array_fill(0, count($selectedDescriptionIds), '?'));
    $sql = "SELECT title, content FROM descriptions WHERE id IN ($placeholders)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($selectedDescriptionIds);
        $descriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($descriptions)) {
            $context .= "Les descriptions sélectionnées par l'utilisateur sont les suivantes :\n\n";
            foreach ($descriptions as $desc) {
                $context .= "Titre : " . $desc['title'] . "\n";
                $context .= "Contenu : " . $desc['content'] . "\n\n";
            }
            $context .= "Réponds maintenant à la question de l'utilisateur en te basant principalement sur ces descriptions. Si la question ne peut pas être répondue par le contexte fourni, indique-le.\n\n";
        } else {
             $context .= "Aucune description correspondante trouvée pour les IDs fournis. Réponds à la question de l'utilisateur sans contexte spécifique.\n\n";
        }

    } catch(PDOException $e) {
        // En cas d'erreur de base de données lors de la récupération des descriptions
        error_log("Database error fetching descriptions: " . $e->getMessage());
        // Continuer sans contexte mais logguer l'erreur
         $context .= "[Erreur lors de la récupération des descriptions. Réponds sans contexte spécifique.]\n\n";
    }
}

// Préparer le prompt pour l'API
$prompt = $context . "Question de l'utilisateur : " . $userMessage;

// Appeler l'API OpenRouter
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $API_URL,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        'model' => $API_MODEL,
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un chatbot utile qui répond aux questions sur la base du contexte fourni.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => $MAX_TOKENS, // Utiliser le MAX_TOKENS de la configuration
    ]),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $API_KEY,
        'Content-Type: application/json',
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    // Gérer l'erreur cURL
    error_log("cURL Error #:" . $err);
    // Inclure le message d'erreur cURL spécifique dans la réponse pour aider au débogage
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la communication avec l\'API : ' . $err]);
} else {
    // Traiter la réponse de l'API
    $responseData = json_decode($response, true);

    if (isset($responseData['choices'][0]['message']['content'])) {
        $reply = $responseData['choices'][0]['message']['content'];
        echo json_encode(['success' => true, 'reply' => $reply]);
    } else {
        // Gérer les réponses d'API sans le champ attendu
        error_log("API Response Error: " . print_r($responseData, true));
        echo json_encode(['success' => false, 'message' => 'Impossible d\'obtenir une réponse de l\'API.', 'details' => $responseData]);
    }
}

?> 