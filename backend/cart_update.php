<?php
// =============================================
// ОБНОВЛЕНИЕ КОЛИЧЕСТВА В КОРЗИНЕ (в активном заказе)
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
$quantity = max(1, min(20, (int)($_POST['quantity'] ?? 1)));
$user_id = $_SESSION['user_id'];

// Ищем активный заказ
$stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'cart' LIMIT 1");
$stmt->execute([$user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Корзина пуста']);
    exit;
}

// Проверяем, что позиция принадлежит заказу
$stmt = $pdo->prepare("SELECT id, dish_id FROM order_items WHERE id = ? AND order_id = ?");
$stmt->execute([$item_id, $order['id']]);
$item = $stmt->fetch();

if (!$item) {
    echo json_encode(['success' => false, 'error' => 'Товар не найден']);
    exit;
}

// Обновляем количество
$stmt = $pdo->prepare("UPDATE order_items SET count = ? WHERE id = ?");
$stmt->execute([$quantity, $item_id]);

// Получаем цену блюда
$stmt = $pdo->prepare("SELECT price FROM dishes WHERE id = ?");
$stmt->execute([$item['dish_id']]);
$price = (float)$stmt->fetchColumn();

$item_total = $price * $quantity;

// Пересчитываем общую сумму заказа
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
    'item_total' => number_format($item_total, 2, '.', ''),
    'cart_total' => number_format($cart_total, 2, '.', ''),
    'cart_count' => $cart_count
]);
