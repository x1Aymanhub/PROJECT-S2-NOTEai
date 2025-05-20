<?php
header('Content-Type: application/json');

// Charger la configuration
$config = file_get_contents('config.js');
preg_match('/API_KEY\s*=\s*"([^"]+)"/', $config, $apiKeyMatch);
preg_match('/API_URL\s*=\s*"([^"]+)"/', $config, $apiUrlMatch);
preg_match('/API_MODEL\s*=\s*"([^"]+)"/', $config, $apiModelMatch);
preg_match('/MAX_TOKENS\s*=\s*(\d+)/', $config, $maxTokensMatch);

define('API_KEY', $apiKeyMatch[1] ?? '');
define('API_URL', $apiUrlMatch[1] ?? '');
define('API_MODEL', $apiModelMatch[1] ?? '');
define('MAX_TOKENS', intval($maxTokensMatch[1] ?? 1000));

// Vérification de la clé API
if (!API_KEY) {
    echo json_encode(['success' => false, 'message' => 'Clé API manquante.']);
    exit;
}

// Récupérer les données POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$module = $data['module'] ?? '';
$difficulty = $data['difficulty'] ?? 'moyen';
$questionCount = intval($data['questionCount'] ?? 5);

if (empty($module)) {
    echo json_encode(['success' => false, 'message' => 'Module is required']);
    exit;
}

// Préparer le prompt pour Llama 3
$prompt = "Génère un QCM de {$questionCount} questions sur le module '{$module}' avec un niveau de difficulté {$difficulty}.\nFormat de réponse attendu (JSON strict, guillemets doubles) :\n{\n  \"questions\": [\n    {\n      \"text\": \"Question text\",\n      \"options\": [\n        {\"text\": \"Option A\", \"isCorrect\": true/false},\n        {\"text\": \"Option B\", \"isCorrect\": true/false},\n        {\"text\": \"Option C\", \"isCorrect\": true/false},\n        {\"text\": \"Option D\", \"isCorrect\": true/false}\n      ]\n    }\n  ]\n}";

// Préparer la requête pour OpenRouter
$apiUrl = API_URL;
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . API_KEY,
    'HTTP-Referer: https://openrouter.ai/', // recommandé par OpenRouter
    'X-Title: NOTEai-QCM'
];

$requestData = [
    'model' => API_MODEL,
    'max_tokens' => MAX_TOKENS,
    'temperature' => 0.7,
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Tu es un assistant pédagogique qui génère des QCM interactifs en JSON.'
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ]
];

// Appel API
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['success' => false, 'message' => 'Erreur cURL: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'message' => 'Erreur API OpenRouter: ' . $response]);
    exit;
}

// Décoder la réponse
$responseData = json_decode($response, true);
$aiResponse = $responseData['choices'][0]['message']['content'] ?? '';

if (!$aiResponse) {
    echo json_encode(['success' => false, 'message' => 'Réponse vide de l\'API AI', 'raw' => $response]);
    exit;
}

// Extraire le JSON du texte généré
preg_match('/\{.*\}/s', $aiResponse, $matches);
$qcmData = json_decode($matches[0] ?? '{}', true);

// Si le JSON est invalide, tente de corriger les guillemets simples en doubles
if ((empty($qcmData) || !isset($qcmData['questions'])) && !empty($matches[0])) {
    $fixedJson = str_replace("'", '"', $matches[0]);
    $qcmData = json_decode($fixedJson, true);
}

if (empty($qcmData) || !isset($qcmData['questions'])) {
    echo json_encode(['success' => false, 'message' => 'Format de réponse AI invalide', 'raw' => $aiResponse]);
    exit;
}

// Retourner le QCM
echo json_encode([
    'success' => true,
    'questions' => $qcmData['questions']
]); 