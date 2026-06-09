<?php
// =============================================
// УДАЛЕНИЕ ТОВАРА ИЗ КОРЗИНЫ
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

$cart_id = (int)($_POST['cart_id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE id = ? AND user_id = ?");
$stmt->execute([$cart_id, $user_id]);

// Считаем общую сумму корзины
$stmt = $pdo->prepare("
    SELECT SUM(d.price * sc.count) as total
    FROM shopping_cart sc
    JOIN dishes d ON sc.dish_id = d.id
    WHERE sc.user_id = ?
");
$stmt->execute([$user_id]);
$cart_total = (float)$stmt->fetchColumn();

// Считаем общее количество
$stmt = $pdo->prepare("SELECT SUM(count) FROM shopping_cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_count = (int)$stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'cart_total' => number_format($cart_total, 2, '.', ''),
    'cart_count' => $cart_count
]);
