<?php

class Shortify {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    // Генерация кода
    public function generateCode(): string {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
    }

    // Создание короткой ссылки
    public function shorten(string $url): string {
        $code = $this->generateCode();
        $this->db->createShortUrl($url, $code);
        return "http://localhost:8081/$code";
    }

    // Получение оригинальной ссылки
    public function resolve(string $code): ?string {
        $stmt = $this->db->getPdo()->prepare("
            SELECT url FROM urls 
            WHERE code = :code AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute(['code' => $code]);
        $longUrl = $stmt->fetchColumn();
    
        if ($longUrl) {
            $this->incrementClickCount($code);
        }
    
        return $longUrl ?: null;
    }
    
    // Добавляем переход
    private function incrementClickCount(string $code): void {
        $stmt = $this->db->getPdo()->prepare("
            UPDATE urls 
            SET click_count = click_count + 1 
            WHERE code = :code
        ");
        $stmt->execute(['code' => $code]);
    }
}