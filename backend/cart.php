<?php
// =============================================
// РАБОТА С КОРЗИНОЙ (API)
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

switch ($action) {
    // Получить корзину
    case 'get':
        $stmt = $pdo->prepare("
            SELECT sc.id, sc.dish_id, sc.count, d.name, d.price, d.image
            FROM shopping_cart sc
            JOIN dishes d ON sc.dish_id = d.id
            WHERE sc.user_id = ?
        ");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll();

        $total = array_reduce($items, function($sum, $item) {
            return $sum + ($item['price'] * $item['count']);
        }, 0);

        echo json_encode(['items' => $items, 'total' => $total]);
        break;

    // Добавить в корзину
    case 'add':
        $dishId = (int)($_POST['dish_id'] ?? 0);
        $count = (int)($_POST['count'] ?? 1);

        // Проверка, есть ли уже такой товар
        $stmt = $pdo->prepare("SELECT id, count FROM shopping_cart WHERE user_id = ? AND dish_id = ?");
        $stmt->execute([$userId, $dishId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE shopping_cart SET count = count + ? WHERE id = ?");
            $stmt->execute([$count, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO shopping_cart (user_id, dish_id, count) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $dishId, $count]);
        }

        echo json_encode(['success' => true]);
        break;

    // Удалить из корзины
    case 'remove':
        $dishId = (int)($_POST['dish_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ? AND dish_id = ?");
        $stmt->execute([$userId, $dishId]);
        echo json_encode(['success' => true]);
        break;

    // Очистить корзину
    case 'clear':
        $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Неизвестное действие']);
}
