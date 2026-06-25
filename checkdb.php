<?php
$hosts = ['mysql', 'mysql.railway.internal', '127.0.0.1'];
$dbName = getenv('DB_NAME') ?: 'restaurant_db';
$dbUser = getenv('DB_USER') ?: 'user';
$dbPass = getenv('DB_PASS') ?: 'userpass';

foreach ($hosts as $host) {
    try {
        $pdo = new PDO("mysql:host=$host;port=3306;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $ver = $pdo->query("SELECT VERSION()")->fetchColumn();
        echo "✅ $host: MySQL $ver\n";
        break;
    } catch (Exception $e) {
        echo "❌ $host: " . $e->getMessage() . "\n";
    }
}
