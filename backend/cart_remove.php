<?php
// =============================================
// УДАЛЕНИЕ ТОВАРА ИЗ КОРЗИНЫ (из активного заказа)
// =============================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необходимо авторизоваться']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Неверный метод']);
    exit;
}

$item_id = (int)($_POST['item_id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Ищем активный заказ
$stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'cart' LIMIT 1");
$stmt->execute([$user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Корзина пуста']);
    exit;
}

// Удаляем позицию из заказа
$stmt = $pdo->prepare("DELETE FROM order_items WHERE id = ? AND order_id = ?");
$stmt->execute([$item_id, $order['id']]);

// Пересчитываем сумму заказа
$stmt = $pdo->prepare("SELECT COALESCE(SUM(count * price), 0) FROM order_items WHERE order_id = ?");
$stmt->execute([$order['id']]);
$cart_total = (float)$stmt->fetchColumn();

$stmt = $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
$stmt->execute([$cart_total, $order['id']]);

// Считаем общее количество
$stmt = $pdo->prepare("SELECT COALESCE(SUM(count), 0) FROM order_items WHERE order_id = ?");
$stmt->execute([$order['id']]);
$cart_count = (int)$stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'cart_total' => number_format($cart_total, 2, '.', ''),
    'cart_count' => $cart_count
]);
