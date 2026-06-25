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
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($tables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    $sql = file_get_contents(__DIR__ . '/database/restaurant.sql');
    $lines = explode("\n", $sql);
    $filtered = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match('/^ALTER TABLE.*ADD COLUMN IF NOT EXISTS/i', $trimmed)) continue;
        if (preg_match('/^INSERT IGNORE INTO access_rights/i', $trimmed)) continue;
        $filtered[] = $line;
    }
    
    $statements = explode(";\n", implode("\n", $filtered));
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
    echo "   ✅ $success statements, ⚠️ $failed skipped\n";
} else {
    echo "✅ Categories table exists\n";
    
    // Check each table for missing columns and add them
    echo "📦 Checking for missing columns...\n";
    
    $columns = [
        'orders' => [
            'type' => "VARCHAR(20) DEFAULT 'delivery' AFTER status",
            'payment_status' => "VARCHAR(20) DEFAULT 'unpaid' AFTER type",
            'booking_id' => 'INT DEFAULT NULL AFTER payment_status',
        ],
        'users' => [
            'name' => "VARCHAR(100) DEFAULT NULL AFTER phone",
            'bio' => 'TEXT DEFAULT NULL AFTER name',
            'avatar' => "VARCHAR(255) DEFAULT NULL AFTER bio",
            'position' => "VARCHAR(100) DEFAULT NULL AFTER avatar",
            'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
        ],
        'dishes' => [
            'ingredients' => 'TEXT DEFAULT NULL AFTER description',
            'is_popular' => "TINYINT(1) DEFAULT 0 AFTER image",
            'is_special' => "TINYINT(1) DEFAULT 0 AFTER is_popular",
        ],
    ];
    
    foreach ($columns as $table => $tableCols) {
        try {
            $existingCols = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tableCols as $col => $def) {
                if (!in_array($col, $existingCols)) {
                    $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $def");
                    echo "   ✅ Added `$col` to `$table`\n";
                }
            }
        } catch (Exception $e) {
            echo "   ⚠️ $table: " . $e->getMessage() . "\n";
        }
    }
}

// Add cold dishes category if missing
$stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE name = 'Холодные блюда'");
if ($stmt->fetchColumn() == 0) {
    echo "📦 Adding 'Холодные блюда'...\n";
    $pdo->exec("INSERT INTO categories (name) VALUES ('Холодные блюда')");
    $catId = $pdo->lastInsertId();
    $pdo->exec("INSERT INTO dishes (category_id, name, description, price, weight, image) VALUES
        ($catId, 'Суши-сет', 'Набор из 8 видов суши и роллов с лососем, тунцом и креветкой', 950.00, 350, 'uploads/dishes/17-sushi.jpg'),
        ($catId, 'Холодец', 'Домашний холодец из говядины с хреном и горчицей', 350.00, 250, 'uploads/dishes/21-kholodets.jpg'),
        ($catId, 'Окрошка', 'Классическая окрошка на квасе с говядиной', 280.00, 300, 'uploads/dishes/22-okroshka.jpg')
    ");
    echo "✅ Cold dishes added!\n";
} else {
    echo "✅ 'Холодные блюда' already exists\n";
}

// Add images to dishes that have NULL image
echo "📦 Checking dish images...\n";
$noImg = $pdo->query("SELECT COUNT(*) FROM dishes WHERE image IS NULL OR image = ''")->fetchColumn();
if ($noImg > 0) {
    $images = [
        'Цезарь с курицей' => 'uploads/dishes/1-caesar.jpg',
        'Греческий салат' => 'uploads/dishes/2-greek.jpg',
        'Том Ям' => 'uploads/dishes/3-tom-yam.jpg',
        'Борщ' => 'uploads/dishes/4-borscht.jpg',
        'Стейк Рибай' => 'uploads/dishes/5-steak.jpg',
        'Паста Карбонара' => 'uploads/dishes/6-carbonara.jpg',
        'Тирамису' => 'uploads/dishes/7-tiramisu.jpg',
        'Чизкейк' => 'uploads/dishes/8-cheesecake.jpg',
        'Лимонад' => 'uploads/dishes/9-lemonade.jpg',
        'Кофе' => 'uploads/dishes/10-coffee.jpg',
        'Суши-сет' => 'uploads/dishes/17-sushi.jpg',
        'Холодец' => 'uploads/dishes/21-kholodets.jpg',
        'Окрошка' => 'uploads/dishes/22-okroshka.jpg',
    ];
    $updated = 0;
    foreach ($images as $name => $img) {
        $updated += $pdo->exec("UPDATE dishes SET image = '$img' WHERE name = '$name' AND (image IS NULL OR image = '')");
    }
    echo "   ✅ $updated dishes updated with images\n";
} else {
    echo "   ✅ All dishes have images\n";
}

echo "\n=== 📋 Menu summary ===\n";
$rows = $pdo->query("SELECT c.name, COUNT(d.id) as cnt FROM categories c LEFT JOIN dishes d ON d.category_id = c.id GROUP BY c.id, c.name ORDER BY c.id")->fetchAll();
foreach ($rows as $r) {
    echo "   {$r['name']}: {$r['cnt']} dishes\n";
}
echo "\n✅ Migration complete!\n";
