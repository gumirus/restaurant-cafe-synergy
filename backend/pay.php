<?php
// =============================================
// ФЕЙКОВАЯ ОПЛАТА ЗАКАЗА
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

$order_id = (int)($_POST['order_id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Проверяем, что заказ принадлежит пользователю
$stmt = $pdo->prepare("SELECT id, payment_status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Заказ не найден']);
    exit;
}

if ($order['payment_status'] === 'paid') {
    echo json_encode(['success' => false, 'error' => 'Заказ уже оплачен']);
    exit;
}

// Имитация оплаты — просто помечаем как оплачено
$stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
$stmt->execute([$order_id]);

echo json_encode([
    'success' => true,
    'message' => '✅ Оплата прошла успешно!',
    'order_id' => $order_id
]);
