<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/backend/config/db.php';

$fixes = [
    "ALTER TABLE orders ADD COLUMN type VARCHAR(20) DEFAULT 'delivery' AFTER status",
    "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'unpaid' AFTER type",
    "ALTER TABLE orders ADD COLUMN booking_id INT DEFAULT NULL AFTER payment_status",
    "ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
];

$fixed = 0; $errors = 0;
foreach ($fixes as $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ $sql\n";
        $fixed++;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "⏭️ Already exists\n";
        } else {
            echo "❌ " . $e->getMessage() . "\n";
            $errors++;
        }
    }
}

echo "\n---\n";
$cols = $pdo->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);
echo "Orders columns: " . implode(', ', $cols) . "\n";
