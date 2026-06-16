<?php
// =============================================
// ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ (PDO) — Docker
// =============================================

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'restaurant_db';
$username = getenv('DB_USER') ?: 'user';
$password = getenv('DB_PASS') ?: 'userpass';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}
