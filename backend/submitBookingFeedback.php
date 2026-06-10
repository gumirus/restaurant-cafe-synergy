<?php
// =============================================
// ОБРАТНАЯ СВЯЗЬ ПО БРОНИРОВАНИЮ (лайк/дизлайк + комментарий)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isLoggedIn()) {
    header('Location: ../frontend/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $rating = $_POST['rating'] ?? '';
    $comment = trim($_POST['comment'] ?? '');

    // Проверяем, что бронь существует
    $stmt = $pdo->prepare("SELECT id, status, name FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header('Location: ../frontend/profile.php?error=Бронирование не найдено');
        exit;
    }

    // Проверяем, что бронь подтверждена (можно оценить после посещения)
    if ($booking['status'] !== 'confirmed') {
        header('Location: ../frontend/profile.php?error=Можно оценить только подтверждённое бронирование');
        exit;
    }

    // Проверяем, что ещё не оставляли отзыв
    $stmt = $pdo->prepare("SELECT id FROM booking_feedback WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    if ($stmt->fetch()) {
        header('Location: ../frontend/profile.php?error=Вы уже оставили отзыв на это бронирование');
        exit;
    }

    // Валидация рейтинга
    if (!in_array($rating, ['like', 'dislike'])) {
        header('Location: ../frontend/profile.php?error=Некорректная оценка');
        exit;
    }

    // Сохраняем
    $name = $user['name'] ?: $booking['name'];
    $stmt = $pdo->prepare("INSERT INTO booking_feedback (booking_id, user_id, name, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$booking_id, $user_id, $name, $rating, $comment ?: null]);

    header('Location: ../frontend/profile.php?success=Спасибо за ваш отзыв о посещении!');
    exit;
}

header('Location: ../frontend/profile.php');
exit;
