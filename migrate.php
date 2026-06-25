<?php
require_once __DIR__ . '/backend/config/db.php';

// Add category
$stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE name = 'Холодные блюда'");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO categories (name) VALUES ('Холодные блюда')");
    $catId = $pdo->lastInsertId();
    $pdo->exec("INSERT INTO dishes (category_id, name, description, price, weight) VALUES 
        ($catId, 'Суши-сет', 'Набор из 8 видов суши и роллов с лососем, тунцом и креветкой', 950.00, 350),
        ($catId, 'Холодец', 'Домашний холодец из говядины с хреном и горчицей', 350.00, 250),
        ($catId, 'Окрошка', 'Классическая окрошка на квасе с говядиной', 280.00, 300)
    ");
    echo "✅ Category and dishes added\n";
} else {
    echo "✅ Already exists\n";
}
