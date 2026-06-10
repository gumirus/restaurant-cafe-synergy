<?php
// =============================================
// Get Special Dishes — получение фирменных блюд (шеф-рекомендует)
// =============================================

require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT id, name, description, price, image FROM dishes WHERE is_special = 1 ORDER BY name LIMIT 6");
$dishes = $stmt->fetchAll();

echo json_encode($dishes);
