<?php
// =============================================
// ДОБАВЛЕНИЕ БЛЮДА В КОРЗИНУ (в активный заказ)
// У одного клиента — один активный заказ (status='cart')
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

$dish_id = (int)($_POST['dish_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($dish_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID блюда']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Получаем цену блюда
    $stmt = $pdo->prepare("SELECT price FROM dishes WHERE id = ?");
    $stmt->execute([$dish_id]);
    $dish = $stmt->fetch();

    if (!$dish) {
        throw new Exception('Блюдо не найдено');
    }

    // Ищем активный заказ (status='cart') для этого пользователя
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'cart' LIMIT 1");
    $stmt->execute([$user_id]);
    $order = $stmt->fetch();

    if ($order) {
        $order_id = $order['id'];
    } else {
        // Создаём новый заказ со статусом 'cart'
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, address, status, total_price) VALUES (?, '', 'cart', 0)");
        $stmt->execute([$user_id]);
        $order_id = $pdo->lastInsertId();
    }

    // Проверяем, есть ли уже такое блюдо в заказе
    $stmt = $pdo->prepare("SELECT id, count FROM order_items WHERE order_id = ? AND dish_id = ?");
    $stmt->execute([$order_id, $dish_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Проверяем лимит в 20 единиц
        if ($existing['count'] >= 20) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Максимальное количество — 20 шт']);
            exit;
        }
        // Увеличиваем количество
        $stmt = $pdo->prepare("UPDATE order_items SET count = count + 1 WHERE id = ?");
        $stmt->execute([$existing['id']]);
    } else {
        // Добавляем новую запись
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, dish_id, count, price) VALUES (?, ?, 1, ?)");
        $stmt->execute([$order_id, $dish_id, $dish['price']]);
    }

    // Пересчитываем общую сумму заказа
    $stmt = $pdo->prepare("SELECT SUM(count * price) as total FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $total = (float)$stmt->fetchColumn();

    $stmt = $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
    $stmt->execute([$total, $order_id]);

    // Считаем общее количество позиций в заказе
    $stmt = $pdo->prepare("SELECT SUM(count) as total FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $cart_count = (int)$stmt->fetchColumn();

    $pdo->commit();

    echo json_encode(['success' => true, 'cart_count' => $cart_count]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
