<?php
// =============================================
// СОЗДАНИЕ БРОНИРОВАНИЯ
// =============================================

require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$guests = (int)($_POST['guests'] ?? 1);
$comment = trim($_POST['comment'] ?? '');

// Валидация
$errors = [];
if (!$name) $errors[] = 'Укажите имя';
if (!$phone) $errors[] = 'Укажите телефон';
if (!$date) $errors[] = 'Укажите дату';
if (!$time) $errors[] = 'Укажите время';
if ($guests < 1 || $guests > 20) $errors[] = 'Количество гостей от 1 до 20';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO bookings (name, phone, email, guests, booking_date, booking_time, comment, status, created_at)
        VALUES (?, ?, '', ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$name, $phone, $guests, $date, $time, $comment]);

    echo json_encode([
        'success' => true,
        'message' => '✅ Спасибо, ' . htmlspecialchars($name) . '! Мы свяжемся с вами для подтверждения брони.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера. Попробуйте позже.']);
}
