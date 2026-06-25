<?php
// =============================================
// ОФОРМЛЕНИЕ ЗАКАЗА (с выбором типа и оплатой)
// Меняет статус активного заказа с 'cart' на 'pending'
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
$booking_id_from_form = (int)($_POST['booking_id'] ?? 0);

// Ищем активный заказ (status='cart')
$stmt = $pdo->prepare("SELECT id, total_price FROM orders WHERE user_id = ? AND status = 'cart' LIMIT 1");
$stmt->execute([$user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Корзина пуста']);
    exit;
}

$order_id = $order['id'];
$total = (float)$order['total_price'];

// Проверяем, есть ли товары в заказе
$stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$itemCount = (int)$stmt->fetchColumn();

if ($itemCount === 0) {
    echo json_encode(['success' => false, 'error' => 'Корзина пуста']);
    exit;
}

try {
    $pdo->beginTransaction();

    $booking_id = $booking_id_from_form ?: null;

    // Если бронь — создаём новую или используем существующую
    if ($type === 'booking') {
        if ($booking_id) {
            // Используем существующую бронь
            $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $user_id]);
            if (!$stmt->fetch()) {
                throw new Exception('Бронь не найдена');
            }
        } else {
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
    }

    // Меняем статус заказа с 'cart' на 'pending'
    $orderAddress = ($type === 'delivery') ? $address : '';
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'pending', type = ?, payment_status = 'unpaid', booking_id = ?, address = ?
        WHERE id = ?
    ");
    $stmt->execute([$type, $booking_id, $orderAddress, $order_id]);

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
