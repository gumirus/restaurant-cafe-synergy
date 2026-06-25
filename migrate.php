<?php
// Миграция БД — создаёт таблицы и добавляет холодные блюда
$hosts = ['mysql', 'mysql.railway.internal', '127.0.0.1', 'localhost'];
$dbName = getenv('DB_NAME') ?: 'restaurant_db';
$dbUser = getenv('DB_USER') ?: 'user';
$dbPass = getenv('DB_PASS') ?: 'userpass';
$connected = false;

foreach ($hosts as $host) {
    try {
        $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $connected = true;
        echo "✅ Connected via host: $host\n";
        break;
    } catch (Exception $e) {
        echo "❌ $host: " . $e->getMessage() . "\n";
    }
}

if (!$connected) {
    echo "❌ Could not connect to MySQL with any host\n";
    exit(1);
}

// Check if tables exist
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
if (count($tables) === 0) {
    echo "📦 Tables empty, importing dump...\n";
    $sql = file_get_contents(__DIR__ . '/database/restaurant.sql');
    $pdo->exec($sql);
    echo "✅ Dump imported\n";
} else {
    echo "✅ Tables exist (" . count($tables) . ")\n";
    
    // Check if cold dishes category exists
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
        echo "✅ Category and dishes added\n";
    } else {
        echo "✅ 'Холодные блюда' already exists\n";
    }
}

echo "\n=== Categories & dish counts ===\n";
$rows = $pdo->query("SELECT c.name, COUNT(d.id) as cnt FROM categories c LEFT JOIN dishes d ON d.category_id = c.id GROUP BY c.id, c.name ORDER BY c.id")->fetchAll();
foreach ($rows as $r) {
    echo "  {$r['name']}: {$r['cnt']} блюд\n";
}
