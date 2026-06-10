<?php
// =============================================
// ОФОРМЛЕНИЕ ЗАКАЗА (с выбором типа и оплатой)
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
$type = $_POST['type'] ?? 'delivery'; // delivery / pickup / booking
$address = trim($_POST['address'] ?? '');
$booking_date = $_POST['booking_date'] ?? '';
$booking_time = $_POST['booking_time'] ?? '';
$guests = (int)($_POST['guests'] ?? 1);
$comment = trim($_POST['comment'] ?? '');

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

    $booking_id = null;

    // Если бронь — создаём бронирование
    if ($type === 'booking') {
        if (empty($booking_date) || empty($booking_time)) {
            throw new Exception('Укажите дату и время бронирования');
        }
        $stmt = $pdo->prepare("
            INSERT INTO bookings (name, phone, guests, booking_date, booking_time, comment, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        $userStmt = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
        $userStmt->execute([$user_id]);
        $userData = $userStmt->fetch();
        $stmt->execute([
            $userData['name'] ?: 'Клиент #' . $user_id,
            $userData['phone'],
            $guests,
            $booking_date,
            $booking_time,
            $comment
        ]);
        $booking_id = $pdo->lastInsertId();
    }

    // Создаём заказ
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_price, status, type, payment_status, booking_id, address)
        VALUES (?, ?, 'pending', ?, 'unpaid', ?, ?)
    ");
    $orderAddress = ($type === 'delivery') ? $address : '';
    $stmt->execute([$user_id, $total, $type, $booking_id, $orderAddress]);
    $order_id = $pdo->lastInsertId();

    // Добавляем позиции заказа
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, dish_id, count, price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $stmt->execute([$order_id, $item['dish_id'], $item['count'], $item['price']]);
    }

    // Очищаем корзину
    $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'total' => $total,
        'type' => $type,
        'booking_id' => $booking_id
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage() ?: 'Ошибка при оформлении заказа']);
}
