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
            'email' => "VARCHAR(255) DEFAULT NULL AFTER phone",
            'avatar_data' => "LONGTEXT DEFAULT NULL AFTER avatar",
            'name' => "VARCHAR(100) DEFAULT NULL AFTER email",
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

// Migrate existing file avatars to DB (base64)
echo "📦 Checking file avatars...\n";
$usersNoData = $pdo->query("SELECT id, avatar FROM users WHERE avatar IS NOT NULL AND avatar != '' AND (avatar_data IS NULL OR avatar_data = '')")->fetchAll();
if (count($usersNoData) > 0) {
    $converted = 0;
    foreach ($usersNoData as $u) {
        $filePath = __DIR__ . '/frontend/uploads/' . $u['avatar'];
        if (file_exists($filePath)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            $imgData = file_get_contents($filePath);
            if ($imgData !== false) {
                $b64 = 'data:' . $mime . ';base64,' . base64_encode($imgData);
                $pdo->exec("UPDATE users SET avatar_data = " . $pdo->quote($b64) . " WHERE id = {$u['id']}");
                $converted++;
            }
        }
    }
    echo "   ✅ $converted file avatars converted to DB\n";
} else {
    echo "   ✅ No file avatars to convert\n";
}

// Create verification_codes table if missing
try {
    $pdo->query("SELECT 1 FROM verification_codes LIMIT 1");
} catch (Exception $e) {
    echo "📦 Creating verification_codes table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS verification_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(20) DEFAULT NULL,
        email VARCHAR(255) DEFAULT NULL,
        code VARCHAR(6) NOT NULL,
        method ENUM('email', 'sms') NOT NULL,
        verified TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        INDEX idx_phone (phone),
        INDEX idx_email (email)
    ) ENGINE=InnoDB");
    echo "   ✅ verification_codes table created\n";
}

// Add images to dishes that have NULL image
echo "📦 Checking dish images...\n";
$noImg = $pdo->query("SELECT COUNT(*) FROM dishes WHERE image IS NULL OR image = ''")->fetchColumn();
if ($noImg > 0) {
    $images = [
        'Цезарь с курицей' => 'uploads/dishes/1-caesar.jpg',
        'Греческий салат' => 'uploads/dishes/2-greek.jpg',
        'Боул с киноа' => 'uploads/dishes/11-bowl.jpg',
        'Нисуаз' => 'uploads/dishes/14-salad.jpg',
        'Том Ям' => 'uploads/dishes/3-tom-yam.jpg',
        'Борщ' => 'uploads/dishes/4-borscht.jpg',
        'Рамен' => 'uploads/dishes/13-ramen.jpg',
        'Стейк Рибай' => 'uploads/dishes/5-steak.jpg',
        'Паста Карбонара' => 'uploads/dishes/6-carbonara.jpg',
        'Пицца Маргарита' => 'uploads/dishes/12-pizza.jpg',
        'Лосось с овощами' => 'uploads/dishes/15-fish.jpg',
        'Куриный рулет' => 'uploads/dishes/16-chicken.jpg',
        'Бургер' => 'uploads/dishes/18-burger.jpg',
        'Тирамису' => 'uploads/dishes/7-tiramisu.jpg',
        'Чизкейк' => 'uploads/dishes/8-cheesecake.jpg',
        'Мороженое' => 'uploads/dishes/20-icecream.jpg',
        'Лимонад' => 'uploads/dishes/9-lemonade.jpg',
        'Кофе' => 'uploads/dishes/10-coffee.jpg',
        'Смузи' => 'uploads/dishes/19-smoothie.jpg',
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

// Add missing dishes (not in the original dump)
echo "📦 Checking for missing dishes...\n";
$extraDishes = [
    ['name' => 'Боул с киноа', 'cat' => 'Салаты', 'desc' => 'Полезный боул с киноа, авокадо и овощами', 'price' => 420.00, 'img' => 'uploads/dishes/11-bowl.jpg'],
    ['name' => 'Нисуаз', 'cat' => 'Салаты', 'desc' => 'Французский салат с тунцом и яйцом', 'price' => 390.00, 'img' => 'uploads/dishes/14-salad.jpg'],
    ['name' => 'Рамен', 'cat' => 'Супы', 'desc' => 'Японский суп с лапшой, свининой и яйцом', 'price' => 480.00, 'img' => 'uploads/dishes/13-ramen.jpg'],
    ['name' => 'Пицца Маргарита', 'cat' => 'Горячие блюда', 'desc' => 'Классическая итальянская пицца с моцареллой', 'price' => 550.00, 'img' => 'uploads/dishes/12-pizza.jpg'],
    ['name' => 'Лосось с овощами', 'cat' => 'Горячие блюда', 'desc' => 'Запечённый лосось с сезонными овощами', 'price' => 890.00, 'img' => 'uploads/dishes/15-fish.jpg'],
    ['name' => 'Куриный рулет', 'cat' => 'Горячие блюда', 'desc' => 'Куриный рулет с грибами и сыром', 'price' => 520.00, 'img' => 'uploads/dishes/16-chicken.jpg'],
    ['name' => 'Бургер', 'cat' => 'Горячие блюда', 'desc' => 'Говяжий бургер с сыром и карамелизированным луком', 'price' => 490.00, 'img' => 'uploads/dishes/18-burger.jpg'],
    ['name' => 'Мороженое', 'cat' => 'Десерты', 'desc' => 'Пломбир с ягодным топпингом', 'price' => 280.00, 'img' => 'uploads/dishes/20-icecream.jpg'],
    ['name' => 'Смузи', 'cat' => 'Напитки', 'desc' => 'Ягодный смузи с бананом и мятой', 'price' => 250.00, 'img' => 'uploads/dishes/19-smoothie.jpg'],
];

$added = 0;
foreach ($extraDishes as $d) {
    $exists = $pdo->query("SELECT COUNT(*) FROM dishes WHERE name = '{$d['name']}'")->fetchColumn();
    if ($exists == 0) {
        $catId = $pdo->query("SELECT id FROM categories WHERE name = '{$d['cat']}'")->fetchColumn();
        if ($catId) {
            $pdo->exec("INSERT INTO dishes (category_id, name, description, price, image) VALUES ($catId, '{$d['name']}', '{$d['desc']}', {$d['price']}, '{$d['img']}')");
            $added++;
        }
    }
}
if ($added > 0) {
    echo "   ✅ $added missing dishes added\n";
} else {
    echo "   ✅ No missing dishes\n";
}

echo "\n=== 📋 Menu summary ===\n";
$rows = $pdo->query("SELECT c.name, COUNT(d.id) as cnt FROM categories c LEFT JOIN dishes d ON d.category_id = c.id GROUP BY c.id, c.name ORDER BY c.id")->fetchAll();
foreach ($rows as $r) {
    echo "   {$r['name']}: {$r['cnt']} dishes\n";
}
echo "\n✅ Migration complete!\n";
