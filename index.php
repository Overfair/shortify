<?php

// Устанавливаем заголовки для CORS
function setCorsHeaders() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
}

// Проверка и приведение URL к корректному виду
function checkUrl(string $url): string {
    return preg_match('/^https?:\/\//', $url) ? $url : 'https://' . $url;
}

// Обработка CORS-запросов
function handlePreflight() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Главная функция для обработки запросов
function handleRequest(Database $db, Shortify $shortify) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST' && $requestUri === '/get-short') {
        handleShortenRequest($shortify);
    } elseif ($method === 'GET' && preg_match('/^\/([a-zA-Z0-9]{6})$/', $requestUri, $matches)) {
        handleRedirectRequest($shortify, $matches[1]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
}

// Обработка запроса на сокращение URL
function handleShortenRequest(Shortify $shortify) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['url'])) {
        http_response_code(400);
        echo json_encode(['error' => 'URL is required']);
        exit();
    }

    $url = checkUrl($input['url']);
    $shortUrl = $shortify->shorten($url);

    echo json_encode(['short_url' => $shortUrl]);
    exit();
}

// Обработка запроса на редирект по коду
function handleRedirectRequest(Shortify $shortify, string $code) {
    $longUrl = $shortify->resolve($code);

    if ($longUrl) {
        header("Location: $longUrl", true, 302);
        exit();
    }

    http_response_code(404);
    echo json_encode(['error' => 'URL not found']);
    exit();
}

setCorsHeaders();
handlePreflight();

require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Shortify.php';

$db = new Database();
$shortify = new Shortify($db);

handleRequest($db, $shortify);