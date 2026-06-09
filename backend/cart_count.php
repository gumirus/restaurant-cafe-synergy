<?php
// =============================================
// ПОЛУЧЕНИЕ КОЛИЧЕСТВА ТОВАРОВ В КОРЗИНЕ
// =============================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

$stmt = $pdo->prepare("SELECT COALESCE(SUM(count), 0) FROM shopping_cart WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$count = (int)$stmt->fetchColumn();

echo json_encode(['count' => $count]);
