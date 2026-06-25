<?php
// Миграция БД
$host = 'mysql';
$dbName = getenv('DB_NAME') ?: 'restaurant_db';
$dbUser = getenv('DB_USER') ?: 'user';
$dbPass = getenv('DB_PASS') ?: 'userpass';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ Connected to MySQL ($host)\n";
} catch (Exception $e) {
    echo "❌ Cannot connect: " . $e->getMessage() . "\n";
    exit(1);
}

// Ensure database exists
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `$dbName`");

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
if (count($tables) === 0) {
    echo "📦 Importing schema...\n";
    $sql = file_get_contents(__DIR__ . '/database/restaurant.sql');
    
    // Remove ALTER TABLE ADD COLUMN IF NOT EXISTS lines (MySQL < 8.0.16 compat)
    $lines = explode("\n", $sql);
    $filtered = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match('/^ALTER TABLE.*ADD COLUMN IF NOT EXISTS/i', $trimmed)) {
            continue; // skip these lines
        }
        if (preg_match('/^INSERT IGNORE INTO access_rights/i', $trimmed)) {
            continue; // skip - table may not exist yet
        }
        $filtered[] = $line;
    }
    $sql = implode("\n", $filtered);
    
    // Execute statement by statement
    $pdo->exec($sql);
    echo "✅ Schema imported\n";
} else {
    echo "✅ Tables exist (" . count($tables) . ")\n";
}

// Add cold dishes category if missing
$stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE name = 'Холодные блюда'");
if ($stmt->fetchColumn() == 0) {
    echo "📦 Adding 'Холодные блюда'...\n";
    $pdo->exec("INSERT INTO categories (name) VALUES ('Холодные блюда')");
    $catId = $pdo->lastInsertId();
    $pdo->exec("INSERT INTO dishes (category_id, name, description, price, weight) VALUES 
        ($catId, 'Суши-сет', 'Набор из 8 видов суши и роллов с лососем, тунцом и креветкой', 950.00, 350),
        ($catId, 'Холодец', 'Домашний холодец из говядины с хреном и горчицей', 350.00, 250),
        ($catId, 'Окрошка', 'Классическая окрошка на квасе с говядиной', 280.00, 300)
    ");
    echo "✅ Cold dishes added\n";
} else {
    echo "✅ 'Холодные блюда' already exists\n";
}

echo "\n=== Menu summary ===\n";
$rows = $pdo->query("SELECT c.name, COUNT(d.id) as cnt FROM categories c LEFT JOIN dishes d ON d.category_id = c.id GROUP BY c.id, c.name ORDER BY c.id")->fetchAll();
foreach ($rows as $r) {
    echo "  {$r['name']}: {$r['cnt']} dishes\n";
}
