<?php
header('Content-Type: text/plain; charset=utf-8');

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

// Check if categories table exists
$hasCategories = false;
try {
    $pdo->query("SELECT 1 FROM categories LIMIT 1");
    $hasCategories = true;
} catch (Exception $e) {
    $hasCategories = false;
}

if (!$hasCategories) {
    echo "📦 Tables incomplete. Reimporting schema...\n";
    
    // Drop everything and reimport
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($tables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "   Dropped " . count($tables) . " existing tables\n";
    
    // Read and filter the SQL dump
    $sql = file_get_contents(__DIR__ . '/database/restaurant.sql');
    $lines = explode("\n", $sql);
    $filtered = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match('/^ALTER TABLE.*ADD COLUMN IF NOT EXISTS/i', $trimmed)) continue;
        if (preg_match('/^INSERT IGNORE INTO access_rights/i', $trimmed)) continue;
        $filtered[] = $line;
    }
    
    // Execute statement by statement
    $fullSql = implode("\n", $filtered);
    try {
        $pdo->exec($fullSql);
        echo "✅ Schema imported successfully\n";
    } catch (Exception $e) {
        echo "⚠️ Import error: " . $e->getMessage() . "\n";
        echo "   Trying line-by-line...\n";
        // Fallback: execute each statement separately
        $statements = explode(";\n", $fullSql);
        $success = 0; $failed = 0;
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (empty($stmt)) continue;
            try {
                $pdo->exec($stmt);
                $success++;
            } catch (Exception $e) {
                $failed++;
            }
        }
        echo "   ✅ $success statements OK, ⚠️ $failed skipped\n";
    }
} else {
    echo "✅ Categories table exists\n";
}

// Add cold dishes category if missing
$stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE name = 'Холодные блюда'");
if ($stmt->fetchColumn() == 0) {
    echo "📦 Adding 'Холодные блюда' category...\n";
    $pdo->exec("INSERT INTO categories (name) VALUES ('Холодные блюда')");
    $catId = $pdo->lastInsertId();
    $pdo->exec("INSERT INTO dishes (category_id, name, description, price, weight) VALUES 
        ($catId, 'Суши-сет', 'Набор из 8 видов суши и роллов с лососем, тунцом и креветкой', 950.00, 350),
        ($catId, 'Холодец', 'Домашний холодец из говядины с хреном и горчицей', 350.00, 250),
        ($catId, 'Окрошка', 'Классическая окрошка на квасе с говядиной', 280.00, 300)
    ");
    echo "✅ Cold dishes added!\n";
} else {
    echo "✅ 'Холодные блюда' already exists\n";
}

echo "\n=== 📋 Menu summary ===\n";
$rows = $pdo->query("SELECT c.name, COUNT(d.id) as cnt FROM categories c LEFT JOIN dishes d ON d.category_id = c.id GROUP BY c.id, c.name ORDER BY c.id")->fetchAll();
foreach ($rows as $r) {
    echo "   {$r['name']}: {$r['cnt']} dishes\n";
}
echo "\n✅ Migration complete!\n";
