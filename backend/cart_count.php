<?php
// =============================================
// ПОЛУЧЕНИЕ КОЛИЧЕСТВА ТОВАРОВ В КОРЗИНЕ
// Считаем из активного заказа (status='cart')
// =============================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

$userId = $_SESSION['user_id'];

// Ищем активный заказ
$stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'cart' LIMIT 1");
$stmt->execute([$userId]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['count' => 0]);
    exit;
}

$stmt = $pdo->prepare("SELECT COALESCE(SUM(count), 0) FROM order_items WHERE order_id = ?");
$stmt->execute([$order['id']]);
$count = (int)$stmt->fetchColumn();

echo json_encode(['count' => $count]);
