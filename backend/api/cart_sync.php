<?php
// =============================================
// СИНХРОНИЗАЦИЯ КОРЗИНЫ (серверная)
// =============================================
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Загрузить корзину с сервера
    $stmt = $pdo->prepare("
        SELECT uc.dish_id as id, d.name, d.price, uc.quantity as count
        FROM user_cart uc
        JOIN dishes d ON uc.dish_id = d.id
        WHERE uc.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();
    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['items'])) {
        echo json_encode(['success' => false, 'error' => 'Неверные данные']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        // Очистить текущую корзину
        $pdo->prepare("DELETE FROM user_cart WHERE user_id = ?")->execute([$user_id]);
        // Вставить новые товары
        $stmt = $pdo->prepare("INSERT INTO user_cart (user_id, dish_id, quantity) VALUES (?, ?, ?)");
        foreach ($input['items'] as $item) {
            if (isset($item['id']) && isset($item['count'])) {
                $stmt->execute([$user_id, (int)$item['id'], (int)$item['count']]);
            }
        }
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Неверный метод']);
