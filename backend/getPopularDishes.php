<?php
// =============================================
// Get Popular Dishes — получение популярных блюд
// =============================================

require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT id, name, description, price, image FROM dishes WHERE is_popular = 1 ORDER BY name LIMIT 8");
$dishes = $stmt->fetchAll();

echo json_encode($dishes);
