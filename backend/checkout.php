<?php
// =============================================
// ОФОРМЛЕНИЕ ЗАКАЗА
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

$user_id = $_SESSION['user_id'];

// Получаем товары из корзины
$stmt = $pdo->prepare("
    SELECT sc.dish_id, sc.count, d.name, d.price
    FROM shopping_cart sc
    JOIN dishes d ON sc.dish_id = d.id
    WHERE sc.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

if (empty($items)) {
    echo json_encode(['success' => false, 'error' => 'Корзина пуста']);
    exit;
}

$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['count'];
}

try {
    $pdo->beginTransaction();

    // Создаём заказ
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$user_id, $total]);
    $order_id = $pdo->lastInsertId();

    // Добавляем позиции заказа
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, dish_id, name, price, quantity) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmt->execute([$order_id, $item['dish_id'], $item['name'], $item['price'], $item['count']]);
    }

    // Очищаем корзину
    $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Ошибка при оформлении заказа']);
}
