<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$api_key = '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';
$url = 'https://openrouter.ai/api/v1/chat/completions';

$data = [
    'model' => 'qwen/qwen3-0.6b-04-28:free',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, are you working?']
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key,
    'HTTP-Referer: ' . $_SERVER['HTTP_HOST']
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo 'Erreur cURL: ' . curl_error($ch);
} else {
    echo 'Code HTTP: ' . $httpcode . '<br>';
    echo 'Réponse: ' . $response;
}

curl_close($ch); 