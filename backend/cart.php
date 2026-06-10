<?php
// =============================================
// РАБОТА С КОРЗИНОЙ (API) — через активный заказ
// У одного клиента — один активный заказ (status='cart')
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Необходима авторизация']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// Вспомогательная функция: получить активный заказ (status='cart')
function getActiveOrder($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'cart' LIMIT 1");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

switch ($action) {
    // Получить корзину
    case 'get':
        $order = getActiveOrder($pdo, $userId);
        if (!$order) {
            echo json_encode(['items' => [], 'total' => 0, 'order_id' => null]);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT oi.id, oi.dish_id, oi.count, d.name, d.price, d.image
            FROM order_items oi
            JOIN dishes d ON oi.dish_id = d.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order['id']]);
        $items = $stmt->fetchAll();

        $total = array_reduce($items, function($sum, $item) {
            return $sum + ($item['price'] * $item['count']);
        }, 0);

        echo json_encode(['items' => $items, 'total' => $total, 'order_id' => (int)$order['id']]);
        break;

    // Добавить в корзину
    case 'add':
        $dishId = (int)($_POST['dish_id'] ?? 0);
        $count = (int)($_POST['count'] ?? 1);

        // Получаем цену блюда
        $stmt = $pdo->prepare("SELECT price FROM dishes WHERE id = ?");
        $stmt->execute([$dishId]);
        $dish = $stmt->fetch();

        if (!$dish) {
            echo json_encode(['error' => 'Блюдо не найдено']);
            exit;
        }

        // Ищем или создаём активный заказ
        $order = getActiveOrder($pdo, $userId);
        if ($order) {
            $orderId = $order['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, address, status, total_price) VALUES (?, '', 'cart', 0)");
            $stmt->execute([$userId]);
            $orderId = $pdo->lastInsertId();
        }

        // Проверка, есть ли уже такой товар
        $stmt = $pdo->prepare("SELECT id, count FROM order_items WHERE order_id = ? AND dish_id = ?");
        $stmt->execute([$orderId, $dishId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newCount = min($existing['count'] + $count, 20);
            $stmt = $pdo->prepare("UPDATE order_items SET count = ? WHERE id = ?");
            $stmt->execute([$newCount, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, dish_id, count, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $dishId, $count, $dish['price']]);
        }

        // Пересчитываем сумму
        $stmt = $pdo->prepare("SELECT SUM(count * price) FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $total = (float)$stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
        $stmt->execute([$total, $orderId]);

        echo json_encode(['success' => true]);
        break;

    // Удалить из корзины
    case 'remove':
        $itemId = (int)($_POST['item_id'] ?? 0);
        $order = getActiveOrder($pdo, $userId);

        if (!$order) {
            echo json_encode(['error' => 'Корзина пуста']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM order_items WHERE id = ? AND order_id = ?");
        $stmt->execute([$itemId, $order['id']]);

        // Пересчитываем сумму
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(count * price), 0) FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $total = (float)$stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
        $stmt->execute([$total, $order['id']]);

        echo json_encode(['success' => true]);
        break;

    // Очистить корзину
    case 'clear':
        $order = getActiveOrder($pdo, $userId);

        if ($order) {
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$order['id']]);
            $stmt = $pdo->prepare("UPDATE orders SET total_price = 0 WHERE id = ?");
            $stmt->execute([$order['id']]);
        }

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Неизвестное действие']);
}
