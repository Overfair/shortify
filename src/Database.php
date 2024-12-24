<?php

class Database {
    private PDO $pdo;

    public function __construct() {
        $dsn = 'pgsql:host=db;port=5432;dbname=shortify;';
        $user = 'postgres';
        $password = 'password';
        $this->pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    // Сохраняем PDO приватным
    public function getPdo(): PDO {
        return $this->pdo;
    }

    // Создание короткого url
    public function createShortUrl(string $url, string $code): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO urls (code, url, created_at, expires_at) 
            VALUES (:code, :url, NOW(), NOW() + INTERVAL '31 days')
        ");
        $stmt->execute(['code' => $code, 'url' => $url]);
    }

    // Получение оригинального url
    public function getLongUrl(string $code): ?string {
        $stmt = $this->pdo->prepare("SELECT url FROM urls WHERE code = :code");
        $stmt->execute(['code' => $code]);
        return $stmt->fetchColumn() ?: null;
    }
}